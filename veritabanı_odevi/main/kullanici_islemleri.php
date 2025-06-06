<?php

require_once 'database/db_connect.php';

function girisYap(string $eposta, string $sifre) {
    global $conn;

    $ogrenciSorgusu = $conn->prepare("SELECT ogrenci_id, sifre_hash FROM ogrenciler WHERE e_posta = :eposta");
    $ogrenciSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $ogrenciSorgusu->execute();
    $ogrenciKaydi = $ogrenciSorgusu->fetch(PDO::FETCH_ASSOC);

    if ($ogrenciKaydi && password_verify($sifre, $ogrenciKaydi['sifre_hash'])) {
        return [
            'id'  => $ogrenciKaydi['ogrenci_id'],
            'rol' => 'ogrenci'
        ];
    }

    $ogretmenSorgusu = $conn->prepare("SELECT ogretmen_id, sifre_hash FROM ogretmenler WHERE e_posta = :eposta");
    $ogretmenSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $ogretmenSorgusu->execute();
    $ogretmenKaydi = $ogretmenSorgusu->fetch(PDO::FETCH_ASSOC);

    if ($ogretmenKaydi && password_verify($sifre, $ogretmenKaydi['sifre_hash'])) {
        return [
            'id'  => $ogretmenKaydi['ogretmen_id'],
            'rol' => 'ogretmen'
        ];
    }

    return false;
}

function kayitOl(string $eposta, string $sifre, string $rol): string {
    global $conn;

    $sifreHash = password_hash($sifre, PASSWORD_BCRYPT);

    $epostaKontrolSorgusu = null;
    if ($rol === 'ogrenci') {
        $epostaKontrolSorgusu = $conn->prepare("SELECT e_posta FROM ogrenciler WHERE e_posta = :eposta");
    } elseif ($rol === 'ogretmen') {
        $epostaKontrolSorgusu = $conn->prepare("SELECT e_posta FROM ogretmenler WHERE e_posta = :eposta");
    }

    $epostaKontrolSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $epostaKontrolSorgusu->execute();

    if ($epostaKontrolSorgusu->rowCount() > 0) {
        return 'kayit_var';
    }

    $kayitSorgusu = null;
    if ($rol === 'ogrenci') {
        $kayitSorgusu = $conn->prepare("INSERT INTO ogrenciler (e_posta, sifre_hash) VALUES (:eposta, :sifre_hash)");
    } elseif ($rol === 'ogretmen') {
        $kayitSorgusu = $conn->prepare("INSERT INTO ogretmenler (e_posta, sifre_hash) VALUES (:eposta, :sifre_hash)");
    }

    $kayitSorgusu->bindParam(':eposta', $eposta, PDO::PARAM_STR);
    $kayitSorgusu->bindParam(':sifre_hash', $sifreHash, PDO::PARAM_STR);

    if ($kayitSorgusu->execute()) {
        return 'basarili';
    } else {
        return 'kayit_hata';
    }
}

?>