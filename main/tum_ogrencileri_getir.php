<?php


require_once 'ayarlar.php';  

header('Content-Type: application/json'); 

try {
     
    $stmt = $pdo->query("SELECT ogrenci_id, adi, soyadi, e_posta FROM ogrenciler ORDER BY adi, soyadi");
    $ogrenciler = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    echo json_encode(['success' => true, 'ogrenciler' => $ogrenciler]); 
} catch (PDOException $e) {
    
    echo json_encode(['success' => false, 'message' => 'Öğrenciler çekilirken hata oluştu: ' . $e->getMessage()]);
}
?>
