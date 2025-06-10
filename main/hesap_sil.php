<?php

require_once 'ayarlar.php'; 
require_once 'kullanici_islemleri.php'; 

header('Content-Type: application/json'); 


if (!isset($_SESSION['giris_yapti']) || $_SESSION['giris_yapti'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Bu işlemi yapmak için giriş yapmalısınız.']);
    exit();
}


$kullanici_id = isset($_SESSION['kullanici_id']) ? intval($_SESSION['kullanici_id']) : 0;

if ($kullanici_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Kullanıcı ID\'si bulunamadı.']);
    exit();
}


if (kullaniciSil($kullanici_id)) {
    
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Hesabınız ve tüm ilişkili verileriniz başarıyla silindi.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Hesabınız silinirken bir hata oluştu.']);
}
?>
