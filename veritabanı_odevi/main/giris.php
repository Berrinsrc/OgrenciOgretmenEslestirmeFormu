<?php
// giris.php
// Bu dosya, kullanıcıların sisteme giriş yapmasını sağlayan ana sayfadır.

// Veritabanı bağlantı ayarlarını ve oturum yönetimini içeren dosyayı dahil et.
// ÖNEMLİ: Bu dosyanın 'ayarlar.php' olarak adlandırıldığından ve diğer PHP dosyalarınla aynı klasörde olduğundan emin ol.
require_once 'ayarlar.php';

// Eğer kullanıcı zaten giriş yapmışsa, rolüne göre ilgili ana sayfaya yönlendir.
if (isset($_SESSION['giris_yapti']) && $_SESSION['giris_yapti'] === true) {
    if (isset($_SESSION['rol'])) {
        if ($_SESSION['rol'] == 'ogrenci') {
            header("Location: ogrenci_ana_sayfa.php"); // Öğrenci ana sayfasına yönlendir
            exit();
        } elseif ($_SESSION['rol'] == 'ogretmen') {
            header("Location: ogretmen_ana_sayfa.php"); // Öğretmen ana sayfasına yönlendir
            exit();
        }
    }
    // Eğer rol tanımlı değilse veya bilinmeyen bir rolse, kullanıcı giriş sayfasında kalır.
    // Güvenlik için burada bir çıkış yaptırıp giriş sayfasına yönlendirme de düşünülebilir.
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Öğrenci Mentor Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Form kapsayıcısının minimum yüksekliğini ayarlarız */
        .form-container {
            min-height: calc(100vh - 8rem);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Başlık kısmı -->
    <header class="bg-blue-600 text-white p-4 text-center">
        <h1 class="text-xl font-semibold">Öğrenci Mentor Sistemi</h1>
    </header>

    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Hesabınıza giriş yapın
                </h2>
            </div>
            <!-- Giriş formu -->
            <!-- Formun action kısmı 'kontrol.php?islem=giris' olarak ayarlı. -->
            <!-- Bu, giriş bilgilerini 'kontrol.php' dosyasına göndereceği anlamına gelir. -->
            <form class="mt-8 space-y-6" method="POST" action="kontrol.php?islem=giris">
                <input type="hidden" name="remember" value="true">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="eposta" class="sr-only">E-posta adresi</label>
                        <input id="eposta" name="eposta" type="email" autocomplete="email" required
                               class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="E-posta adresiniz">
                    </div>
                    <div>
                        <label for="sifre" class="sr-only">Şifre</label>
                        <input id="sifre" name="sifre" type="password" autocomplete="current-password" required
                               class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Şifreniz">
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Giriş Yap
                    </button>
                </div>
            </form>
            <p class="mt-6 text-center text-sm text-gray-600">
                Hesabınız yok mu?
                <a href="kayit_ol.php" class="font-medium text-blue-600 hover:text-blue-500">
                    Hemen Kayıt Olun!
                </a>
            </p>

            <?php
            // Hata ve başarı mesajlarını burada gösteririz
            if (isset($_GET['hata'])) {
                $hata_mesaji = '';
                switch ($_GET['hata']) {
                    case 'giris':
                        $hata_mesaji = 'Hatalı e-posta veya şifre! Lütfen bilgilerinizi kontrol edin.';
                        break;
                    case 'bos_alanlar':
                        $hata_mesaji = 'Lütfen e-posta ve şifre alanlarını eksiksiz doldurun.';
                        break;
                    case 'yetkisiz':
                        $hata_mesaji = 'Bu sayfaya erişim yetkiniz bulunmamaktadır. Lütfen giriş yapın.';
                        break;
                    case 'rol_tanimsiz':
                        $hata_mesaji = 'Kullanıcı rolü sistemde tanımlı değil. Lütfen bir yönetici ile iletişime geçin.';
                        break;
                    case 'db_hata':
                        $hata_mesaji = 'Veritabanı ile ilgili bir sorun oluştu. Lütfen daha sonra tekrar deneyin.';
                        break;
                    case 'gecersiz_istek':
                        $hata_mesaji = 'Geçersiz istek. Lütfen formu kullanarak giriş yapın.';
                        break;
                    default:
                        $hata_mesaji = 'Giriş sırasında bilinmeyen bir hata oluştu. Lütfen tekrar deneyin.';
                }
                echo '<div class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md text-center" role="alert">' . htmlspecialchars($hata_mesaji) . '</div>';
            }

            // Kayıt sonrası başarı mesajı
            if (isset($_GET['kayit_basarili'])) {
                 echo '<div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md text-center" role="alert">Kaydınız başarıyla tamamlandı! Şimdi giriş yapabilirsiniz.</div>';
            }

            // Çıkış yapıldığında gösterilecek mesaj
            if (isset($_GET['cikis']) && $_GET['cikis'] == 'basarili') {
                echo '<div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md text-center" role="alert">Başarıyla çıkış yaptınız. Tekrar görüşmek üzere!</div>';
            }
            ?>
        </div>
    </main>

    <!-- Alt bilgi (footer) kısmı -->
    <footer class="bg-gray-800 text-white p-4 text-center text-sm">
        <p>&copy; <?php echo date("Y"); ?> Öğrenci Mentor Sistemi. Tüm hakları saklıdır.</p>
    </footer>

</body>
</html>
