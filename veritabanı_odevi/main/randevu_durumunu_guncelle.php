<?php
// randevu_durumunu_guncelle.php
// Bu dosya, öğretmenin bir randevu talebini onaylamasına veya reddetmesine yarar.
// Ayrıca, öğretmen isterse bir açıklama da yazabilir.

require_once 'ayarlar.php'; // Veritabanı bağlantısı için ayarlar dosyasını çağırırız.

header('Content-Type: application/json'); // Sana göndereceğimiz bilginin JSON formatında olduğunu söyleriz.

// Öğretmenden gelen randevu ID'sini, yeni durumu (onaylandı/reddedildi) ve açıklamayı alırız.
$randevu_id = isset($_POST['randevu_id']) ? intval($_POST['randevu_id']) : 0;
$durum = isset($_POST['durum']) ? $_POST['durum'] : '';
$aciklama = isset($_POST['aciklama']) ? $_POST['aciklama'] : '';

// Eğer bilgilerden biri eksikse, hata mesajı göndeririz.
if ($randevu_id === 0 || empty($durum)) {
    echo json_encode(['success' => false, 'message' => 'Randevu durumu bilgileri eksik.']);
    exit();
}

// Durumun doğru olup olmadığını kontrol ederiz (sadece 'onaylandi' veya 'reddedildi' olabilir).
if (!in_array($durum, ['onaylandi', 'reddedildi'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz durum değeri.']);
    exit();
}

try {
    // Randevunun durumunu ve öğretmenin açıklamasını veritabanında güncelleriz.
    $stmt = $pdo->prepare("UPDATE randevular SET randevu_durumu = :durum, ogretmen_aciklama = :aciklama WHERE randevu_id = :randevu_id");
    $stmt->bindParam(':durum', $durum);
    $stmt->bindParam(':aciklama', $aciklama);
    $stmt->bindParam(':randevu_id', $randevu_id, PDO::PARAM_INT);
    $stmt->execute();

    // Güncelleme başarılı olduysa, başarılı mesajı göndeririz.
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Randevu durumu başarıyla güncellendi.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Randevu bulunamadı veya güncellenmedi.']);
    }

} catch (PDOException $e) {
    // Bir hata olursa, hata mesajını sana göndeririz.
    echo json_encode(['success' => false, 'message' => 'Randevu durumu güncellenirken hata oluştu: ' . $e->getMessage()]);
}
?>
