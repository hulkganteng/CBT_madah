# Backup, pembaruan, dan pemulihan

Panduan ini membantu operator server melindungi data dan memperbarui GarudaCBT. Selalu hentikan pelaksanaan ujian sebelum operasi database atau penggantian kode.

## Buat backup

Buat backup sebelum pembaruan, impor massal, koreksi nilai massal, dan penghapusan data.

1. Masuk sebagai administrator.
2. Buka **Pengaturan > Database > Backup**.
3. Jalankan backup database.
4. Unduh atau salin hasil backup ke media di luar server aplikasi.
5. Salin folder `uploads` jika backup aplikasi tidak menyertakan lampiran.
6. Catat tanggal, versi aplikasi, dan periode data.
7. Uji pemulihan pada server nonproduksi secara berkala.

Backup yang hanya tersimpan pada disk server yang sama tidak melindungi data dari kerusakan disk atau ransomware.

## Perbarui aplikasi

Gunakan jendela pemeliharaan ketika tidak ada siswa atau guru yang aktif.

1. Umumkan waktu pemeliharaan.
2. Buat backup database dan folder aplikasi.
3. Simpan salinan `application/config/database.php` dan konfigurasi web server.
4. Ganti kode aplikasi dengan versi baru tanpa menghapus folder `uploads` atau backup.
5. Pastikan konfigurasi database tetap menunjuk ke database yang benar.
6. Jalankan pembaruan database lewat **Pengaturan > Database > Update** sebagai administrator (halaman ini menolak akses tanpa login admin).
7. Jalankan migrasi keamanan:

   ```powershell
   C:\xampp\php\php.exe index.php migrate CreateMigration
   ```

8. Uji login administrator, guru, dan siswa.
9. Uji satu jadwal simulasi sebelum membuka layanan.

## Pulihkan database

Dua jalur tersedia: menu **Pemulihan Database** (`dbpemulihan`) untuk restore dari berkas `.sql.zip` di folder `backups/`, dan `mysqldump`/`mysql` untuk restore manual. Keduanya sudah diuji.

Pulihkan hanya dari backup yang telah diverifikasi dan sesuai dengan versi kode.

1. Hentikan akses pengguna ke aplikasi.
2. Buat salinan kondisi database saat ini untuk kebutuhan investigasi.
3. Impor backup ke database pemulihan terpisah terlebih dahulu.
4. Periksa jumlah pengguna, jadwal, jawaban, dan hasil.
5. Arahkan konfigurasi aplikasi ke database pemulihan atau ganti database produksi sesuai prosedur server.
6. Jalankan migrasi yang diperlukan untuk versi kode saat ini.
7. Uji alur login dan ujian sebelum membuka akses.

## Bersihkan data

Menu **Data Manager** sudah dinonaktifkan karena menjalankan `drop_table` pada semua tabel lewat `GET` tanpa konfirmasi. Membersihkan data sekarang dilakukan lewat backup, koreksi manual, atau restore, bukan lewat menu aplikasi.

1. Pastikan tidak ada ujian aktif.
2. Buat dan unduh backup penuh.
3. Tentukan tabel dan rentang data yang akan dibersihkan; catat di berita acara.
4. Minta persetujuan operator kedua untuk data nilai atau jawaban ujian.
5. Jalankan `DELETE` bersyarat lewat `mysql.exe`, jangan `DROP`/`TRUNCATE` seluruh tabel.

```powershell
# contoh: hapus jawaban satu jadwal yang dibatalkan
C:\xampp\mysql\bin\mysql.exe -u root -e "DELETE FROM garuda_cbt.cbt_soal_siswa WHERE id_jadwal=123;"
```

Setelah pembersihan, jalankan ulang rekap agar nilai tidak menyisakan baris yatim.

## Backup terjadwal

PRD FR-27 mewajibkan backup terjadwal, bukan hanya manual.

```powershell
# Daftarkan tugas harian pukul 05:30 dan 21:30 (sebelum & sesudah sesi ujian)
schtasks /Create /TN "GarudaCBT Backup Pagi" /TR "C:\xampp\php\php.exe C:\xampp\htdocs\garudacbt\index.php dbpemulihan backup" /SC DAILY /ST 05:30 /RU SYSTEM /RL HIGHEST /F
schtasks /Create /TN "GarudaCBT Backup Malam" /TR "C:\xampp\php\php.exe C:\xampp\htdocs\garudacbt\index.php dbpemulihan backup" /SC DAILY /ST 21:30 /RU SYSTEM /RL HIGHEST /F
schtasks /Query /TN "GarudaCBT Backup Pagi" /V /FO LIST
```

Berkas hasil tersimpan di `backups/` dengan nama `backup-db-YYYY-mm-dd-HH-MM-SS.sql.zip`. Salin hasil ke media di luar server minimal sekali sehari.

## Pantau kapasitas

Periksa sumber daya server sebelum dan selama periode ujian.

- Sisa ruang disk untuk database, log, backup, dan unggahan.
- Penggunaan memori serta CPU Apache dan MySQL/MariaDB.
- Ukuran tabel jawaban, attempt, dan event ujian.
- Pertumbuhan `application/logs`.
- Keberhasilan backup terbaru.

## Langkah berikutnya

Gunakan panduan berikut untuk verifikasi lanjutan.

- [Referensi konfigurasi](referensi.md) untuk lokasi konfigurasi dan tabel keamanan.
- [Pemecahan masalah](troubleshooting.md) untuk kegagalan database atau migrasi.
- [Operasional ujian](operasional-ujian.md) untuk daftar periksa sebelum membuka layanan.

