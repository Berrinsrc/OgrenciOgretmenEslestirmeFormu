<?php

require_once 'ayarlar.php'; 

function girisYap(string $eposta, string $sifre) {
    global $pdo; 

    try {
        
        $stmt = $pdo->prepare("SELECT id, sifre_hash, rol FROM kullanicilar WHERE e_posta = :eposta");
        $stmt->bindParam(':eposta', $eposta, PDO::PARAM_STR);
        $stmt->execute();
        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($kullanici && password_verify($sifre, $kullanici['sifre_hash'])) {
            
            if ($kullanici['rol'] === 'ogrenci') {
                $stmt_detay = $pdo->prepare("SELECT ogrenci_id, adi, soyadi FROM ogrenciler WHERE kullanici_id = :kullanici_id");
                $stmt_detay->bindParam(':kullanici_id', $kullanici['id'], PDO::PARAM_INT);
                $stmt_detay->execute();
                $detay = $stmt_detay->fetch(PDO::FETCH_ASSOC);
                if ($detay) {
                    return [
                        'id' => $kullanici['id'], 
                        'rol' => 'ogrenci',
                        'ogrenci_id' => $detay['ogrenci_id'], 
                        'ad' => $detay['adi'],
                        'soyad' => $detay['soyadi']
                    ];
                }
            } elseif ($kullanici['rol'] === 'ogretmen') {
                $stmt_detay = $pdo->prepare("SELECT ogretmen_id, adi, soyadi, uzmanlik_alanlari FROM ogretmenler WHERE kullanici_id = :kullanici_id");
                $stmt_detay->bindParam(':kullanici_id', $kullanici['id'], PDO::PARAM_INT);
                $stmt_detay->execute();
                $detay = $stmt_detay->fetch(PDO::FETCH_ASSOC);
                if ($detay) {
                    return [
                        'id' => $kullanici['id'], 
                        'rol' => 'ogretmen',
                        'ogretmen_id' => $detay['ogretmen_id'], 
                        'ad' => $detay['adi'],
                        'soyad' => $detay['soyadi'],
                        'brans' => $detay['uzmanlik_alanlari'] 
                    ];
                }
            }
        }
        return false; 
    } catch (PDOException $e) {
        error_log("Giriş yapma hatası (DB): " . $e->getMessage()); 
        return false;
    }
}


function kayitOl(string $ad, string $soyad, string $eposta, string $sifre, string $rol, array $secilenDersler = [], ?string $uzmanlikAlani = null): string {
    global $pdo;

    try {
        $pdo->beginTransaction(); 

       
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM kullanicilar WHERE e_posta = :eposta");
        $stmt_check->bindParam(':eposta', $eposta, PDO::PARAM_STR);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $pdo->rollBack(); 
            return 'kayit_var';
        }

        
        $sifreHash = password_hash($sifre, PASSWORD_BCRYPT);

        
        $stmt_kullanici = $pdo->prepare("INSERT INTO kullanicilar (e_posta, sifre_hash, rol) VALUES (:eposta, :sifre_hash, :rol)");
        $stmt_kullanici->bindParam(':eposta', $eposta, PDO::PARAM_STR);
        $stmt_kullanici->bindParam(':sifre_hash', $sifreHash, PDO::PARAM_STR);
        $stmt_kullanici->bindParam(':rol', $rol, PDO::PARAM_STR);
        $stmt_kullanici->execute();
        $kullanici_id = $pdo->lastInsertId(); 

        
        if ($rol === 'ogrenci') {
            $stmt_ogrenci = $pdo->prepare("INSERT INTO ogrenciler (kullanici_id, adi, soyadi) VALUES (:kullanici_id, :adi, :soyadi)");
            $stmt_ogrenci->bindParam(':kullanici_id', $kullanici_id, PDO::PARAM_INT);
            $stmt_ogrenci->bindParam(':adi', $ad, PDO::PARAM_STR);
            $stmt_ogrenci->bindParam(':soyadi', $soyad, PDO::PARAM_STR);
            $stmt_ogrenci->execute();
        } elseif ($rol === 'ogretmen') {
            $stmt_ogretmen = $pdo->prepare("INSERT INTO ogretmenler (kullanici_id, adi, soyadi, uzmanlik_alanlari) VALUES (:kullanici_id, :adi, :soyadi, :uzmanlik_alanlari)");
            $stmt_ogretmen->bindParam(':kullanici_id', $kullanici_id, PDO::PARAM_INT);
            $stmt_ogretmen->bindParam(':adi', $ad, PDO::PARAM_STR);
            $stmt_ogretmen->bindParam(':soyadi', $soyad, PDO::PARAM_STR);
            $stmt_ogretmen->bindParam(':uzmanlik_alanlari', $uzmanlikAlani, PDO::PARAM_STR); 
            $stmt_ogretmen->execute();
            $ogretmen_id = $pdo->lastInsertId();

            
            if (!empty($secilenDersler)) {
                foreach ($secilenDersler as $ders_id) {
                    $stmt_ders = $pdo->prepare("INSERT INTO ogretmen_dersleri (ogretmen_id, ders_id) VALUES (:ogretmen_id, :ders_id)");
                    $stmt_ders->bindParam(':ogretmen_id', $ogretmen_id, PDO::PARAM_INT);
                    $stmt_ders->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
                    $stmt_ders->execute();
                }
            }
        }
        $pdo->commit(); 
        return 'basarili';
    } catch (PDOException $e) {
        $pdo->rollBack(); 
        error_log("Kayıt olma hatası: " . $e->getMessage()); 
        return 'hata';
    }
}


function kullaniciBilgileriniGuncelle(int $kullaniciId, string $rol, string $ad, string $soyad, string $eposta, ?string $yeniSifre = null, ?string $uzmanlikAlani = null): bool {
    global $pdo;

    try {
        $pdo->beginTransaction();

        $sorgu_kullanici = "UPDATE kullanicilar SET e_posta = :eposta";
        if ($yeniSifre !== null && !empty($yeniSifre)) {
            $sorgu_kullanici .= ", sifre_hash = :sifre_hash";
        }
        $sorgu_kullanici .= " WHERE id = :kullanici_id";

        $stmt_kullanici = $pdo->prepare($sorgu_kullanici);
        $stmt_kullanici->bindParam(':eposta', $eposta, PDO::PARAM_STR);
        if ($yeniSifre !== null && !empty($yeniSifre)) {
            $sifreHash = password_hash($yeniSifre, PASSWORD_BCRYPT);
            $stmt_kullanici->bindParam(':sifre_hash', $sifreHash, PDO::PARAM_STR);
        }
        $stmt_kullanici->bindParam(':kullanici_id', $kullaniciId, PDO::PARAM_INT);
        $stmt_kullanici->execute();

        
        if ($rol === 'ogrenci') {
            $stmt_rol = $pdo->prepare("UPDATE ogrenciler SET adi = :adi, soyadi = :soyadi WHERE kullanici_id = :kullanici_id");
            $stmt_rol->bindParam(':adi', $ad, PDO::PARAM_STR);
            $stmt_rol->bindParam(':soyadi', $soyad, PDO::PARAM_STR);
            $stmt_rol->bindParam(':kullanici_id', $kullaniciId, PDO::PARAM_INT);
            $stmt_rol->execute();
        } elseif ($rol === 'ogretmen') {
            $sorgu_rol = "UPDATE ogretmenler SET adi = :adi, soyadi = :soyadi";
            if ($uzmanlikAlani !== null) { 
                $sorgu_rol .= ", uzmanlik_alanlari = :uzmanlik_alanlari"; 
            }
            $sorgu_rol .= " WHERE kullanici_id = :kullanici_id";

            $stmt_rol = $pdo->prepare($sorgu_rol);
            $stmt_rol->bindParam(':adi', $ad, PDO::PARAM_STR);
            $stmt_rol->bindParam(':soyadi', $soyad, PDO::PARAM_STR);
            if ($uzmanlikAlani !== null) {
                $stmt_rol->bindParam(':uzmanlik_alanlari', $uzmanlikAlani, PDO::PARAM_STR); 
            }
            $stmt_rol->bindParam(':kullanici_id', $kullaniciId, PDO::PARAM_INT);
            $stmt_rol->execute();
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Kullanıcı bilgisi güncelleme hatası: " . $e->getMessage());
        return false;
    }
}


function kullaniciSil(int $kullaniciId): bool {
    global $pdo;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM kullanicilar WHERE id = :kullanici_id");
        $stmt->bindParam(':kullanici_id', $kullaniciId, PDO::PARAM_INT);
        $success = $stmt->execute();

        if ($success && $stmt->rowCount() > 0) {
            $pdo->commit();
            return true;
        } else {
            $pdo->rollBack();
            return false;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Kullanıcı silme hatası: " . $e->getMessage());
        return false;
    }
}
