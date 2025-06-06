<?php
// kontrol.php - Kayıt ve Giriş İşlemleri Yöneticisi
// Bu dosya, kullanıcıların kayıt olmasını ve sisteme giriş yapmasını yönetir.

// Veritabanı bağlantısı ve oturum başlatma için 'ayarlar.php' dosyasını dahil et.
require_once 'ayarlar.php';

// İşlem türünü al (URL'den gelen 'islem' parametresi ile)
$islem = isset($_GET['islem']) ? $_GET['islem'] : '';

// Sadece POST isteklerini işleriz, güvenlik için önemlidir.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Kayıt işlemi
    if ($islem == 'kayit') {
        // Formdan gelen verileri al ve boşlukları temizle
        $ad = trim($_POST['ad']);
        $soyad = trim($_POST['soyad']);
        $eposta = trim($_POST['eposta']);
        $sifre = $_POST['sifre'];
        $rol = isset($_POST['rol']) ? $_POST['rol'] : '';
        $secilen_dersler = isset($_POST['dersler']) ? $_POST['dersler'] : []; // Yeni: Seçilen dersler

        // Basit doğrulama: Alanlar boş mu?
        if (empty($ad) || empty($soyad) || empty($eposta) || empty($sifre) || empty($rol)) {
            header("Location: kayit_ol.php?hata=bos_alanlar"); // Eksik bilgi hatası
            exit();
        }

        // E-posta formatı doğru mu?
        if (!filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
            header("Location: kayit_ol.php?hata=gecersiz_eposta"); // Geçersiz e-posta hatası
            exit();
        }

        try {
            // E-posta zaten kayıtlı mı kontrol et
            $stmt_check_email = $pdo->prepare("SELECT id FROM kullanicilar WHERE e_posta = :eposta");
            $stmt_check_email->bindParam(':eposta', $eposta);
            $stmt_check_email->execute();

            if ($stmt_check_email->rowCount() > 0) {
                header("Location: kayit_ol.php?hata=kayit_var"); // E-posta zaten var hatası
                exit();
            }

            // Şifreyi güvenli bir şekilde hashle (şifreleme)
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);

            // Kullanıcıyı 'kullanicilar' tablosuna ekle
            $stmt_kullanici = $pdo->prepare("INSERT INTO kullanicilar (ad, soyad, e_posta, sifre_hash, rol) VALUES (:ad, :soyad, :eposta, :sifre_hash, :rol)");
            $stmt_kullanici->bindParam(':ad', $ad);
            $stmt_kullanici->bindParam(':soyad', $soyad);
            $stmt_kullanici->bindParam(':eposta', $eposta);
            $stmt_kullanici->bindParam(':sifre_hash', $sifre_hash);
            $stmt_kullanici->bindParam(':rol', $rol);

            if ($stmt_kullanici->execute()) {
                $kullanici_id = $pdo->lastInsertId(); // Eklenen son kullanıcının ID'sini al

                // Rolüne göre ilgili tabloya (ogrenciler veya ogretmenler) ekleme yap
                if ($rol == 'ogrenci') {
                    $stmt_ogrenci = $pdo->prepare("INSERT INTO ogrenciler (kullanici_id, adi, soyadi, ilgi_alanlari) VALUES (:kullanici_id, :adi, :soyadi, NULL)");
                    $stmt_ogrenci->bindParam(':kullanici_id', $kullanici_id, PDO::PARAM_INT);
                    $stmt_ogrenci->bindParam(':adi', $ad);
                    $stmt_ogrenci->bindParam(':soyadi', $soyad);
                    $stmt_ogrenci->execute();
                } elseif ($rol == 'ogretmen') {
                    $stmt_ogretmen = $pdo->prepare("INSERT INTO ogretmenler (kullanici_id, adi, soyadi, e_posta, uzmanlik_alanlari, sifre_hash) VALUES (:kullanici_id, :adi, :soyadi, :eposta, NULL, :sifre_hash)");
                    $stmt_ogretmen->bindParam(':kullanici_id', $kullanici_id, PDO::PARAM_INT);
                    $stmt_ogretmen->bindParam(':adi', $ad);
                    $stmt_ogretmen->bindParam(':soyadi', $soyad);
                    $stmt_ogretmen->bindParam(':eposta', $eposta);
                    $stmt_ogretmen->bindParam(':sifre_hash', $sifre_hash);
                    $stmt_ogretmen->execute();
                    
                    $ogretmen_id = $pdo->lastInsertId(); // Eklenen son öğretmenin ID'sini al

                    // Seçilen dersleri ogretmen_dersleri tablosuna ekle
                    if (!empty($secilen_dersler)) {
                        $stmt_ogretmen_ders = $pdo->prepare("INSERT INTO ogretmen_dersleri (ogretmen_id, ders_id) VALUES (:ogretmen_id, :ders_id)");
                        foreach ($secilen_dersler as $ders_id) {
                            $stmt_ogretmen_ders->bindParam(':ogretmen_id', $ogretmen_id, PDO::PARAM_INT);
                            $stmt_ogretmen_ders->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
                            $stmt_ogretmen_ders->execute();
                        }
                    }
                }
                // Kayıt başarılı olduğunda kayıt sayfasına başarı mesajıyla yönlendir
                header("Location: kayit_ol.php?kayit=basarili");
                exit();
            } else {
                // Kullanıcı eklenirken bir hata oluştuysa
                header("Location: kayit_ol.php?hata=kayit_basarisiz"); // Kayıt başarısız hatası
                exit();
            }
        } catch (PDOException $e) {
            // Veritabanı hatası durumunda
            error_log("Kayıt hatası: " . $e->getMessage()); // Hata loglama
            header("Location: kayit_ol.php?hata=db_hata"); // Veritabanı hatası
            exit();
        }

    } elseif ($islem == 'giris') {
        // Giriş işlemi
        $eposta = trim($_POST['eposta']);
        $sifre = $_POST['sifre'];

        // E-posta veya şifre boş mu?
        if (empty($eposta) || empty($sifre)) {
            header("Location: giris.php?hata=bos_alanlar"); // Boş alanlar hatası
            exit();
        }

        try {
            // Kullanıcıyı e-postasına göre 'kullanicilar' tablosundan bul
            $stmt = $pdo->prepare("SELECT id, sifre_hash, rol, ad, soyad FROM kullanicilar WHERE e_posta = :eposta");
            $stmt->bindParam(':eposta', $eposta);
            $stmt->execute();
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC); // Kullanıcı bilgilerini ilişkisel dizi olarak al

            // Kullanıcı bulunduysa ve şifre doğruysa
            if ($kullanici && password_verify($sifre, $kullanici['sifre_hash'])) {
                // Oturum değişkenlerini ayarla
                $_SESSION['giris_yapti'] = true;
                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['eposta'] = $kullanici['e_posta']; 
                $_SESSION['ad'] = $kullanici['ad'];
                $_SESSION['soyad'] = $kullanici['soyad'];
                $_SESSION['rol'] = $kullanici['rol'];

                // Rolüne göre özel ID'yi oturuma kaydet
                if ($kullanici['rol'] == 'ogrenci') {
                    // Öğrenci ID'sini 'ogrenciler' tablosundan çek
                    $stmt_ogrenci_id = $pdo->prepare("SELECT ogrenci_id FROM ogrenciler WHERE kullanici_id = :kullanici_id");
                    $stmt_ogrenci_id->bindParam(':kullanici_id', $kullanici['id'], PDO::PARAM_INT);
                    $stmt_ogrenci_id->execute();
                    $ogrenci_info = $stmt_ogrenci_id->fetch(PDO::FETCH_ASSOC);
                    if ($ogrenci_info) {
                        $_SESSION['ogrenci_id'] = $ogrenci_info['ogrenci_id'];
                    } else {
                        // Öğrenci ID'si bulunamazsa hata
                        header("Location: giris.php?hata=rol_veri_eksik");
                        exit();
                    }
                    header("Location: ogrenci_ana_sayfa.php"); // Öğrenci ana sayfasına yönlendir
                    exit();
                } elseif ($kullanici['rol'] == 'ogretmen') {
                    // Öğretmen ID'sini 'ogretmenler' tablosundan çek
                    $stmt_ogretmen_id = $pdo->prepare("SELECT ogretmen_id FROM ogretmenler WHERE kullanici_id = :kullanici_id");
                    $stmt_ogretmen_id->bindParam(':kullanici_id', $kullanici['id'], PDO::PARAM_INT);
                    $stmt_ogretmen_id->execute();
                    $ogretmen_info = $stmt_ogretmen_id->fetch(PDO::FETCH_ASSOC);
                    if ($ogretmen_info) {
                        $_SESSION['ogretmen_id'] = $ogretmen_info['ogretmen_id']; // Öğretmen ID'sini oturuma kaydet
                    } else {
                        // Öğretmen ID'si bulunamazsa hata
                        header("Location: giris.php?hata=rol_veri_eksik");
                        exit();
                    }
                    header("Location: ogretmen_ana_sayfa.php"); // Öğretmen ana sayfasına yönlendir
                    exit();
                } else {
                    // Bilinmeyen rol durumunda
                    header("Location: giris.php?hata=rol_tanimsiz");
                    exit();
                }
            } else {
                // Şifre yanlış veya kullanıcı bulunamadı
                header("Location: giris.php?hata=giris"); // Hatalı giriş hatası
                exit();
            }
        } catch (PDOException $e) {
            // Veritabanı hatası durumunda
            error_log("Giriş hatası: " . $e->getMessage()); // Hata loglama
            header("Location: giris.php?hata=db_hata"); // Veritabanı hatası
            exit();
        }
    } else {
        // Geçersiz işlem (islem parametresi 'kayit' veya 'giris' değilse)
        header("Location: index.php"); // Ana sayfaya yönlendir
        exit();
    }
} else {
    // POST isteği değilse (doğrudan URL'den erişim gibi) ana sayfaya yönlendir
    header("Location: index.php");
    exit();
}
?>
