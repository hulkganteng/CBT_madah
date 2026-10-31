# Dokumentasi penggunaan GarudaCBT

Dokumentasi ini membantu administrator, guru, proktor, dan siswa memasang serta menjalankan GarudaCBT. Mulai dari panduan sesuai peran Anda, lalu gunakan referensi dan pemecahan masalah saat diperlukan.

## Pilih panduan Anda

Gunakan tabel berikut untuk menemukan dokumen yang sesuai dengan tugas Anda.

| Pembaca | Mulai dari | Hasil |
|---|---|---|
| Administrator baru | [Memulai dan instalasi](memulai.md) | Aplikasi terpasang dan siap dikonfigurasi |
| Administrator sekolah | [Panduan administrator](administrator.md) | Data sekolah, pengguna, kelas, dan CBT siap |
| Guru | [Panduan guru](guru.md) | Bank soal, jadwal, dan hasil ujian terkelola |
| Proktor/pengawas | [Operasional ujian](operasional-ujian.md) | Ujian dapat dibuka, dipantau, dan ditutup |
| Siswa | [Panduan siswa](siswa.md) | Siswa dapat masuk dan mengerjakan ujian |
| Pengelola ujian aman | [Safe Exam Browser](safe-exam-browser.md) | Jadwal tervalidasi dengan Config Key atau Browser Exam Key |
| Teknisi lab | [Laboratorium Hybrid: Online dan LAN](lab-hybrid.md) | Server, Cloudflare Tunnel, 25 client, dua shortcut, dan Veyon siap |
| Operator server | [Backup, pembaruan, dan pemulihan](pemeliharaan.md) | Data terlindungi dan aplikasi dapat diperbarui |

## Alur penggunaan utama

GarudaCBT membutuhkan data master sebelum jadwal ujian dapat digunakan. Ikuti urutan berikut untuk pelaksanaan pertama.

1. Pasang aplikasi dan buat akun administrator.
2. Isi profil sekolah serta tahun pelajaran dan semester aktif.
3. Tambahkan mata pelajaran, guru, siswa, kelas, sesi, dan ruang.
4. Tempatkan siswa ke kelas, ruang, dan sesi ujian.
5. Siapkan PC server dan 25 client sesuai [Laboratorium Hybrid](lab-hybrid.md): Cloudflare Tunnel, alamat LAN, dan dua shortcut di desktop.
6. Buat bank soal, isi soal, lalu buat jadwal ujian.
7. Atur token dan kebijakan Safe Exam Browser jika diperlukan.
8. Cetak kartu peserta dan daftar hadir.
9. Pantau siswa selama ujian melalui **Aktivitas Siswa**.
10. Periksa hasil, analisis butir soal, dan rekap nilai.
11. Buat backup setelah pelaksanaan selesai.

## Dokumen pendukung

Gunakan dokumen berikut untuk informasi teknis dan penanganan kendala.

- [Referensi konfigurasi](referensi.md) menjelaskan persyaratan server, peran, status sesi, tabel keamanan, dan mode SEB.
- [Cara kerja keamanan ujian](keamanan-ujian.md) menjelaskan batas perlindungan aplikasi dan SEB.
- [Pemecahan masalah](troubleshooting.md) menyediakan tindakan untuk masalah login, soal, waktu, SEB, dan database.

## Ruang lingkup

Dokumentasi ini mengikuti kode aplikasi di repositori ini, termasuk modul keamanan ujian dan dukungan SEB. Versi ini adalah CBT murni: modul Rapor, Buku Induk, dan e-Learning di luar lingkup, sehingga menu dan rutenya sudah dihapus dan diblok server-side.

