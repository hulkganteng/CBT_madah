# Memecahkan masalah GarudaCBT

Panduan ini menyediakan tindakan diagnosis untuk masalah yang paling sering mengganggu pemasangan dan ujian. Catat pesan error, waktu kejadian, akun, jadwal, dan URL sebelum mengubah data.

## Aplikasi tidak dapat dibuka

Periksa layanan dan alamat aplikasi terlebih dahulu.

1. Pastikan Apache dan MySQL/MariaDB aktif.
2. Pastikan URL memuat folder aplikasi yang benar.
3. Periksa log Apache/PHP dan `application/logs`.
4. Pastikan `application/config/database.php` menggunakan host serta database yang benar.
5. Pastikan `.htaccess` dapat dibaca dan `mod_rewrite` aktif.

## Hanya satu jalur (Online atau LAN) yang bisa dibuka

Kedua jalur memakai server dan database yang sama, jadi kegagalan biasanya ada pada jaringan atau `base_url`.

1. Uji dari PC server sendiri: `http://localhost/garudacbt/auth` dan `http://127.0.0.1/garudacbt/auth`. Bila keduanya jalan, aplikasi sehat dan masalah ada di jaringan.
2. Jalur Online gagal: jalankan `Get-Service cloudflared`. Bila tidak `Running`, `Start-Service cloudflared`, lalu periksa `cloudflared tunnel info garudacbt`. Periksa juga DNS hostname di dashboard Cloudflare.
3. Jalur LAN gagal: dari PC client, `ping 192.168.1.10`. Bila gagal, periksa IP statis server, kabel/switch, dan Windows Firewall (izinkan port 80).
4. Aset atau form mengarah ke alamat yang salah: `base_url` terkunci. Lihat [Laboratorium Hybrid](lab-hybrid.md#3-base_url-dinamis).
5. Pindahkan sesi ke jalur yang sehat sesuai [prosedur pindah jalur](lab-hybrid.md#6-prosedur-pindah-jalur-di-tengah-ujian); jangan menunggu jalur yang rusak pulih.

## Login selalu gagal

Pisahkan masalah kredensial dari masalah periode aktif.

1. Pastikan username tidak memiliki spasi tambahan.
2. Periksa akun melalui **User Management**.
3. Pastikan akun aktif dan berada pada grup yang benar.
4. Untuk siswa, pastikan siswa masuk kelas pada tahun pelajaran dan semester aktif.
5. Jika banyak akun gagal bersamaan, periksa session, cookie, HTTPS, dan waktu server.

## Siswa tidak melihat jadwal

Jadwal tampil hanya jika data peserta dan waktu memenuhi syarat aplikasi.

1. Pastikan jadwal aktif dan tanggal saat ini berada pada rentang jadwal.
2. Pastikan siswa berada pada kelas yang dipilih bank soal.
3. Pastikan siswa memiliki ruang dan sesi.
4. Pastikan waktu sesi belum berakhir.
5. Pastikan tahun pelajaran dan semester siswa sama dengan periode aktif.

## Soal tidak tampil

Periksa bank soal dan pembentukan soal peserta.

1. Pastikan jumlah soal per jenis mencukupi pengaturan jadwal.
2. Pastikan soal tidak kosong dan bank tersimpan.
3. Pastikan siswa sudah melewati validasi token.
4. Periksa respons endpoint soal dan log aplikasi.
5. Jangan mengaktifkan kembali endpoint legacy `saveSoalSiswa`; endpoint tersebut sengaja dinonaktifkan untuk keamanan.

## Jawaban tidak tersimpan

Server menolak jawaban yang tidak cocok dengan siswa, jadwal, bank, soal, atau batas waktu.

1. Pastikan sesi siswa masih aktif.
2. Pastikan timer belum melewati deadline server.
3. Periksa koneksi jaringan dan respons HTTP pada browser.
4. Pastikan soal tersebut memang dipetakan kepada siswa pada jadwal yang sama.
5. Periksa status pada `cbt_exam_attempt` dan log aplikasi.

Jangan mengubah `id_siswa`, `id_jadwal`, atau `id_bank` dari browser. Server mengambil identitas siswa dari akun yang masuk.

## Tombol mulai SEB nonaktif

Pesan pada kotak status menunjukkan penyebab validasi yang paling dekat.

1. Pastikan jadwal memakai key dari berkas `.seb` yang sedang dibuka.
2. Pastikan Config Key terdiri dari 64 karakter heksadesimal tanpa spasi.
3. Pastikan Start URL menggunakan hostname, HTTPS, port, dan path yang sama dengan URL aplikasi.
4. Jika memakai Browser Exam Key, masukkan key untuk versi dan platform SEB tersebut.
5. Pastikan JavaScript aktif dan tidak diblokir proxy.
6. Ubah jadwal ke **Opsional** hanya untuk diagnosis terkontrol, bukan untuk melewati kebijakan produksi.

## SEB berhasil pada Windows tetapi gagal pada macOS/iOS

WKWebView modern tidak mengirim key melalui header dan menggunakan JavaScript API.

1. Gunakan versi SEB yang mendukung JavaScript API.
2. Pastikan `assets/app/js/exam-guard.js` dapat dimuat.
3. Pastikan Content Security Policy atau proxy tidak memblokir API halaman.
4. Uji kembali Start URL persis seperti yang dikonfigurasi.
5. Periksa respons `examguard/status/{id_jadwal}`.

## Timer berbeda atau ujian langsung berakhir

Deadline percobaan dihitung server agar tidak dapat diperpanjang dari browser.

1. Sinkronkan waktu dan zona waktu server ke `Asia/Jakarta`.
2. Periksa durasi jadwal dan nilai `started_at` serta `deadline_at` pada attempt.
3. Periksa data lama pada `cbt_durasi_siswa`.
4. Lakukan reset hanya melalui prosedur proktor yang sah.

## Migrasi gagal

Migrasi memerlukan hak untuk membuat tabel, indeks, kolom, dan foreign key.

1. Buat backup database.
2. Pastikan pengguna database memiliki hak `ALTER`, `CREATE`, `INDEX`, dan `REFERENCES`.
3. Pastikan tabel `master_siswa` dan `cbt_jadwal` menggunakan InnoDB.
4. Jalankan kembali perintah migrasi.
5. Periksa error MySQL/MariaDB jika versi migrasi tidak berubah.

## Eskalasi masalah

Jika masalah belum selesai, kumpulkan data berikut tanpa menyertakan kata sandi atau key SEB lengkap.

- Waktu kejadian dan zona waktu.
- Peran, ID jadwal, serta ID siswa yang terdampak.
- URL dan status HTTP.
- Versi PHP, MySQL/MariaDB, browser, dan SEB.
- Potongan log yang relevan.
- Langkah yang dapat mengulang masalah.

## Langkah berikutnya

Gunakan dokumen berikut untuk memahami konfigurasi yang diperiksa.

- [Referensi konfigurasi](referensi.md) untuk nama tabel, endpoint, dan mode.
- [Laboratorium Hybrid](lab-hybrid.md) untuk Cloudflare Tunnel, alamat LAN, dan Veyon.
- [Cara kerja keamanan ujian](keamanan-ujian.md) untuk status attempt dan event.
- [Pemeliharaan](pemeliharaan.md) untuk backup sebelum tindakan pemulihan.

