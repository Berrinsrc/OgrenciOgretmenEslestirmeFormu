<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Öğrenci Mentor Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div class="flex-grow flex items-center justify-center py-8 px-4">
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Yeni Hesap Oluştur</h1>
            
            <?php
            // ayarlar.php dosyasını dahil et
            require_once 'ayarlar.php';

            // Hata mesajlarını göster
            if (isset($_GET['hata'])) {
                $hata_mesaji = '';
                switch ($_GET['hata']) {
                    case 'kayit_var':
                        $hata_mesaji = 'Bu e-posta adresi zaten kayıtlı!';
                        break;
                    case 'kayit':
                        $hata_mesaji = 'Kayıt sırasında bir hata oluştu. Lütfen tüm alanları doldurduğunuzdan emin olun.';
                        break;
                    case 'gecersiz_eposta':
                        $hata_mesaji = 'Geçersiz e-posta adresi biçimi.';
                        break;
                    case 'bos_alanlar':
                        $hata_mesaji = 'Lütfen tüm alanları doldurun.';
                        break;
                    case 'db_hata':
                        $hata_mesaji = 'Veritabanı hatası oluştu. Lütfen daha sonra tekrar deneyin.';
                        break;
                    default:
                        $hata_mesaji = 'Bilinmeyen bir hata oluştu.';
                        break;
                }
                echo '<div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md text-center" role="alert">' . htmlspecialchars($hata_mesaji) . '</div>';
            }

            // Başarı mesajını göster
            if (isset($_GET['kayit']) && $_GET['kayit'] == 'basarili') {
                echo '<div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md text-center" role="alert">Kaydınız başarıyla tamamlandı! Lütfen <a href="giris.php" class="font-semibold text-green-700 hover:underline">giriş yapın</a>.</div>';
            }

            // Dersleri veritabanından çek
            $dersler = [];
            try {
                $stmt_dersler = $pdo->query("SELECT ders_id, ders_adi FROM dersler ORDER BY ders_adi ASC");
                $dersler = $stmt_dersler->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Dersler çekilirken hata: " . $e->getMessage());
                // Kullanıcıya hata gösterme, sadece logla
            }
            ?>

            <form class="space-y-6" method="POST" action="kontrol.php?islem=kayit">
                <div>
                    <label for="ad" class="block text-sm font-medium text-gray-700 mb-1">Adınız:</label>
                    <input type="text" id="ad" name="ad" class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Adınızı girin" required>
                </div>
                <div>
                    <label for="soyad" class="block text-sm font-medium text-gray-700 mb-1">Soyadınız:</label>
                    <input type="text" id="soyad" name="soyad" class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Soyadınızı girin" required>
                </div>
                <div>
                    <label for="eposta" class="block text-sm font-medium text-gray-700 mb-1">E-posta Adresiniz:</label>
                    <input type="email" id="eposta" name="eposta" autocomplete="email" class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="ornek@eposta.com" required>
                </div>
                <div>
                    <label for="sifre" class="block text-sm font-medium text-gray-700 mb-1">Şifreniz:</label>
                    <input type="password" id="sifre" name="sifre" autocomplete="new-password" class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="••••••••" required>
                </div>
                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700 mb-1">Hesap Türü:</label>
                    <select id="rol" name="rol" class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
                        <option value="ogrenci">Öğrenci</option>
                        <option value="ogretmen">Öğretmen</option>
                    </select>
                </div>

                <!-- Öğretmen rolü seçildiğinde gösterilecek ders seçim alanı -->
                <div id="dersSecimAlani" class="hidden">
                    <label for="dersler" class="block text-sm font-medium text-gray-700 mb-1">Verebileceğiniz Dersler (Birden Fazla Seçilebilir):</label>
                    <select id="dersler" name="dersler[]" multiple class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm h-40">
                        <?php foreach ($dersler as $ders): ?>
                            <option value="<?php echo htmlspecialchars($ders['ders_id']); ?>">
                                <?php echo htmlspecialchars($ders['ders_adi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-2 text-sm text-gray-500">Ctrl (Windows) veya Command (Mac) tuşuna basılı tutarak birden fazla ders seçebilirsiniz.</p>
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Kayıt Ol
                </button>
            </form>
            <p class="mt-8 text-center text-sm text-gray-600">
                Zaten bir hesabınız var mı?
                <a href="giris.php" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">
                    Giriş Yapın
                </a>
            </p>
        </div>
    </div>
    <footer class="bg-gray-800 text-white p-4 text-center text-sm mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Öğrenci Mentor Sistemi. Tüm hakları saklıdır.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rolSelect = document.getElementById('rol');
            const dersSecimAlani = document.getElementById('dersSecimAlani');

            function toggleDersSecimAlani() {
                if (rolSelect.value === 'ogretmen') {
                    dersSecimAlani.classList.remove('hidden');
                } else {
                    dersSecimAlani.classList.add('hidden'); // Corrected from classList.add
                }
            }

            // Sayfa yüklendiğinde ve rol değiştiğinde kontrol et
            rolSelect.addEventListener('change', toggleDersSecimAlani);
            toggleDersSecimAlani(); // Sayfa ilk yüklendiğinde de çalıştır
        });
    </script>
</body>
</html>
