# Memulai dan memasang GarudaCBT

Tutorial ini memandu administrator server memasang GarudaCBT pada XAMPP atau lingkungan Apache, PHP, dan MySQL/MariaDB yang setara. Setelah selesai, Anda dapat masuk sebagai administrator dan melanjutkan pengaturan sekolah.

## Prasyarat

Siapkan server dan akses berikut sebelum membuka installer.

- Apache dengan dukungan `.htaccess` dan `mod_rewrite`.
- PHP 7.0 atau lebih baru dengan ekstensi `mysqli`, `dom`, `fileinfo`, `zip`, `json`, `gd`, `iconv`, dan `calendar`.
- MySQL atau MariaDB serta akun yang boleh membuat dan mengubah database.
- Ruang tulis untuk folder `application/cache`, `application/logs`, `backups`, dan `uploads`.
- HTTPS untuk server produksi, terutama jika menggunakan Safe Exam Browser.

## Mulai cepat

Ikuti langkah singkat ini untuk memasang aplikasi melalui installer web.

1. Salin folder aplikasi ke direktori web, misalnya `C:\xampp\htdocs\garudacbt`.
2. Nyalakan Apache dan MySQL dari XAMPP Control Panel.
3. Buka `http://localhost/garudacbt` pada browser.
4. Isi host database, pengguna, kata sandi, dan nama database, lalu klik **INSTALL / UPDATE**.
5. Lengkapi akun administrator dan profil sekolah sampai installer menampilkan tahap selesai.

Setelah instalasi selesai, buka halaman masuk dan gunakan akun administrator yang Anda buat.

## Pasang secara manual

Gunakan cara manual jika installer web tidak dapat membuat database atau menulis konfigurasi.

1. Buat database kosong dengan encoding UTF-8 melalui phpMyAdmin atau klien MySQL.
2. Impor `assets/app/db/master.sql` ke database tersebut.
3. Buka `application/config/database.php`.
4. Isi `hostname`, `username`, `password`, dan `database` sesuai server.
5. Buka kembali alamat aplikasi untuk menyelesaikan data administrator dan sekolah.
6. Jalankan migrasi keamanan dari folder aplikasi:

   ```powershell
   C:\xampp\php\php.exe index.php migrate CreateMigration
   ```

Perintah berhasil jika menampilkan pesan bahwa berkas migrasi telah dijalankan.

## Atur keamanan produksi

Sebelum siswa menggunakan aplikasi melalui jaringan, gunakan konfigurasi produksi berikut.

1. Pasang sertifikat TLS dan arahkan pengguna ke alamat `https://`.
2. Atur environment variable `GARUDACBT_ENCRYPTION_KEY` dengan nilai acak yang panjang pada konfigurasi web server.
3. Batasi akses publik ke folder `application`, `system`, `backups`, dan berkas konfigurasi.
4. Nonaktifkan tampilan error PHP pada produksi dan aktifkan pencatatan error ke berkas.
5. Buat backup database sebelum memasukkan data sekolah.

Jangan mengganti encryption key setelah sesi pengguna aktif karena perubahan tersebut dapat membatalkan sesi.

## Verifikasi instalasi

Periksa komponen berikut sebelum melanjutkan ke data master.

1. Masuk sebagai administrator.
2. Buka **Pengaturan > Profile Sekolah** dan pastikan data sekolah tampil.
3. Buka **Data Umum > Tahun Pelajaran** dan pastikan satu tahun serta semester berstatus aktif.
4. Buka **Pengaturan > Database > Backup** dan pastikan halaman dapat dimuat.
5. Jika memakai SEB, pastikan migrasi `20260812000100` telah membuat tabel `cbt_exam_attempt` dan `cbt_exam_event`.

## Langkah berikutnya

Lanjutkan pengaturan aplikasi melalui panduan berikut.

- [Panduan administrator](administrator.md) untuk mengisi data master dan akun pengguna.
- [Operasional ujian](operasional-ujian.md) untuk menyiapkan pelaksanaan CBT pertama.
- [Safe Exam Browser](safe-exam-browser.md) untuk membatasi lingkungan perangkat ujian.
- [Pemeliharaan](pemeliharaan.md) untuk membuat backup dan memperbarui aplikasi.

