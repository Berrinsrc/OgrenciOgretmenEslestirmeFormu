<?php
// Veritabanı bağlantısı
$db = new mysqli("localhost", "root", "", "veritabanı_odevi");

if ($db->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $db->connect_error);
}

// Oturumdan veya başka bir yerden öğrenci ID'sini alın
$ogrenci_id = $_SESSION['ogrenci_id'] ?? 1; // Örnek olarak 1 aldık

// Öğrenci bilgilerini çek (gerekirse)
$ogrenci_bilgileri_sorgu = "SELECT * FROM ogrenciler WHERE ogrenci_id = ?";
$stmt_ogrenci = $db->prepare($ogrenci_bilgileri_sorgu);
$stmt_ogrenci->bind_param("i", $ogrenci_id);
$stmt_ogrenci->execute();
$ogrenci_bilgileri_sonuc = $stmt_ogrenci->get_result();
$ogrenci = $ogrenci_bilgileri_sonuc->fetch_assoc();
$stmt_ogrenci->close();

// Öğrencinin randevularını çek (öğretmen bilgileriyle birlikte)
$randevu_sorgu = "SELECT r.randevu_tarihi, r.randevu_baslangic_saati, r.randevu_bitis_saati, r.randevu_durumu,
                         ogr.adi AS ogretmen_adi, ogr.soyadi AS ogretmen_soyadi
                  FROM randevular r
                  INNER JOIN ogretmenler ogr ON r.ogretmen_id = ogr.ogretmen_id
                  WHERE r.ogrenci_id = ?";
$stmt_randevu = $db->prepare($randevu_sorgu);
$stmt_randevu->bind_param("i", $ogrenci_id);
$stmt_randevu->execute();
$randevular_sonuc = $stmt_randevu->get_result();

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Profili</title>
    <link rel="stylesheet" href="stil.css">
</head>
<body>
    <div class="profil-sayfasi">
        <div class="profil-bilgi">
            <div class="profil-resim"></div>
            <h2 class="kullanici-adi"><?php echo $ogrenci['adi'] ?? 'Öğrenci'; ?></h2>
        </div>

        <div class="randevular-bolumu">
            <h3>Randevularım</h3>
            <ul class="randevu-listesi">
                <?php
                if ($randevular_sonuc->num_rows > 0) {
                    while ($randevu = $randevular_sonuc->fetch_assoc()) {
                        echo '<li class="randevu-item">';
                        echo '<p><strong>Öğretmen:</strong> ' . $randevu['ogretmen_adi'] . ' ' . $randevu['ogretmen_soyadi'] . '</p>';
                        echo '<p><strong>Tarih:</strong> ' . date('d.m.Y', strtotime($randevu['randevu_tarihi'])) . ' <strong>Saat:</strong> ' . date('H:i', strtotime($randevu['randevu_baslangic_saati'])) . ' - ' . date('H:i', strtotime($randevu['randevu_bitis_saati'])) . '</p>';
                        echo '<p><strong>Durum:</strong> ' . $randevu['randevu_durumu'] . '</p>';
                        echo '</li>';
                    }
                } else {
                    echo '<li class="randevu-item">Henüz bir randevunuz bulunmuyor.</li>';
                }
                ?>
            </ul>
            <button class="daha-fazla-randevu">Daha Fazla Randevu...</button>
        </div>

        <div class="profil-ayarlar-bolumu">
            <h3>Profil Ayarları</h3>
            <button class="ayarlar-butonu">Şifre Değiştir</button>
            <button class="ayarlar-butonu">Bilgileri Güncelle</button>
        </div>
    </div>
</body>
</html>

<?php
$stmt_randevu->close();
$db->close();
?>