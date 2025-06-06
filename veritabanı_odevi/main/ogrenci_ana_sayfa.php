<?php
// ogrenci_ana_sayfa.php
// Öğrenci paneli ana sayfası. Randevu oluşturma ve bekleyen randevuları gösterme.

require_once 'ayarlar.php'; // Veritabanı bağlantısı ve oturum için ayarlar dosyasını dahil ediyoruz.

// Oturum kontrolü: Öğrenci giriş yapmış mı?
if (!isset($_SESSION['giris_yapti']) || $_SESSION['rol'] !== 'ogrenci') {
    header("Location: giris.php"); // Giriş yapmamışsa veya öğrenci değilse giriş sayfasına yönlendir.
    exit();
}

$ogrenci_adi = $_SESSION['ad'] ?? 'Misafir';
$ogrenci_soyadi = $_SESSION['soyad'] ?? '';
$ogrenci_id = $_SESSION['ogrenci_id'] ?? 0;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Paneli - Öğrenci Mentor Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Özel stil eklemeleri */
        .container {
            max-width: 1200px;
        }
        .card {
            background-color: #fff;
            border-radius: 0.75rem; /* rounded-lg */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* shadow-md */
            padding: 2rem; /* p-8 */
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-blue-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Öğrenci Paneli</h1>
            <div class="flex items-center space-x-4">
                <span>Hoş geldin, <?php echo htmlspecialchars($ogrenci_adi . ' ' . $ogrenci_soyadi); ?>!</span>
                <a href="cikis.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                    Çıkış Yap
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto p-4 flex flex-col md:flex-row gap-6 mt-6">
        <!-- Randevu Oluşturma Kartı -->
        <div class="w-full md:w-1/2 card">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Randevu Oluştur</h2>
            
            <form id="randevu-form" class="space-y-4">
                <div>
                    <label for="randevu_tarihi" class="block text-gray-700 text-sm font-semibold mb-2">Tarih:</label>
                    <input type="date" id="randevu_tarihi" name="randevu_tarihi" 
                           class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>

                <div>
                    <label for="randevu_saati" class="block text-gray-700 text-sm font-semibold mb-2">Saat:</label>
                    <input type="time" id="randevu_saati" name="randevu_saati" 
                           class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>

                <div>
                    <label for="ders_id" class="block text-gray-700 text-sm font-semibold mb-2">Ders Seçin:</label>
                    <select id="ders_id" name="ders_id" 
                            class="shadow-sm border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                            required>
                        <option value="">Ders seçiniz</option>
                        <!-- Dersler buraya JavaScript ile yüklenecek -->
                    </select>
                </div>

                <div>
                    <label for="ogretmen_id" class="block text-gray-700 text-sm font-semibold mb-2">Öğretmen Seçin:</label>
                    <select id="ogretmen_id" name="ogretmen_id" 
                            class="shadow-sm border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                            required disabled>
                        <option value="">Öğretmen seçiniz</option>
                        <!-- Öğretmenler buraya JavaScript ile yüklenecek -->
                    </select>
                </div>

                <div class="flex items-center justify-center pt-4">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md shadow-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Randevu Başvurusu Yap
                    </button>
                </div>
            </form>

            <div id="randevu-sonuc-mesaji" class="mt-4 text-center text-sm font-medium"></div>
        </div>

        <!-- Bekleyen Randevu İstekleri Kartı -->
        <div class="w-full md:w-1/2 card">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Bekleyen Randevu İstekleri</h2>
            <div id="bekleyen-randevular" class="space-y-4">
                <!-- Randevular buraya JavaScript ile yüklenecek -->
                <p class="text-gray-500 text-center">Bekleyen randevunuz bulunmamaktadır.</p>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-4 text-center text-sm mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Öğrenci Mentor Sistemi. Tüm hakları saklıdır.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dersSelect = document.getElementById('ders_id');
            const ogretmenSelect = document.getElementById('ogretmen_id');
            const randevuForm = document.getElementById('randevu-form');
            const randevuSonucMesaji = document.getElementById('randevu-sonuc-mesaji');
            const bekleyenRandevularDiv = document.getElementById('bekleyen-randevular');
            const randevuTarihiInput = document.getElementById('randevu_tarihi');
            const randevuSaatiInput = document.getElementById('randevu_saati');

            // --- Dersleri Yükle ---
            function loadDersler() {
                // ders_getir.php dosyası daha önce oluşturulmuş olmalı
                fetch('ders_getir.php') 
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mevcut seçenekleri temizle (varsa)
                            dersSelect.innerHTML = '<option value="">Ders seçiniz</option>';
                            data.dersler.forEach(ders => {
                                const option = document.createElement('option');
                                option.value = ders.ders_id;
                                option.textContent = ders.ders_adi;
                                dersSelect.appendChild(option);
                            });
                        } else {
                            randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-red-600';
                            randevuSonucMesaji.textContent = 'Dersler yüklenirken hata: ' + data.message;
                        }
                    })
                    .catch(error => {
                        console.error('Dersleri yüklerken hata:', error);
                        randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-red-600';
                        randevuSonucMesaji.textContent = 'Dersler yüklenirken bir ağ hatası oluştu.';
                    });
            }

            // --- Öğretmenleri Yükle (Ders, Tarih ve Saat Seçimine Göre) ---
            function loadOgretmenler() {
                const dersId = dersSelect.value;
                const randevuTarihi = randevuTarihiInput.value;
                const randevuSaati = randevuSaatiInput.value;

                ogretmenSelect.innerHTML = '<option value="">Öğretmen seçiniz</option>'; // Önceki öğretmenleri temizle
                ogretmenSelect.disabled = true; // Öğretmen seçimini devre dışı bırak

                // Eğer ders, tarih ve saat seçilmişse öğretmenleri getir
                if (dersId && randevuTarihi && randevuSaati) {
                    fetch(`musait_ogretmenleri_getir.php?ders_id=${dersId}&randevu_tarihi=${randevuTarihi}&randevu_saati=${randevuSaati}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (data.ogretmenler.length > 0) {
                                    data.ogretmenler.forEach(ogretmen => {
                                        const option = document.createElement('option');
                                        option.value = ogretmen.ogretmen_id;
                                        option.textContent = ogretmen.adi + ' ' + ogretmen.soyadi;
                                        ogretmenSelect.appendChild(option);
                                    });
                                    ogretmenSelect.disabled = false; // Öğretmen seçimini etkinleştir
                                } else {
                                    randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-yellow-600';
                                    randevuSonucMesaji.textContent = 'Bu dersi, seçilen tarih ve saatte veren müsait öğretmen bulunamadı.';
                                }
                            } else {
                                randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-red-600';
                                randevuSonucMesaji.textContent = 'Öğretmenler yüklenirken hata: ' + data.message;
                            }
                        })
                        .catch(error => {
                            console.error('Öğretmenleri yüklerken hata:', error);
                            randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-red-600';
                            randevuSonucMesaji.textContent = 'Öğretmenler yüklenirken bir ağ hatası oluştu.';
                        });
                } else {
                    randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-blue-600';
                    randevuSonucMesaji.textContent = 'Lütfen ders, tarih ve saat seçiminizi yapın.';
                }
            }

            // --- Randevu Oluşturma Formu Gönderimi ---
            randevuForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Formun varsayılan gönderimini engelle

                const formData = new FormData(this); // Form verilerini al

                fetch('randevu_olustur.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-green-600';
                        randevuSonucMesaji.textContent = data.message;
                        randevuForm.reset(); // Formu temizle
                        dersSelect.value = ""; // Ders seçimini sıfırla
                        ogretmenSelect.innerHTML = '<option value="">Öğretmen seçiniz</option>'; // Öğretmen seçimini sıfırla
                        ogretmenSelect.disabled = true; // Öğretmen seçimini devre dışı bırak
                        loadBekleyenRandevular(); // Randevu oluşturulduktan sonra bekleyen randevuları güncelle
                    } else {
                        randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-red-600';
                        randevuSonucMesaji.textContent = 'Randevu oluşturulamadı: ' + data.message;
                    }
                })
                .catch(error => {
                    console.error('Randevu oluşturulurken hata:', error);
                    randevuSonucMesaji.className = 'mt-4 text-center text-sm font-medium text-red-600';
                    randevuSonucMesaji.textContent = 'Randevu oluşturulurken bir ağ hatası oluştu.';
                });
            });

            // --- Bekleyen Randevuları Yükle ---
            function loadBekleyenRandevular() {
                fetch('ogrenci_randevularini_getir.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bekleyenRandevularDiv.innerHTML = ''; // Önceki randevuları temizle
                            if (data.randevular.length > 0) {
                                data.randevular.forEach(randevu => {
                                    const randevuCard = document.createElement('div');
                                    randevuCard.className = 'bg-white p-4 rounded-md shadow-sm border border-gray-200';
                                    let statusClass = '';
                                    let statusText = '';
                                    // Randevu durumuna göre renk ve metin ayarlarız.
                                    switch (randevu.randevu_durumu) {
                                        case 'beklemede':
                                            statusClass = 'text-yellow-600';
                                            statusText = 'Beklemede';
                                            break;
                                        case 'onaylandi':
                                            statusClass = 'text-green-600';
                                            statusText = 'Onaylandı';
                                            break;
                                        case 'reddedildi':
                                            statusClass = 'text-red-600';
                                            statusText = 'Reddedildi';
                                            break;
                                        default:
                                            statusClass = 'text-gray-600';
                                            statusText = randevu.randevu_durumu;
                                            break;
                                    }

                                    randevuCard.innerHTML = `
                                        <p class="font-semibold text-gray-800">${randevu.ders_adi} - ${randevu.ogretmen_adi} ${randevu.ogretmen_soyadi}</p>
                                        <p class="text-sm text-gray-600">Tarih: ${randevu.randevu_tarihi} Saat: ${randevu.randevu_baslangic_saati}</p>
                                        <p class="text-sm text-gray-600">Durum: <span class="font-bold ${statusClass}">
                                            ${statusText}
                                        </span></p>
                                        ${randevu.ogretmen_aciklama ? `<p class="text-sm text-gray-700 mt-2">Öğretmen Notu: ${randevu.ogretmen_aciklama}</p>` : ''}
                                    `;
                                    bekleyenRandevularDiv.appendChild(randevuCard);
                                });
                            } else {
                                bekleyenRandevularDiv.innerHTML = '<p class="text-gray-500 text-center">Bekleyen randevunuz bulunmamaktadır.</p>';
                            }
                        } else {
                            bekleyenRandevularDiv.innerHTML = `<p class="text-red-600 text-center">Randevular yüklenirken hata: ${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Bekleyen randevuları yüklerken hata:', error);
                        bekleyenRandevularDiv.innerHTML = '<p class="text-red-600 text-center">Bekleyen randevuları yüklerken bir ağ hatası oluştu.</p>';
                    });
            }

            // --- Olay Dinleyicileri ---
            // Ders, tarih veya saat değiştiğinde öğretmenleri yükle
            dersSelect.addEventListener('change', loadOgretmenler);
            randevuTarihiInput.addEventListener('change', loadOgretmenler);
            randevuSaatiInput.addEventListener('change', loadOgretmenler);

            // Sayfa yüklendiğinde dersleri ve bekleyen randevuları yükle
            loadDersler();
            loadBekleyenRandevular();
        });
    </script>
</body>
</html>
