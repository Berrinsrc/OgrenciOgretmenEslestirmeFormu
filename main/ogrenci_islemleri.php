<?php

require_once 'ayarlar.php'; 

function ogrenciKaydet(string $eposta, string $sifreHash, string $ad, string $soyad): bool {
    global $pdo; 

    $kayitSorgusu = $pdo->prepare("INSERT INTO ogrenciler (e_posta, sifre_hash, ad, soyad) VALUES (:eposta, :sifreHash, :ad, :soyad)");
    $kayitSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':sifreHash', $sifreHash, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':ad', $ad, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':soyad', $soyad, PDO::PARAM_STR);

    return $kayitSorgusu->execute();
}

function ogrenciSil(int $ogrenciId): bool {
    global $pdo; 

    $silmeSorgusu = $pdo->prepare("DELETE FROM ogrenciler WHERE ogrenci_id = :ogrenciId");
    $silmeSorgusu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);

    return $silmeSorgusu->execute();
}

function ogrencileriGetir(): array {
    global $pdo; 

    
    $sorgu = $pdo->query("SELECT ogrenci_id, e_posta, adi, soyadi FROM ogrenciler", PDO::FETCH_ASSOC);
    return $sorgu->fetchAll();
}


function ogrenciDetayGetir(int $ogrenciId): ?array {
    global $pdo; 

    try {
        $stmt = $pdo->prepare("SELECT o.ogrenci_id, k.e_posta, o.adi, o.soyadi, o.ilgi_alanlari, k.id AS kullanici_id FROM ogrenciler o JOIN kullanicilar k ON o.kullanici_id = k.id WHERE o.ogrenci_id = :ogrenciId");
        $stmt->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Öğrenci detayları çekilirken hata oluştu: " . $e->getMessage());
        return null;
    }
}


function ogrenciGuncelle(int $ogrenciId, string $ad, string $soyad, string $eposta, ?string $yeniSifre = null, ?string $ilgiAlanlari = null): bool {
    global $pdo; 

    try {
        $pdo->beginTransaction();

       
        $sorgu_kullanici = "UPDATE kullanicilar SET e_posta = :eposta";
        if ($yeniSifre !== null && !empty($yeniSifre)) {
            $sorgu_kullanici .= ", sifre_hash = :sifre_hash";
        }
        $sorgu_kullanici .= " WHERE id = (SELECT kullanici_id FROM ogrenciler WHERE ogrenci_id = :ogrenciId)";

        $stmt_kullanici = $pdo->prepare($sorgu_kullanici);
        $stmt_kullanici->bindParam(':eposta', $eposta, PDO::PARAM_STR);
        if ($yeniSifre !== null && !empty($yeniSifre)) {
            $sifreHash = password_hash($yeniSifre, PASSWORD_BCRYPT);
            $stmt_kullanici->bindParam(':sifre_hash', $sifreHash, PDO::PARAM_STR);
        }
        $stmt_kullanici->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT); 
        $stmt_kullanici->execute();

       
        $sorgu_ogrenci = "UPDATE ogrenciler SET adi = :adi, soyadi = :soyadi";
        if ($ilgiAlanlari !== null) {
            $sorgu_ogrenci .= ", ilgi_alanlari = :ilgi_alanlari";
        }
        $sorgu_ogrenci .= " WHERE ogrenci_id = :ogrenciId";

        $stmt_ogrenci = $pdo->prepare($sorgu_ogrenci);
        $stmt_ogrenci->bindParam(':adi', $ad, PDO::PARAM_STR);
        $stmt_ogrenci->bindParam(':soyadi', $soyad, PDO::PARAM_STR);
        if ($ilgiAlanlari !== null) {
            $stmt_ogrenci->bindParam(':ilgi_alanlari', $ilgiAlanlari, PDO::PARAM_STR);
        }
        $stmt_ogrenci->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
        $stmt_ogrenci->execute();

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Öğrenci bilgisi güncelleme hatası: " . $e->getMessage());
        return false;
    }
}
