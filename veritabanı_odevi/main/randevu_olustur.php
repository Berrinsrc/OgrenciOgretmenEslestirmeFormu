<?php
// randevu_olustur.php
// Bu dosya, senin randevu isteğini alır ve veritabanına kaydeder.

require_once 'ayarlar.php'; // Veritabanı bağlantısı için ayarlar dosyasını çağırırız.

header('Content-Type: application/json'); // Sana göndereceğimiz bilginin JSON formatında olduğunu söyleriz.

// Oturumdan senin öğrenci numaranı alırız.
$ogrenci_id = isset($_SESSION['ogrenci_id']) ? intval($_SESSION['ogrenci_id']) : 0;

// Eğer öğrenci olarak giriş yapmadıysan, hata mesajı göndeririz.
if ($ogrenci_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Öğrenci olarak giriş yapmadın. Lütfen giriş yap.']);
    exit();
}

// Senin gönderdiğin öğretmen ID'sini, ders ID'sini, tarihi ve saati alırız.
$ogretmen_id = isset($_POST['ogretmen_id']) ? intval($_POST['ogretmen_id']) : 0;
$ders_id = isset($_POST['ders_id']) ? intval($_POST['ders_id']) : 0;
$randevu_tarihi = isset($_POST['randevu_tarihi']) ? $_POST['randevu_tarihi'] : '';
$randevu_saati = isset($_POST['randevu_saati']) ? $_POST['randevu_saati'] : '';

// Eğer bilgilerden biri eksikse, hata mesajı göndeririz.
if ($ogretmen_id === 0 || $ders_id === 0 || empty($randevu_tarihi) || empty($randevu_saati)) {
    echo json_encode(['success' => false, 'message' => 'Randevu bilgileri eksik. Lütfen tüm yerleri doldur.']);
    exit();
}

try {
    // Aynı randevuyu daha önce istemiş misin diye kontrol ederiz.
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM randevular
                                 WHERE ogrenci_id = :ogrenci_id
                                   AND ogretmen_id = :ogretmen_id
                                   AND ders_id = :ders_id
                                   AND randevu_tarihi = :randevu_tarihi
                                   AND randevu_baslangic_saati = :randevu_saati");
    $stmt_check->bindParam(':ogrenci_id', $ogrenci_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':ogretmen_id', $ogretmen_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':randevu_tarihi', $randevu_tarihi);
    $stmt_check->bindParam(':randevu_saati', $randevu_saati);
    $stmt_check->execute();
    if ($stmt_check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Bu randevu isteği zaten var.']);
        exit();
    }

    // Yeni randevu isteğini veritabanına kaydederiz. Durumu "beklemede" olarak ayarlarız.
    // randevu_bitis_saati için basit bir mantık ekledim (başlangıç saati + 1 saat).
    // Gerçek bir uygulamada bu daha dinamik olabilir.
    $randevu_bitis_saati = date('H:i:s', strtotime($randevu_saati . ' +1 hour'));

    $stmt = $pdo->prepare("INSERT INTO randevular (ogrenci_id, ogretmen_id, ders_id, randevu_tarihi, randevu_baslangic_saati, randevu_bitis_saati, randevu_durumu)
                           VALUES (:ogrenci_id, :ogretmen_id, :ders_id, :randevu_tarihi, :randevu_baslangic_saati, :randevu_bitis_saati, 'beklemede')");
    $stmt->bindParam(':ogrenci_id', $ogrenci_id, PDO::PARAM_INT);
    $stmt->bindParam(':ogretmen_id', $ogretmen_id, PDO::PARAM_INT);
    $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
    $stmt->bindParam(':randevu_tarihi', $randevu_tarihi);
    $stmt->bindParam(':randevu_baslangic_saati', $randevu_saati);
    $stmt->bindParam(':randevu_bitis_saati', $randevu_bitis_saati);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Randevu isteğin başarıyla oluşturuldu ve beklemeye alındı.']);

} catch (PDOException $e) {
    // Bir hata olursa, hata mesajını sana göndeririz.
    echo json_encode(['success' => false, 'message' => 'Randevu oluşturulurken hata oluştu: ' . $e->getMessage()]);
}
?>
