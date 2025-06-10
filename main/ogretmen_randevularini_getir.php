<?php
require_once 'ayarlar.php'; 

header('Content-Type: application/json'); 


$ogretmen_id = isset($_SESSION['ogretmen_id']) ? intval($_SESSION['ogretmen_id']) : 0;


if ($ogretmen_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Öğretmen olarak giriş yapmadın. Lütfen giriş yap.']);
    exit();
}

try {

    $stmt = $pdo->prepare("SELECT r.randevu_id, r.randevu_tarihi, r.randevu_baslangic_saati, r.randevu_durumu, r.ogretmen_aciklama,
                                  o.adi AS ogrenci_adi, o.soyadi AS ogrenci_soyadi,
                                  d.ders_adi
                           FROM randevular r
                           JOIN ogrenciler o ON r.ogrenci_id = o.ogrenci_id
                           JOIN dersler d ON r.ders_id = d.ders_id
                           WHERE r.ogretmen_id = :ogretmen_id
                           ORDER BY r.randevu_tarihi ASC, r.randevu_baslangic_saati ASC");
    $stmt->bindParam(':ogretmen_id', $ogretmen_id, PDO::PARAM_INT);
    $stmt->execute();
    $randevular = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    echo json_encode(['success' => true, 'randevular' => $randevular]);

} catch (PDOException $e) {
   
    echo json_encode(['success' => false, 'message' => 'Randevu talepleri çekilirken hata oluştu: ' . $e->getMessage()]);
}
?>
