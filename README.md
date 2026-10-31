# CBT Madah

CBT Madah adalah aplikasi Computer Based Test berbasis **GarudaCBT 1.5.2** yang disesuaikan untuk pelaksanaan ujian sekolah. Aplikasi ini membantu operator, guru, proktor, dan siswa mengelola ujian dari bank soal sampai penilaian.

## Fitur utama

- Data master sekolah: tahun pelajaran, mapel, kelas, guru, dan siswa.
- Bank soal dengan tipe PG, PG Kompleks, dan Benar/Salah.
- Pembuatan jenis ujian, sesi, ruang, token, jadwal, alokasi peserta, dan pengawas.
- Randomisasi soal dan opsi yang dipersistenkan per siswa dan jadwal melalui `cbt_soal_siswa`.
- Penyimpanan jawaban berdasarkan identitas soal, sehingga refresh, reconnect, relogin, dan resume tidak mengubah urutan ujian.
- Autosave jawaban, penanda ragu-ragu, timer, auto-submit, dan pengumpulan manual.
- Monitoring proktor: aktivitas peserta, pelanggaran tab, keluarkan peserta, izinkan masuk kembali, dan perpanjangan waktu.
- Penilaian, rekap nilai, analisis soal, serta ekspor hasil.
- Akses Online dan LAN pada basis data yang sama.
- Integrasi Safe Exam Browser (SEB) dengan konfigurasi global aplikasi.
- Endpoint dan validasi server-side untuk menjaga integritas sesi, jadwal, jawaban, dan akses peran.

## Peran pengguna

- **Administrator** — mengatur data sekolah, pengguna, ujian, konfigurasi, dan database.
- **Guru** — membuat bank soal dan mengelola ujian sesuai kewenangan.
- **Proktor/Pengawas** — memantau pelaksanaan dan menangani peserta.
- **Siswa** — mengikuti ujian, menyimpan jawaban, menandai ragu-ragu, dan mengumpulkan ujian.

## Teknologi

- PHP dan CodeIgniter 3.1.13
- MySQL/MariaDB
- Bootstrap, AdminLTE, dan jQuery
- XAMPP atau Laragon untuk lingkungan lokal
- Safe Exam Browser untuk mode ujian yang diwajibkan sekolah

## Instalasi lokal

1. Siapkan PHP, MySQL/MariaDB, dan web server.
2. Salin proyek ke `htdocs` (XAMPP) atau `www` (Laragon).
3. Buat database dan import skema awal dari `assets/app/db/master.sql` bila diperlukan.
4. Atur koneksi database di `application/config/database.php`.
5. Atur URL aplikasi di `application/config/config.php`.
6. Jalankan migrasi atau menu pembaruan database sesuai versi aplikasi.
7. Login sebagai administrator, lengkapi data sekolah, lalu buat bank soal dan jadwal.

Panduan operasional tersedia di [docs/panduan/index.md](docs/panduan/index.md). Catatan teknis dan batasan pengujian tersedia di [Pengembangan.md](Pengembangan.md).

## Alur ujian siswa

1. Operator membuat bank soal dan jadwal.
2. Siswa login dan membuka jadwal yang aktif.
3. Sistem membuat mapping soal dan opsi sekali untuk kombinasi siswa, jadwal, dan bank.
4. Mapping digunakan kembali saat refresh, reconnect, relogin, resume, dan perpindahan Online/LAN.
5. Jawaban disimpan melalui endpoint existing dan dinilai berdasarkan ID soal serta nilai opsi yang stabil.
6. Ujian dapat dikumpulkan manual atau otomatis ketika waktu berakhir.

## Safe Exam Browser

Konfigurasi SEB menggunakan satu Start URL aplikasi. Config Key disimpan pada konfigurasi server lokal atau environment server dan tidak disertakan dalam repository. Gunakan [tools/buat-seb.php](tools/buat-seb.php) untuk membuat berkas `.seb` setelah konfigurasi deployment selesai.

Jangan memasukkan file `.seb`, `application/config/.seb_global.php`, `application/config/.encryption_key`, dump database, atau data peserta ke repository publik.

## Pemeriksaan keamanan dan pengembangan

Regression security dapat dijalankan dengan:

```bash
php tools/test-security-regressions.php
```

Lint PHP pada file yang diubah dengan:

```bash
php -l path/to/file.php
```

Sebelum produksi, lakukan uji end-to-end database, refresh/resume, reconnect Online-LAN, concurrent start, autosave, penilaian, dan submit pada server nyata. Detail status implementasi ada di [Pengembangan.md](Pengembangan.md).

## Lisensi

CBT Madah mempertahankan lisensi MIT dari GarudaCBT. Lisensi lengkap ada di [LICENCE.md](LICENCE.md) dan [license.txt](license.txt). Hak cipta dan pemberitahuan lisensi wajib dipertahankan pada salinan atau bagian substansial perangkat lunak.

MIT License

Copyright (c) 2020 GarudaCBT

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
