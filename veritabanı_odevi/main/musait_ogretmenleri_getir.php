<?php
// musait_ogretmenleri_getir.php
// Bu dosya, senin seçtiğin ders, tarih ve saate göre hangi öğretmenlerin boş olduğunu bulur.

require_once 'ayarlar.php'; // Veritabanı bağlantısı için ayarlar dosyasını çağırırız.

header('Content-Type: application/json'); // Sana göndereceğimiz bilginin JSON formatında olduğunu söyleriz.

// Senin gönderdiğin ders ID'sini, tarihi ve saati alırız.
$ders_id = isset($_GET['ders_id']) ? intval($_GET['ders_id']) : 0;
$randevu_tarihi = isset($_GET['randevu_tarihi']) ? $_GET['randevu_tarihi'] : '';
$randevu_saati = isset($_GET['randevu_saati']) ? $_GET['randevu_saati'] : '';

// Eğer bu bilgilerden biri eksikse, hata mesajı göndeririz.
if ($ders_id === 0 || empty($randevu_tarihi) || empty($randevu_saati)) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi var.']);
    exit();
}

try {
    // 1. Önce, seçtiğin dersi veren tüm öğretmenleri buluruz.
    $stmt = $pdo->prepare("SELECT o.ogretmen_id, o.adi, o.soyadi
                           FROM ogretmen_dersleri od
                           JOIN ogretmenler o ON od.ogretmen_id = o.ogretmen_id
                           WHERE od.ders_id = :ders_id");
    $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
    $stmt->execute();
    $ogretmenler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $available_teachers = []; // Boş olan öğretmenleri buraya ekleyeceğiz.

    foreach ($ogretmenler as $ogretmen) {
        // 2. Öğretmenin o saatte başka bir randevusu (beklemede veya onaylanmış) var mı diye bakarız.
        // Eğer randevu_bitis_saati de kontrol edilecekse, sorguyu buna göre ayarlamak gerekir.
        // Şu anki mantık, randevu_baslangic_saati'nin tam olarak çakışıp çakışmadığını kontrol ediyor.
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
            // Eğer o saatte başka randevusu yoksa, bu öğretmeni listeye ekleriz.
            $available_teachers[] = $ogretmen;
        }
    }

    echo json_encode(['success' => true, 'ogretmenler' => $available_teachers]); // Boş öğretmenleri sana göndeririz.

} catch (PDOException $e) {
    // Bir hata olursa, hata mesajını sana göndeririz.
    error_log("Öğretmenler bulunurken hata oluştu: " . $e->getMessage()); // Hata loglama
    echo json_encode(['success' => false, 'message' => 'Öğretmenler bulunurken hata oluştu: ' . $e->getMessage()]);
}
?>
