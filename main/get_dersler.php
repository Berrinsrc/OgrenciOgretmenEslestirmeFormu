<?php

require_once 'ayarlar.php'; 

header('Content-Type: application/json'); 

try {
    
    $stmt = $pdo->query("SELECT ders_id, ders_adi FROM dersler ORDER BY ders_adi");
    $dersler = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    echo json_encode(['success' => true, 'dersler' => $dersler]); 
} catch (PDOException $e) {
    
    echo json_encode(['success' => false, 'message' => 'Dersler çekilirken hata oluştu: ' . $e->getMessage()]);
}
?>