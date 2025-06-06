<?php

require_once 'database/db_connect.php';

function musaitligiGuncelle(int $ogretmenId, string $gun, string $baslangicSaati, string $bitisSaati): bool {
    global $conn;

    $guncellemeSorgusu = $conn->prepare("UPDATE musaitlikler SET dolu = 1 WHERE ogretmen_id = :ogretmenId AND gun = :gun AND baslangic_saati = :baslangicSaati AND bitis_saati = :bitisSaati");
    $guncellemeSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
    $guncellemeSorgusu->bindParam(':gun', $gun, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':baslangicSaati', $baslangicSaati, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':bitisSaati', $bitisSaati, PDO::PARAM_STR);

    return $guncellemeSorgusu->execute();
}

function eslesmeOlustur(int $ogrenciId, int $ogretmenId, int $dersId, string $tarih, string $gun, string $baslangicSaati, string $bitisSaati): bool {
    global $conn;

    $kayitSorgusu = $conn->prepare("INSERT INTO eslesmeler (ogrenci_id, ogretmen_id, ders_id, tarih, gun, baslangic_saati, bitis_saati) VALUES (:ogrenciId, :ogretmenId, :dersId, :tarih, :gun, :baslangicSaati, :bitisSaati)");
    $kayitSorgusu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
    $kayitSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
    $kayitSorgusu->bindParam(':dersId', $dersId, PDO::PARAM_INT);
    $kayitSorgusu->bindParam(':tarih', $tarih, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':gun', $gun, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':baslangicSaati', $baslangicSaati, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':bitisSaati', $bitisSaati, PDO::PARAM_STR);

    $kayitBasarili = $kayitSorgusu->execute();

    if ($kayitBasarili) {
        // Müsaitliği güncelle
        $musaitlikGuncellemeBasarili = musaitligiGuncelle($ogretmenId, $gun, $baslangicSaati, $bitisSaati);
        return $musaitlikGuncellemeBasarili;
    } else {
        return false;
    }
}

function eslesmeSil(int $eslesmeId): bool {
    global $conn;

    $silmeSorgusu = $conn->prepare("DELETE FROM eslesmeler WHERE eslesme_id = :eslesmeId");
    $silmeSorgusu->bindParam(':eslesmeId', $eslesmeId, PDO::PARAM_INT);

    return $silmeSorgusu->execute();
}

function eslesmeleriGetir(): array {
    global $conn;

    $sorgu = $conn->query("SELECT eslesme_id, ogrenci_id, ogretmen_id, ders_id, tarih, gun, baslangic_saati, bitis_saati FROM eslesmeler", PDO::FETCH_ASSOC);
    return $sorgu->fetchAll();
}

function eslesmeDetayGetir(int $eslesmeId): ?array {
    global $conn;

    $sorgu = $conn->prepare("SELECT eslesme_id, ogrenci_id, ogretmen_id, ders_id, tarih, gun, baslangic_saati, bitis_saati FROM eslesmeler WHERE eslesme_id = :eslesmeId");
    $sorgu->bindParam(':eslesmeId', $eslesmeId, PDO::PARAM_INT);
    $sorgu->execute();

    $sonuc = $sorgu->fetch(PDO::FETCH_ASSOC);
    return $sonuc ? $sonuc : null;
}

function eslesmeleriOgrenciyeGoreGetir(int $ogrenciId): array {
    global $conn;

    $sorgu = $conn->prepare("SELECT eslesme_id, ogretmen_id, ders_id, tarih, gun, baslangic_saati, bitis_saati FROM eslesmeler WHERE ogrenci_id = :ogrenciId");
    $sorgu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
    $sorgu->execute();

    return $sorgu->fetchAll(PDO::FETCH_ASSOC);
}

function eslesmeleriOgretmeneGoreGetir(int $ogretmenId): array {
    global $conn;

    $sorgu = $conn->prepare("SELECT eslesme_id, ogrenci_id, ders_id, tarih, gun, baslangic_saati, bitis_saati FROM eslesmeler WHERE ogretmen_id = :ogretmenId");
    $sorgu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
    $sorgu->execute();

    return $sorgu->fetchAll(PDO::FETCH_ASSOC);
}

function eslesmeGuncelle(int $eslesmeId, int $ogrenciId, int $ogretmenId, int $dersId, string $tarih, string $gun, string $baslangicSaati, string $bitisSaati): bool {
    global $conn;

    $guncellemeSorgusu = $conn->prepare("UPDATE eslesmeler SET ogrenci_id = :ogrenciId, ogretmen_id = :ogretmenId, ders_id = :dersId, tarih = :tarih, gun = :gun, baslangic_saati = :baslangicSaati, bitis_saati = :bitisSaati WHERE eslesme_id = :eslesmeId");
    $guncellemeSorgusu->bindParam(':eslesmeId', $eslesmeId, PDO::PARAM_INT);
    $guncellemeSorgusu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
    $guncellemeSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
    $guncellemeSorgusu->bindParam(':dersId', $dersId, PDO::PARAM_INT);
    $guncellemeSorgusu->bindParam(':tarih', $tarih, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':gun', $gun, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':baslangicSaati', $baslangicSaati, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':bitisSaati', $bitisSaati, PDO::PARAM_STR);

    return $guncellemeSorgusu->execute();
}

?>