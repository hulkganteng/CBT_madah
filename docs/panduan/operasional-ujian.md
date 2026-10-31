# Menjalankan ujian

Panduan ini memberikan prosedur kerja administrator, guru, proktor, dan pengawas dari persiapan sampai rekap. Gunakan daftar periksa untuk setiap jadwal agar perubahan dapat dilacak.

## Siapkan data ujian

Selesaikan data yang menjadi prasyarat sebelum membuat jadwal.

1. Aktifkan tahun pelajaran dan semester yang benar.
2. Periksa kelas, siswa, mata pelajaran, guru, sesi, dan ruang.
3. Tempatkan siswa pada ruang dan sesi.
4. Buat nomor peserta.
5. Tetapkan pengawas.
6. Buat jenis ujian.

## Siapkan bank dan jadwal

Gunakan bank yang sudah ditinjau oleh guru mata pelajaran.

1. Pastikan jumlah soal pada setiap jenis memenuhi jumlah yang akan ditampilkan.
2. Pastikan setiap soal objektif memiliki kunci dan bobot yang benar.
3. Buat jadwal dengan tanggal, durasi, dan durasi minimum yang sesuai.
4. Tentukan pengacakan soal dan opsi.
5. Aktifkan token jika token akan diumumkan di ruang ujian.
6. Atur SEB ke **Opsional** saat simulasi dan **Wajib** setelah seluruh perangkat lulus uji.
7. Aktifkan jadwal setelah pemeriksaan selesai.

## Lakukan simulasi

Jalankan simulasi menggunakan kondisi jaringan dan perangkat yang sama dengan ujian.

1. Buat akun dan jadwal khusus simulasi.
2. Masuk dari setiap jenis perangkat yang digunakan.
3. Uji login, token, pemuatan soal, penyimpanan jawaban, timer, dan selesai ujian.
4. Jika memakai SEB, uji Config Key pada setiap platform.
5. Putuskan jaringan satu perangkat selama satu menit, lalu sambungkan kembali.
6. Periksa hasil dan event audit setelah simulasi.
7. Catat kapasitas perangkat dan jaringan yang berhasil digunakan bersamaan.

## Buka pelaksanaan

Pada hari ujian, lakukan pemeriksaan berikut sebelum membagikan token.

1. Pastikan waktu server sesuai zona `Asia/Jakarta`.
2. Pastikan Apache, MySQL/MariaDB, penyimpanan, dan jaringan aktif.
3. Pastikan `cloudflared` berstatus `Running` dan domain Online dapat dibuka; pastikan juga alamat LAN dapat dibuka. Tetapkan jalur yang dipakai sesi ini dan umumkan.
4. Buka **Aktivitas Siswa** pada perangkat proktor.
5. Pastikan jadwal berstatus aktif serta tanggal dan sesi sesuai.
6. Pastikan pengawas memiliki daftar peserta dan berita acara.
7. Bagikan berkas `.seb` lebih dahulu jika jadwal mewajibkan SEB.
8. Tampilkan token hanya setelah pengawas mengizinkan mulai.

## Pantau ujian

Gunakan status aplikasi bersama pengawasan fisik di ruang ujian.

1. Pantau kolom status, heartbeat, waktu tersisa, dan pelanggaran pindah tab per peserta.
2. Cocokkan permintaan **Izinkan Masuk Kembali** atau **Perpanjang Waktu** dengan nama dan nomor peserta.
3. Catat waktu, alasan, perangkat, dan tindakan untuk setiap intervensi.
4. Jika jaringan terputus, minta siswa mempertahankan halaman ujian. Bila perlu pindah jalur, ikuti prosedur failover pada [Laboratorium Hybrid](lab-hybrid.md#6-prosedur-pindah-jalur-di-tengah-ujian).
5. Jika server bermasalah luas, hentikan perubahan dan pulihkan layanan sebelum meminta siswa memuat ulang.

Jangan menilai satu event perpindahan tab sebagai bukti tunggal. Periksa pola event, catatan pengawas, dan kondisi perangkat.

## Pindah jalur (Online ke LAN)

Lakukan hanya bila gangguan memang pada jalur Online, bukan pada PC server.

1. Umumkan satu instruksi yang sama ke seluruh peserta.
2. Alihkan client secara massal lewat Veyon ke shortcut **CBT Lokal (Darurat)**.
3. Minta peserta login ulang di alamat LAN dengan akun dan token yang sama; sesi browser baru memang diperlukan.
4. Periksa **Aktivitas Siswa**: semua peserta harus kembali berstatus *sedang mengerjakan* sebelum ujian dilanjutkan.
5. Catat jalur, jam perpindahan, dan penyebabnya di berita acara.

## Tutup ujian

Setelah sesi selesai, pastikan jawaban dan status peserta telah tersimpan.

1. Tunggu seluruh siswa menekan selesai atau timer berakhir.
2. Cocokkan jumlah peserta selesai dengan daftar hadir.
3. Catat siswa yang tidak selesai atau mengalami kendala.
4. Nonaktifkan jadwal atau hentikan distribusi token.
5. Buka hasil, analisis soal, dan rekap nilai.
6. Buat backup database.

## Tangani insiden

Gunakan tingkat respons berikut agar tindakan proporsional dengan dampak.

| Dampak | Contoh | Tindakan pertama |
|---|---|---|
| Satu siswa | Login, SEB, atau perangkat gagal | Pindahkan perangkat atau lakukan reset terverifikasi |
| Satu ruang | Access point atau listrik ruang gagal | Catat waktu, pertahankan halaman, pulihkan jaringan |
| Semua peserta | Database atau web server berhenti | Hentikan instruksi siswa, pulihkan server, verifikasi data |
| Dugaan kompromi | Key tersebar atau akun dipakai pihak lain | Nonaktifkan jadwal, rotasi token/key, simpan bukti |

## Langkah berikutnya

Setelah pelaksanaan, lanjutkan dengan tindakan berikut.

- [Laboratorium Hybrid](lab-hybrid.md) untuk penyiapan server, Cloudflare Tunnel, Veyon, dan dua shortcut client.
- [Pemeliharaan](pemeliharaan.md) untuk backup dan pemulihan.
- [Pemecahan masalah](troubleshooting.md) untuk mendiagnosis kegagalan spesifik.
- [Cara kerja keamanan ujian](keamanan-ujian.md) untuk menafsirkan event audit.

