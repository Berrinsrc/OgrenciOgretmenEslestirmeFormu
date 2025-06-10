<?php

require_once 'ayarlar.php'; 

header('Content-Type: application/json'); 

try {
    
    $stmt = $pdo->query("SELECT ders_id, ders_adi FROM dersler ORDER BY ders_adi ASC");
    $dersler = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    
    echo json_encode(['success' => true, 'dersler' => $dersler]);

} catch (PDOException $e) {
    
    error_log("Dersler çekilirken hata oluştu: " . $e->getMessage()); // Hata loglama
    echo json_encode(['success' => false, 'message' => 'Dersler alınırken hata oluştu.']);
}
?>
