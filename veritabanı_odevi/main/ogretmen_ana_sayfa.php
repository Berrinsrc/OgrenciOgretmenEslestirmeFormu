<?php
// ogretmen_ana_sayfa.php
// Bu öğretmenlerin ana sayfası! Burada öğrencilerin listesini ve randevu isteklerini görebilirler.

// Veritabanı bağlantısı için ayarlar dosyasını çağırırız.
require_once 'ayarlar.php';

// Giriş yapıp yapmadığını ve öğretmen olup olmadığını kontrol ederiz.
// Eğer giriş yapmadıysa veya öğretmen değilse, giriş sayfasına yönlendirilir.
if (!isset($_SESSION['giris_yapti']) || $_SESSION['giris_yapti'] !== true || $_SESSION['rol'] !== 'ogretmen') {
    header("Location: giris.php?hata=yetkisiz");
    exit();
}

// Oturumdan öğretmenin adını, soyadını ve öğretmen ID'sini alırız.
$ad = htmlspecialchars($_SESSION['ad']);
$soyad = htmlspecialchars($_SESSION['soyad']);
$ogretmen_id = htmlspecialchars($_SESSION['ogretmen_id']); // Öğretmenin ID'si

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Ana Sayfası</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Panellere güzel bir gölge efekti veririz. */
        .panel-shadow {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        /* Randevu durumlarına göre farklı renkler veririz. */
        .status-beklemede {
            color: #f97316; /* Turuncu */
        }
        .status-onaylandi {
            color: #22c55e; /* Yeşil */
        }
        .status-reddedildi {
            color: #ef4444; /* Kırmızı */
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-green-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-xl font-bold">Öğretmen Paneli</a>
            <div>
                <span class="mr-4">Hoş geldin, <?php echo $ad . " " . $soyad; ?>!</span>
                <a href="cikis.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Çıkış Yap
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-10 p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Sol Panel: Öğrenci Listesi -->
            <div class="bg-white p-8 rounded-lg panel-shadow">
                <h2 class="text-2xl font-bold text-green-700 mb-6">Sistemdeki Öğrenciler</h2>
                <div id="ogrenciListesi" class="space-y-4">
                    <!-- Öğrenciler buraya otomatik olarak yüklenecek -->
                    <p class="text-gray-500 text-center" id="noStudentsMessage">Sistemde kayıtlı öğrenci bulunmamaktadır.</p>
                </div>
                <p id="ogrenciYukleniyor" class="text-sm text-gray-500 mt-4 text-center hidden">Öğrenciler yükleniyor...</p>
            </div>

            <!-- Sağ Panel: Bekleyen Randevu Talepleri -->
            <div class="bg-white p-8 rounded-lg panel-shadow">
                <h2 class="text-2xl font-bold text-green-700 mb-6">Bekleyen Randevu Talepleri</h2>
                <div id="randevuTalepleri" class="space-y-4">
                    <!-- Randevu talepleri buraya otomatik olarak yüklenecek -->
                    <p class="text-gray-500 text-center" id="noAppointmentsMessageTeacher">Size ait bekleyen randevu talebi bulunmamaktadır.</p>
                </div>
                <p id="randevuYukleniyorTeacher" class="text-sm text-gray-500 mt-4 text-center hidden">Randevu talepleri yükleniyor...</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ogrenciListesiDiv = document.getElementById('ogrenciListesi');
            const randevuTalepleriDiv = document.getElementById('randevuTalepleri');
            const ogrenciYukleniyor = document.getElementById('ogrenciYukleniyor');
            const noStudentsMessage = document.getElementById('noStudentsMessage');
            const randevuYukleniyorTeacher = document.getElementById('randevuYukleniyorTeacher');
            const noAppointmentsMessageTeacher = document.getElementById('noAppointmentsMessageTeacher');

            // Tüm öğrencileri veritabanından çekip sol panele doldururuz.
            function loadAllStudents() {
                ogrenciListesiDiv.innerHTML = ''; // Önceki listeyi temizleriz.
                noStudentsMessage.classList.add('hidden'); // Öğrenci yok yazısını gizleriz.
                ogrenciYukleniyor.classList.remove('hidden'); // Yükleniyor yazısını gösteririz.

                // Not: 'tum_ogrencileri_getir.php' dosyasının var olduğunu varsayıyoruz.
                // Eğer yoksa, bu fonksiyon çalışmayacaktır.
                fetch('tum_ogrencileri_getir.php') 
                    .then(response => response.json())
                    .then(data => {
                        ogrenciYukleniyor.classList.add('hidden'); // Yükleniyor yazısını gizleriz.
                        if (data.success && data.ogrenciler.length > 0) {
                            data.ogrenciler.forEach(ogrenci => {
                                const ogrenciItem = document.createElement('div');
                                ogrenciItem.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
                                ogrenciItem.innerHTML = `
                                    <p class="text-lg font-semibold">${ogrenci.adi} ${ogrenci.soyadi}</p>
                                    <p class="text-gray-600">E-posta: ${ogrenci.e_posta}</p>
                                `;
                                ogrenciListesiDiv.appendChild(ogrenciItem);
                            });
                        } else {
                            noStudentsMessage.classList.remove('hidden'); // Öğrenci yok yazısını gösteririz.
                            console.warn('Öğrenci bulunamadı veya hata:', data.message || 'Veri yok.');
                        }
                    })
                    .catch(error => {
                        ogrenciYukleniyor.classList.add('hidden');
                        console.error('Öğrenciler yüklenirken ağ hatası:', error);
                        ogrenciListesiDiv.innerHTML = '<p class="text-red-500 text-center">Öğrenciler yüklenirken hata oluştu.</p>';
                    });
            }

            // Sana gelen randevu taleplerini veritabanından çekip sağ panele doldururuz.
            function loadTeacherAppointments() {
                randevuTalepleriDiv.innerHTML = ''; // Önceki talepleri temizleriz.
                noAppointmentsMessageTeacher.classList.add('hidden'); // Talep yok yazısını gizleriz.
                randevuYukleniyorTeacher.classList.remove('hidden'); // Yükleniyor yazısını gösteririz.

                // Not: 'ogretmen_randevularini_getir.php' dosyasının var olduğunu varsayıyoruz.
                // Eğer yoksa, bu fonksiyon çalışmayacaktır.
                fetch('ogretmen_randevularini_getir.php') 
                    .then(response => response.json())
                    .then(data => {
                        randevuYukleniyorTeacher.classList.add('hidden'); // Yükleniyor yazısını gizleriz.
                        if (data.success && data.randevular.length > 0) {
                            data.randevular.forEach(randevu => {
                                const randevuItem = document.createElement('div');
                                randevuItem.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
                                let statusClass = '';
                                let statusText = '';
                                // Randevu durumuna göre renk ve metin ayarlarız.
                                switch (randevu.randevu_durumu) {
                                    case 'beklemede':
                                        statusClass = 'status-beklemede';
                                        statusText = 'Beklemede';
                                        break;
                                    case 'onaylandi':
                                        statusClass = 'status-onaylandi';
                                        statusText = 'Onaylandı';
                                        break;
                                    case 'reddedildi':
                                        statusClass = 'status-reddedildi';
                                        statusText = 'Reddedildi';
                                        break;
                                    default:
                                        statusClass = 'text-gray-600';
                                        statusText = randevu.randevu_durumu;
                                        break;
                                }

                                randevuItem.innerHTML = `
                                    <p class="text-lg font-semibold">${randevu.ders_adi} Dersi Randevu Talebi</p>
                                    <p class="text-gray-600">Öğrenci: ${randevu.ogrenci_adi} ${randevu.ogrenci_soyadi}</p>
                                    <p class="text-gray-600">Tarih: ${randevu.randevu_tarihi}</p>
                                    <p class="text-gray-600">Saat: ${randevu.randevu_baslangic_saati}</p>
                                    <p class="font-bold ${statusClass}">Durum: ${statusText}</p>
                                    ${randevu.ogretmen_aciklama ? `<p class="text-gray-700 text-sm mt-2">Açıklama: <span class="italic">${randevu.ogretmen_aciklama}</span></p>` : ''}
                                    ${randevu.randevu_durumu === 'beklemede' ? `
                                        <div class="mt-4 flex flex-col space-y-2">
                                            <textarea id="aciklama_${randevu.randevu_id}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="2" placeholder="Açıklama (isteğe bağlı)"></textarea>
                                            <div class="flex space-x-2">
                                                <button data-id="${randevu.randevu_id}" data-status="onaylandi" class="status-button bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex-1">Onayla</button>
                                                <button data-id="${randevu.randevu_id}" data-status="reddedildi" class="status-button bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex-1">Reddet</button>
                                            </div>
                                            <div id="message_${randevu.randevu_id}" class="mt-2 text-sm text-center font-semibold"></div>
                                        </div>
                                    ` : ''}
                                `;
                                randevuTalepleriDiv.appendChild(randevuItem);
                            });
                            addStatusButtonListeners(); // Butonlara tıklama özelliği ekleriz.
                        } else {
                            noAppointmentsMessageTeacher.classList.remove('hidden'); // Talep yok yazısını gösteririz.
                            console.warn('Öğretmen randevu talepleri bulunamadı veya hata:', data.message || 'Veri yok.');
                        }
                    })
                    .catch(error => {
                        randevuYukleniyorTeacher.classList.add('hidden');
                        console.error('Randevu talepleri yüklenirken ağ hatası:', error);
                        randevuTalepleriDiv.innerHTML = '<p class="text-red-500 text-center">Randevu talepleri yüklenirken hata oluştu.</p>';
                    });
            }

            // Onaylama/Reddetme butonlarına tıklandığında ne olacağını belirleriz.
            function addStatusButtonListeners() {
                document.querySelectorAll('.status-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const randevuId = this.dataset.id; // Hangi randevu olduğunu anlarız.
                        const status = this.dataset.status; // Onay mı, red mi olduğunu anlarız.
                        const aciklamaInput = document.getElementById(`aciklama_${randevuId}`);
                        const aciklama = aciklamaInput ? aciklamaInput.value : ''; // Öğretmenin yazdığı açıklamayı alırız.
                        const messageDiv = document.getElementById(`message_${randevuId}`);

                        const formData = new FormData();
                        formData.append('randevu_id', randevuId);
                        formData.append('durum', status);
                        formData.append('aciklama', aciklama);

                        fetch('randevu_durumunu_guncelle.php', { // randevu_durumunu_guncelle.php dosyasına bilgileri göndeririz.
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                messageDiv.textContent = data.message;
                                messageDiv.className = 'mt-2 text-sm text-center text-green-500 font-semibold';
                                loadTeacherAppointments(); // Listeyi güncelleriz.
                            } else {
                                messageDiv.textContent = data.message;
                                messageDiv.className = 'mt-2 text-sm text-center text-red-500 font-semibold';
                            }
                        })
                        .catch(error => {
                            console.error('Randevu durumu güncellenirken ağ hatası:', error);
                            messageDiv.textContent = 'İnternet bağlantısı hatası oluştu.';
                            messageDiv.className = 'mt-2 text-sm text-center text-red-500 font-semibold';
                        });
                    });
                });
            }

            // Sayfa ilk açıldığında öğrenci listesini ve randevu taleplerini yükleriz.
            loadAllStudents();
            loadTeacherAppointments();
        });
    </script>
</body>
</html>
