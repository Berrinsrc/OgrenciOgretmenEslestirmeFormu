<?php

require_once 'ayarlar.php'; // PDO bağlantısı için ayarlar dosyasını dahil et


function musaitligiGuncelle(int $ogretmenId, string $gun, string $baslangicSaati, string $bitisSaati): bool {
    global $pdo;
    try {
        $guncellemeSorgusu = $pdo->prepare("UPDATE ogretmen_musaitlik SET dolu = 1 WHERE ogretmen_id = :ogretmenId AND gun = :gun AND baslangic_saati = :baslangicSaati AND bitis_saati = :bitisSaati");
        $guncellemeSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
        $guncellemeSorgusu->bindParam(':gun', $gun, PDO::PARAM_STR);
        $guncellemeSorgusu->bindParam(':baslangicSaati', $baslangicSaati, PDO::PARAM_STR);
        $guncellemeSorgusu->bindParam(':bitisSaati', $bitisSaati, PDO::PARAM_STR);
        return $guncellemeSorgusu->execute();
    } catch (PDOException $e) {
        error_log("Müsaitlik güncelleme hatası: " . $e->getMessage());
        return false;
    }
}


function eslesmeOlustur(int $ogrenciId, int $ogretmenId, int $dersId, string $tarih, string $gun, string $baslangicSaati, string $bitisSaati): bool {
    global $pdo;
    try {
        $kayitSorgusu = $pdo->prepare("INSERT INTO eslesmeler (ogrenci_id, ogretmen_id, ders_id, tarih, gun, baslangic_saati, bitis_saati) VALUES (:ogrenciId, :ogretmenId, :dersId, :tarih, :gun, :baslangicSaati, :bitisSaati)");
        $kayitSorgusu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
        $kayitSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
        $kayitSorgusu->bindParam(':dersId', $dersId, PDO::PARAM_INT);
        $kayitSorgusu->bindParam(':tarih', $tarih, PDO::PARAM_STR);
        $kayitSorgusu->bindParam(':gun', $gun, PDO::PARAM_STR);
        $kayitSorgusu->bindParam(':baslangicSaati', $baslangicSaati, PDO::PARAM_STR);
        $kayitSorgusu->bindParam(':bitisSaati', $bitisSaati, PDO::PARAM_STR);
        return $kayitSorgusu->execute();
    } catch (PDOException $e) {
        error_log("Eşleşme oluşturma hatası: " . $e->getMessage());
        return false;
    }
}


function eslesmeSil(int $eslesmeId): bool {
    global $pdo;
    try {
        $silmeSorgusu = $pdo->prepare("DELETE FROM eslesmeler WHERE eslesme_id = :eslesmeId");
        $silmeSorgusu->bindParam(':eslesmeId', $eslesmeId, PDO::PARAM_INT);
        return $silmeSorgusu->execute();
    } catch (PDOException $e) {
        error_log("Eşleşme silme hatası: " . $e->getMessage());
        return false;
    }
}


function eslesmeleriGetir(): array {
    global $pdo;
    try {
        $sorgu = $pdo->query("SELECT eslesme_id, ogrenci_id, ogretmen_id, ders_id, tarih, gun, baslangic_saati, bitis_saati FROM eslesmeler", PDO::FETCH_ASSOC);
        return $sorgu->fetchAll();
    } catch (PDOException $e) {
        error_log("Eşleşmeleri getirirken hata oluştu: " . $e->getMessage());
        return [];
    }
}


function eslesmeleriOgrenciyeGoreGetir(int $ogrenciId): array {
    global $pdo;
    try {
        $sorgu = $pdo->prepare("SELECT eslesme_id, ogrenci_id, ogretmen_id, ders_id, tarih, gun, baslangic_saati, bitis_saati FROM eslesmeler WHERE ogrenci_id = :ogrenciId");
        $sorgu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
        $sorgu->execute();
        return $sorgu->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Öğrencinin eşleşmelerini getirirken hata oluştu: " . $e->getMessage());
        return [];
    }
}


function eslesmeleriOgretmeneGoreGetir(int $ogretmenId): array {
    global $pdo;
    try {
        $sorgu = $pdo->prepare("SELECT eslesme_id, ogrenci_id, ders_id, tarih, gun, baslangic_saati, bitis_saati FROM eslesmeler WHERE ogretmen_id = :ogretmenId");
        $sorgu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
        $sorgu->execute();
        return $sorgu->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Öğretmenin eşleşmelerini getirirken hata oluştu: " . $e->getMessage());
        return [];
    }
}


function eslesmeGuncelle(int $eslesmeId, int $ogrenciId, int $ogretmenId, int $dersId, string $tarih, string $gun, string $baslangicSaati, string $bitisSaati): bool {
    global $pdo;
    try {
        $guncellemeSorgusu = $pdo->prepare("UPDATE eslesmeler SET ogrenci_id = :ogrenciId, ogretmen_id = :ogretmenId, ders_id = :dersId, tarih = :tarih, gun = :gun, baslangic_saati = :baslangicSaati, bitis_saati = :bitisSaati WHERE eslesme_id = :eslesmeId");
        $guncellemeSorgusu->bindParam(':eslesmeId', $eslesmeId, PDO::PARAM_INT);
        $guncellemeSorgusu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
        $guncellemeSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
        $guncellemeSorgusu->bindParam(':dersId', $dersId, PDO::PARAM_INT);
        $guncellemeSorgusu->bindParam(':tarih', $tarih, PDO::PARAM_STR);
        $guncellemeSorgusu->bindParam(':gun', $gun, PDO::PARAM_STR);
        $guncellemeSorgusu->bindParam(':baslangicSaati', $baslangicSaati, PDO::PARAM_STR);
        $guncellemeSorgusu->bindParam(':bitisSaati', $bitisSaati, PDO::PARAM_STR);
        return $guncellemeSorgusu->execute();
    } catch (PDOException $e) {
        error_log("Eşleşme güncelleme hatası: " . $e->getMessage());
        return false;
    }
}
