<?php


require_once 'ayarlar.php'; 
require_once 'kullanici_islemleri.php'; 


$islem = isset($_GET['islem']) ? $_GET['islem'] : '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    if ($islem == 'kayit') {
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            header("Location: kayit_ol.php?hata=csrf_gecersiz");
            exit();
        }

        
        $ad = $_POST['ad'] ?? '';
        $soyad = $_POST['soyad'] ?? '';
        $eposta = $_POST['eposta'] ?? '';
        $sifre = $_POST['sifre'] ?? '';
        $rol = $_POST['rol'] ?? '';
        $secilen_dersler = isset($_POST['dersler']) ? (array)$_POST['dersler'] : []; 
        
        $uzmanlik_alani = ($rol === 'ogretmen') ? ($_POST['uzmanlik_alanlari'] ?? null) : null; 

        
        if (empty($ad) || empty($soyad) || empty($eposta) || empty($sifre) || empty($rol)) {
            header("Location: kayit_ol.php?hata=bos_alanlar");
            exit();
        }

        
        if (!filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
            header("Location: kayit_ol.php?hata=gecersiz_eposta");
            exit();
        }

        if (strlen($sifre) < 6) {
            header("Location: kayit_ol.php?hata=sifre_kisa");
            exit();
        }

      
        $kayit_sonuc = kayitOl($ad, $soyad, $eposta, $sifre, $rol, $secilen_dersler, $uzmanlik_alani); 

        if ($kayit_sonuc === 'basarili') {
            header("Location: giris.php?kayit_basarili=true");
            exit();
        } elseif ($kayit_sonuc === 'kayit_var') {
            header("Location: kayit_ol.php?hata=kayit_var");
            exit();
        } else {
            header("Location: kayit_ol.php?hata=kayit_sirasi_hata");
            exit();
        }
    }
    
    elseif ($islem == 'giris') {
        

        $eposta = $_POST['eposta'] ?? '';
        $sifre = $_POST['sifre'] ?? '';

        if (empty($eposta) || empty($sifre)) {
            header("Location: giris.php?hata=bos_alanlar");
            exit();
        }

        $kullanici_bilgileri = girisYap($eposta, $sifre);

        if ($kullanici_bilgileri) {
            
            $_SESSION['giris_yapti'] = true;
            $_SESSION['kullanici_id'] = $kullanici_bilgileri['id'];
            $_SESSION['rol'] = $kullanici_bilgileri['rol'];
            $_SESSION['ad'] = $kullanici_bilgileri['ad'];
            $_SESSION['soyad'] = $kullanici_bilgileri['soyad'];

            
            if ($kullanici_bilgileri['rol'] == 'ogrenci') {
                $_SESSION['ogrenci_id'] = $kullanici_bilgileri['ogrenci_id'];
                header("Location: ogrenci_ana_sayfa.php");
                exit();
            } elseif ($kullanici_bilgileri['rol'] == 'ogretmen') {
                $_SESSION['ogretmen_id'] = $kullanici_bilgileri['ogretmen_id'];
                $_SESSION['uzmanlik_alanlari'] = $kullanici_bilgileri['brans'] ?? ''; 
                header("Location: ogretmen_ana_sayfa.php");
                exit();
            } else {
                
                session_unset();
                session_destroy();
                header("Location: giris.php?hata=rol_tanimsiz");
                exit();
            }
        } else {
            
            header("Location: giris.php?hata=giris_basarisiz");
            exit();
        }
    }
    
    elseif ($islem == 'profil_guncelle') {
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            
            $redirect_page = ($_SESSION['rol'] === 'ogrenci' ? 'ogrenci_profil.php' : 'ogretmen_profil.php');
            header("Location: " . $redirect_page . "?hata=csrf_gecersiz");
            exit();
        }

        
        if (!isset($_SESSION['giris_yapti']) || $_SESSION['giris_yapti'] !== true) {
            header("Location: giris.php?hata=yetkisiz");
            exit();
        }

        $kullanici_id = $_SESSION['kullanici_id'] ?? 0;
        $rol = $_SESSION['rol'] ?? '';
        $ad = $_POST['ad'] ?? '';
        $soyad = $_POST['soyad'] ?? '';
        $eposta = $_POST['eposta'] ?? '';
        $yeni_sifre = $_POST['yeni_sifre'] ?? null;
        $sifre_tekrar = $_POST['sifre_tekrar'] ?? null;
        
        $uzmanlik_alani = ($rol === 'ogretmen') ? ($_POST['uzmanlik_alanlari'] ?? null) : null; 

        
        if (empty($ad) || empty($soyad) || empty($eposta)) {
            $redirect_page = ($rol === 'ogrenci' ? 'ogrenci_profil.php' : 'ogretmen_profil.php');
            header("Location: " . $redirect_page . "?hata=bos_alanlar");
            exit();
        }
        
        if (!filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
            $redirect_page = ($rol === 'ogrenci' ? 'ogrenci_profil.php' : 'ogretmen_profil.php');
            header("Location: " . $redirect_page . "?hata=gecersiz_eposta");
            exit();
        }

        
        if (($yeni_sifre !== null && !empty($yeni_sifre)) || ($sifre_tekrar !== null && !empty($sifre_tekrar))) {
            if ($yeni_sifre !== $sifre_tekrar) {
                $redirect_page = ($rol === 'ogrenci' ? 'ogrenci_profil.php' : 'ogretmen_profil.php');
                header("Location: " . $redirect_page . "?hata=sifreler_eslesmiyor");
                exit();
            }
            if (strlen($yeni_sifre) < 6) { 
                $redirect_page = ($rol === 'ogrenci' ? 'ogrenci_profil.php' : 'ogretmen_profil.php');
                header("Location: " . $redirect_page . "?hata=sifre_kisa");
                exit();
            }
        } else {
            
            $yeni_sifre = null;
        }

        
        $guncelleme_basarili = kullaniciBilgileriniGuncelle($kullanici_id, $rol, $ad, $soyad, $eposta, $yeni_sifre, $uzmanlik_alani); 

        if ($guncelleme_basarili) {
           
            $_SESSION['ad'] = $ad;
            $_SESSION['soyad'] = $soyad;
            if ($rol === 'ogretmen') {
                $_SESSION['uzmanlik_alanlari'] = $uzmanlik_alani; 
            }
            $redirect_page = ($rol === 'ogrenci' ? 'ogrenci_profil.php' : 'ogretmen_profil.php');
            header("Location: " . $redirect_page . "?durum=guncelleme_basarili");
            exit();
        } else {
            $redirect_page = ($rol === 'ogrenci' ? 'ogrenci_profil.php' : 'ogretmen_profil.php');
            header("Location: " . $redirect_page . "?hata=guncelleme_basarisiz");
            exit();
        }
    }
    else {
        
        header("Location: giris.php?hata=gecersiz_istek");
        exit();
    }
} else {
    
    header("Location: giris.php");
    exit();
}
?>
