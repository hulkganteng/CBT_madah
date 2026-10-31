# Cara kerja keamanan ujian

GarudaCBT mengurangi peluang manipulasi dengan memindahkan keputusan penting ke server dan mendukung Safe Exam Browser. Perlindungan ini harus dipadukan dengan pengawasan, jaringan yang dikelola, kebijakan sekolah, dan prosedur insiden.

## Batas kepercayaan

Browser siswa tidak menjadi sumber kebenaran untuk identitas, waktu, atau kepemilikan soal.

Server melakukan pemeriksaan berikut:

- Mengambil identitas siswa dari session akun yang masuk.
- Mengganti ID siswa, jadwal, dan bank yang dikirim form dengan data server.
- Memastikan soal jawaban dimiliki siswa pada jadwal dan bank tersebut.
- Menghitung waktu berjalan serta deadline dari server.
- Menolak attempt yang selesai, kedaluwarsa, atau terikat pada browser lain.
- Menyaring kunci jawaban dari payload pemuatan soal.

## Siklus attempt

Satu siswa memiliki paling banyak satu attempt untuk satu jadwal.

```text
Belum mulai
    |
    v
IN_PROGRESS ---- deadline terlewati ----> EXPIRED
    |
    +---- selesai berhasil -------------> SUBMITTED
```

Heartbeat memperbarui tanda aktivitas, tetapi tidak memperpanjang deadline.

## Peran Safe Exam Browser

SEB membatasi lingkungan perangkat, sedangkan server memeriksa bahwa konfigurasi SEB sesuai dengan jadwal.

Server menerima validasi melalui header klasik atau JavaScript API. Nilai hash bergantung pada URL, sehingga key asli tidak dikirim sebagai teks biasa pada setiap request.

SEB tidak menggantikan HTTPS, keamanan akun, pengawasan fisik, atau pengelolaan perangkat. Perangkat yang sudah dikompromikan tetap memerlukan kontrol tambahan di luar aplikasi.

## Arti event audit

Event browser merupakan sinyal yang perlu dikorelasikan dengan data lain.

Contohnya, `PAGE_HIDDEN` dapat terjadi karena siswa berpindah aplikasi, tetapi juga dapat dipicu dialog sistem. `OFFLINE` dapat menandakan gangguan jaringan ruang, bukan tindakan siswa.

Gunakan event bersama:

- Catatan waktu proktor.
- Rekaman gangguan jaringan atau listrik.
- Status attempt dan heartbeat.
- Pola berulang pada satu siswa dibandingkan seluruh ruang.
- Kebijakan sekolah yang telah diumumkan.

## Data sensitif

Config Key, Browser Exam Key, kredensial database, encryption key, backup, dan akun administrator merupakan data sensitif.

Batasi akses berdasarkan tugas, jangan kirim key lengkap melalui grup siswa, dan rotasi konfigurasi jika key tersebar. Simpan backup di lokasi terenkripsi dengan akses terbatas.

## Keterbatasan

Tidak ada aplikasi web yang dapat menjamin kecurangan tidak terjadi.

Kontrol saat ini tidak dapat sepenuhnya mencegah kamera eksternal, bantuan orang lain di luar pengawasan, perangkat kedua, kebocoran soal sebelum ujian, atau kompromi sistem operasi. Gunakan variasi soal, pengacakan, durasi wajar, pengawasan, dan analisis hasil untuk melengkapi kontrol teknis.

