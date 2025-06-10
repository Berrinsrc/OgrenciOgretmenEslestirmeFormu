<?php


require_once 'ayarlar.php'; 

header('Content-Type: application/json'); 


$ogrenci_id = isset($_SESSION['ogrenci_id']) ? intval($_SESSION['ogrenci_id']) : 0;


if ($ogrenci_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Öğrenci olarak giriş yapmadın. Lütfen giriş yap.']);
    exit();
}

try {
   
    $stmt = $pdo->prepare("SELECT r.randevu_id, r.randevu_tarihi, r.randevu_baslangic_saati, r.randevu_durumu, r.ogretmen_aciklama,
                                  o.adi AS ogretmen_adi, o.soyadi AS ogretmen_soyadi,
                                  d.ders_adi
                           FROM randevular r
                           JOIN ogretmenler o ON r.ogretmen_id = o.ogretmen_id
                           JOIN dersler d ON r.ders_id = d.ders_id
                           WHERE r.ogrenci_id = :ogrenci_id
                           ORDER BY r.randevu_tarihi DESC, r.randevu_baslangic_saati DESC"); 
    $stmt->bindParam(':ogrenci_id', $ogrenci_id, PDO::PARAM_INT);
    $stmt->execute();
    $randevular = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    echo json_encode(['success' => true, 'randevular' => $randevular]); 

} catch (PDOException $e) {
    
    echo json_encode(['success' => false, 'message' => 'Randevular çekilirken hata oluştu: ' . $e->getMessage()]);
}
?>
