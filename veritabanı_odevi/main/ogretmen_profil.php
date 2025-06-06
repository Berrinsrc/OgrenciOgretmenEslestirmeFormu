<?php
// Veritabanı bağlantısı
$db = new mysqli("localhost", "root", "", "veritabanı_odevi");

if ($db->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $db->connect_error);
}

// Oturumdan veya başka bir yerden öğretmen ID'sini alın
$ogretmen_id = $_SESSION['ogretmen_id'] ?? 1; // Örnek olarak 1 aldık

// Öğretmen bilgilerini çek (gerekirse)
$ogretmen_bilgileri_sorgu = "SELECT adi, soyadi FROM ogretmenler WHERE ogretmen_id = ?";
$stmt_ogretmen = $db->prepare($ogretmen_bilgileri_sorgu);
$stmt_ogretmen->bind_param("i", $ogretmen_id);
$stmt_ogretmen->execute();
$ogretmen_bilgileri_sonuc = $stmt_ogretmen->get_result();
$ogretmen = $ogretmen_bilgileri_sonuc->fetch_assoc();
$stmt_ogretmen->close();

// Öğretmenin randevularını çek (öğrenci bilgileriyle birlikte)
$randevu_sorgu = "SELECT r.randevu_tarihi, r.randevu_baslangic_saati, r.randevu_bitis_saati, r.randevu_durumu,
                         ogr.adi AS ogrenci_adi, ogr.soyadi AS ogrenci_soyadi
                  FROM randevular r
                  INNER JOIN ogrenciler ogr ON r.ogrenci_id = ogr.ogrenci_id
                  WHERE r.ogretmen_id = ?";
$stmt_randevu = $db->prepare($randevu_sorgu);
$stmt_randevu->bind_param("i", $ogretmen_id);
$stmt_randevu->execute();
$randevular_sonuc = $stmt_randevu->get_result();

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Profili</title>
    <link rel="stylesheet" href="stil.css">
</head>
<body>
    <div class="profil-sayfasi">
        <div class="profil-bilgi">
            <div class="profil-resim"></div>
            <h2 class="kullanici-adi"><?php echo $ogretmen['adi'] ?? 'Öğretmen'; ?> <?php echo $ogretmen['soyadi'] ?? ''; ?></h2>
        </div>

        <div class="randevular-bolumu">
            <h3>Randevular</h3>
            <ul class="randevu-listesi">
                <?php
                if ($randevular_sonuc->num_rows > 0) {
                    while ($randevu = $randevular_sonuc->fetch_assoc()) {
                        echo '<li class="randevu-item">';
                        echo '<p><strong>Öğrenci:</strong> ' . $randevu['ogrenci_adi'] . ' ' . $randevu['ogrenci_soyadi'] . '</p>';
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

        <div class="musaitlik-yonetimi-bolumu">
            <h3>Müsaitlik Yönetimi</h3>
            <button class="ayarlar-butonu">Müsaitlikleri Görüntüle/Düzenle</button>
        </div>
    </div>
</body>
</html>

<?php
$stmt_randevu->close();
$db->close();
?>