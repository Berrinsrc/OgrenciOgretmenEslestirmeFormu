<?php
// config.php
// Bu dosya, veritabanı bağlantısını ve oturum yönetimini sağlar.

session_start(); // Oturumu başlat

// Veritabanı bağlantı bilgileri
define('DB_SERVER', 'localhost'); // Veritabanı sunucusu adresi
define('DB_USERNAME', 'root'); // Veritabanı kullanıcı adınız
define('DB_PASSWORD', '');     // Veritabanı şifreniz
define('DB_NAME', 'veritabanı_odevi'); // Kullanacağınız veritabanı adı

// PDO ile veritabanı bağlantısı oluştur
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Hata modunu istisna olarak ayarla, böylece PDO hataları istisna olarak fırlatır
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Karakter setini UTF-8 olarak ayarla
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    // Veritabanı bağlantısı başarısız olursa hata mesajını göster ve çık
    die("Veritabanına bağlanılamadı: " . $e->getMessage());
}
?>
