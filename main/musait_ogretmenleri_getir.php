<?php

header('Content-Type: application/json'); 


$ders_id = isset($_GET['ders_id']) ? intval($_GET['ders_id']) : 0;
$randevu_tarihi = isset($_GET['randevu_tarihi']) ? $_GET['randevu_tarihi'] : '';
$randevu_saati = isset($_GET['randevu_saati']) ? $_GET['randevu_saati'] : '';


if ($ders_id === 0 || empty($randevu_tarihi) || empty($randevu_saati)) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi var. Ders, tarih ve saat belirtmelisiniz.']);
    exit();
}

try {
    $available_teachers = [];

   
    $stmt_ogretmenler = $pdo->prepare("SELECT o.ogretmen_id, o.adi, o.soyadi
                                       FROM ogretmen_dersleri od
                                       JOIN ogretmenler o ON od.ogretmen_id = o.ogretmen_id
                                       WHERE od.ders_id = :ders_id
                                       ORDER BY o.adi, o.soyadi");
    $stmt_ogretmenler->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
    $stmt_ogretmenler->execute();
    $dersi_veren_ogretmenler = $stmt_ogretmenler->fetchAll(PDO::FETCH_ASSOC);

   
    foreach ($dersi_veren_ogretmenler as $ogretmen) {
      
        $stmt_randevu = $pdo->prepare("SELECT COUNT(*)
                                           FROM randevular
                                           WHERE ogretmen_id = :ogretmen_id
                                             AND randevu_tarihi = :randevu_tarihi
                                             AND randevu_baslangic_saati = :randevu_saati
                                             AND randevu_durumu IN ('beklemede', 'onaylandi')");
        $stmt_randevu->bindParam(':ogretmen_id', $ogretmen['ogretmen_id'], PDO::PARAM_INT);
        $stmt_randevu->bindParam(':randevu_tarihi', $randevu_tarihi);
        $stmt_randevu->bindParam(':randevu_saati', $randevu_saati);
        $stmt_randevu->execute();
        $has_appointment = $stmt_randevu->fetchColumn();

        if (!$has_appointment) {
            
            $available_teachers[] = $ogretmen;
        }
    }

    echo json_encode(['success' => true, 'ogretmenler' => $available_teachers]); 

} catch (PDOException $e) {
    
    error_log("Öğretmen müsaitlik çekilirken hata oluştu: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Müsait öğretmenler çekilirken bir hata oluştu.']);
}
