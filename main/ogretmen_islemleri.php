<?php

require_once 'ayarlar.php';


function ogretmenleriGetir(): array {
    global $pdo;
    try {

        $stmt = $pdo->query("SELECT o.ogretmen_id, k.e_posta, o.adi, o.soyadi, o.uzmanlik_alanlari FROM ogretmenler o JOIN kullanicilar k ON o.kullanici_id = k.id ORDER BY o.adi, o.soyadi");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Öğretmen listesi çekilirken hata oluştu: " . $e->getMessage());
        return [];
    }
}


function ogretmenDetayGetir(int $ogretmenId): ?array {
    global $pdo;
    try {
        
        $stmt = $pdo->prepare("SELECT o.ogretmen_id, k.e_posta, o.adi, o.soyadi, o.uzmanlik_alanlari, k.id AS kullanici_id FROM ogretmenler o JOIN kullanicilar k ON o.kullanici_id = k.id WHERE o.ogretmen_id = :ogretmenId");
        $stmt->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Öğretmen detayları çekilirken hata oluştu: " . $e->getMessage());
        return null;
    }
}


function ogretmenDersleriniGetir(int $ogretmenId): array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT d.ders_id, d.ders_adi FROM ogretmen_dersleri od JOIN dersler d ON od.ders_id = d.ders_id WHERE od.ogretmen_id = :ogretmenId");
        $stmt->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Öğretmen dersleri çekilirken hata oluştu: " . $e->getMessage());
        return [];
    }
}
