<?php

require_once 'database/db_connect.php';

function ogretmenKaydet(string $eposta, string $sifreHash, string $ad, string $soyad, string $brans): bool {
    global $conn;

    $kayitSorgusu = $conn->prepare("INSERT INTO ogretmenler (e_posta, sifre_hash, ad, soyad, brans) VALUES (:eposta, :sifreHash, :ad, :soyad, :brans)");
    $kayitSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':sifreHash', $sifreHash, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':ad', $ad, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':soyad', $soyad, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':brans', $brans, PDO::PARAM_STR);

    return $kayitSorgusu->execute();
}

function ogretmenSil(int $ogretmenId): bool {
    global $conn;

    $silmeSorgusu = $conn->prepare("DELETE FROM ogretmenler WHERE ogretmen_id = :ogretmenId");
    $silmeSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);

    return $silmeSorgusu->execute();
}

function ogretmenleriGetir(): array {
    global $conn;

    $sorgu = $conn->query("SELECT ogretmen_id, e_posta, ad, soyad, brans FROM ogretmenler", PDO::FETCH_ASSOC);
    return $sorgu->fetchAll();
}

function ogretmenDetayGetir(int $ogretmenId): ?array {
    global $conn;

    $sorgu = $conn->prepare("SELECT ogretmen_id, e_posta, ad, soyad, brans FROM ogretmenler WHERE ogretmen_id = :ogretmenId");
    $sorgu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
    $sorgu->execute();

    $sonuc = $sorgu->fetch(PDO::FETCH_ASSOC);
    return $sonuc ? $sonuc : null;
}

function ogretmenGuncelle(int $ogretmenId, string $eposta, string $ad, string $soyad, string $brans): bool {
    global $conn;

    $guncellemeSorgusu = $conn->prepare("UPDATE ogretmenler SET e_posta = :eposta, ad = :ad, soyad = :soyad, brans = :brans WHERE ogretmen_id = :ogretmenId");
    $guncellemeSorgusu->bindParam(':ogretmenId', $ogretmenId, PDO::PARAM_INT);
    $guncellemeSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':ad', $ad, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':soyad', $soyad, PDO::PARAM_STR);
    $guncellemeSorgusu->bindParam(':brans', $brans, PDO::PARAM_STR);

    return $guncellemeSorgusu->execute();
}

function ogretmenleriBransaGoreGetir(string $brans): array {
    global $conn;

    $sorgu = $conn->prepare("SELECT ogretmen_id, e_posta, ad, soyad FROM ogretmenler WHERE brans = :brans");
    $sorgu->bindParam(':brans', $brans, PDO::PARAM_STR);
    $sorgu->execute();

    return $sorgu->fetchAll(PDO::FETCH_ASSOC);
}

?>