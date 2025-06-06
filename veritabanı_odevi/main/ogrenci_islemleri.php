<?php

require_once 'database/db_connect.php';

function ogrenciKaydet(string $eposta, string $sifreHash, string $ad, string $soyad): bool {
    global $conn;

    $kayitSorgusu = $conn->prepare("INSERT INTO ogrenciler (e_posta, sifre_hash, ad, soyad) VALUES (:eposta, :sifreHash, :ad, :soyad)");
    $kayitSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':sifreHash', $sifreHash, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':ad', $ad, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':soyad', $soyad, PDO::PARAM_STR);

    return $kayitSorgusu->execute();
}

function ogrenciSil(int $ogrenciId): bool {
    global $conn;

    $silmeSorgusu = $conn->prepare("DELETE FROM ogrenciler WHERE ogrenci_id = :ogrenciId");
    $silmeSorgusu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);

    return $silmeSorgusu->execute();
}

function ogrencileriGetir(): array {
    global $conn;

    $sorgu = $conn->query("SELECT ogrenci_id, e_posta, ad, soyad FROM ogrenciler", PDO::FETCH_ASSOC);
    return $sorgu->fetchAll();
}

function ogrenciDetayGetir(int $ogrenciId): ?array {
    global $conn;

    $sorgu = $conn->prepare("SELECT ogrenci_id, e_posta, ad, soyad FROM ogrenciler WHERE ogrenci_id = :ogrenciId");
    $sorgu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
    $sorgu->execute();

    $sonuc = $sorgu->fetch(PDO::FETCH_ASSOC);
    return $sonuc ? $sonuc : null;
}

function ogrenciGuncelle(int $ogrenciId, string $eposta, string $ad, string $soyad): bool {
    global $conn;

    $guncellemeSorgusu = $conn->prepare("UPDATE ogrenciler SET e_posta = :eposta, ad = :ad, soyad = :soyad WHERE ogrenci_id = :ogrenciId");
    $guncellemeSorgusu->bindParam(':ogrenciId', $ogrenciId, PDO::PARAM_INT);
    $guncellemeSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':ad', $ad, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':soyad', $soyad, PDO::PARAM_STR);

    return $guncellemeSorgusu->execute();
}

?>