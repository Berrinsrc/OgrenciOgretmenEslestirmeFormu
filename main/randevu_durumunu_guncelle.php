<?php

require_once 'ayarlar.php'; 

header('Content-Type: application/json'); 


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu.']);
    exit();
}


$randevu_id = isset($_POST['randevu_id']) ? intval($_POST['randevu_id']) : 0;
$durum = isset($_POST['durum']) ? $_POST['durum'] : '';
$aciklama = isset($_POST['aciklama']) ? trim($_POST['aciklama']) : ''; 

if ($randevu_id === 0 || empty($durum)) {
    echo json_encode(['success' => false, 'message' => 'Randevu durumu bilgileri eksik.']);
    exit();
}

if (!in_array($durum, ['onaylandi', 'reddedildi'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz durum değeri.']);
    exit();
}

$ogretmen_id = $_SESSION['ogretmen_id'] ?? 0;
if ($ogretmen_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Oturum sona ermiş veya öğretmen olarak giriş yapmadınız.']);
    exit();
}

try {
    
    $stmt_check_owner = $pdo->prepare("SELECT COUNT(*) FROM randevular WHERE randevu_id = :randevu_id AND ogretmen_id = :ogretmen_id");
    $stmt_check_owner->bindParam(':randevu_id', $randevu_id, PDO::PARAM_INT);
    $stmt_check_owner->bindParam(':ogretmen_id', $ogretmen_id, PDO::PARAM_INT);
    $stmt_check_owner->execute();
    if ($stmt_check_owner->fetchColumn() === 0) {
        echo json_encode(['success' => false, 'message' => 'Bu randevuyu güncelleme yetkiniz yok.']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE randevular SET randevu_durumu = :durum, ogretmen_aciklama = :aciklama WHERE randevu_id = :randevu_id");
    $stmt->bindParam(':durum', $durum);
    $stmt->bindParam(':aciklama', $aciklama);
    $stmt->bindParam(':randevu_id', $randevu_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Randevu durumu başarıyla güncellendi!', 'redirect' => 'ogretmen_ana_sayfa.php?durum=guncelleme_basarili']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Randevu durumu güncellenemedi veya zaten aynı durumda.']);
    }

} catch (PDOException $e) {
    
    error_log("Randevu durumu güncelleme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası: Randevu durumu güncellenemedi.']);
}
?>
