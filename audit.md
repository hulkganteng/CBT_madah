# Pengembangan.md — Perbaikan Audit GarudaCBT

## Peran
Bertindak sebagai Senior PHP Developer, Security Engineer, dan QA Engineer. Perbaiki kerentanan dan bug berdasarkan kode aktual dalam repository GarudaCBT.

**Tujuan:** Menghasilkan aplikasi CBT yang aman, stabil, dan menjaga integritas data ujian tanpa mengubah fitur atau UI yang sudah berjalan.

## Aturan Wajib

1. Audit ulang setiap temuan berdasarkan source code aktual. Jangan menganggap semua temuan pasti masih ada.
2. Kerjakan secara bertahap berdasarkan prioritas P0 → P1 → P2. Jangan melakukan perubahan besar sekaligus.
3. Pertahankan CodeIgniter 3, struktur database, endpoint, autentikasi, dan kompatibilitas PHP 8.2.
4. Jangan melakukan rewrite, migrasi framework, atau menambah dependency jika tidak diperlukan.
5. Jangan menghapus data peserta, soal, jawaban, jadwal, nilai, atau log ujian.
6. Gunakan migration yang aman dan kompatibel dengan data lama apabila perubahan skema diperlukan.
7. Jangan menjalankan restore database, penghapusan data, atau pengujian destruktif pada database produksi.
8. Jangan mengubah logika ujian yang tidak terkait dengan perbaikan.
9. Pertahankan PG, PG Kompleks, Benar/Salah, randomisasi, token, autosave, timer, resume, monitoring, dan Exam Browser.
10. Setiap perbaikan harus disertai pengujian regresi. Jangan menyatakan berhasil tanpa bukti pengujian.

## FASE 1 — P0: Keamanan dan Integritas Ujian

### SEC-001: Upload gambar berpotensi RCE
File: `application/controllers/Cbtbanksoal.php`

- Audit `previewWord()` dan `decode_data()`.
- Tolak MIME dan ekstensi berbahaya, termasuk `image/php`.
- Validasi konten gambar aktual, bukan hanya MIME dari client.
- Decode dan encode ulang ke PNG/JPEG menggunakan library gambar.
- Gunakan nama file acak dan ekstensi berdasarkan format hasil validasi.
- Arahkan upload ke mekanisme `Soalmedia` yang aman jika sesuai.
- Nonaktifkan eksekusi PHP dan skrip lainnya di direktori uploads melalui konfigurasi web server.
- Uji bahwa payload berbahaya ditolak dan gambar valid tetap dapat digunakan.

### SEC-002: SQL Injection pada timer
File:
- `application/controllers/Siswa.php`
- `application/models/Cbt_model.php`
- `application/hooks/Exam_security_hook.php`

- Audit `getTimer()` dan `getDurasiSiswa()`.
- Validasi ID menggunakan `ctype_digit()` dan normalisasi integer.
- Ganti SQL mentah dengan Query Builder atau parameter binding.
- Pastikan ID siswa, jadwal, dan attempt divalidasi sesuai konteks autentikasi.
- Jangan mengandalkan integer cast pada hook sebagai satu-satunya validasi.
- Uji input normal, tidak valid, dan pola SQL Injection secara aman.

### EXAM-001: Submit berhasil meskipun grading gagal
File:
- `application/controllers/Siswa.php`
- `application/hooks/Exam_security_hook.php`

- Audit `selesaiUjian()`, `olahNilai()`, dan status attempt.
- Pastikan penilaian berhasil sebelum attempt berstatus `SUBMITTED`.
- Gunakan transaksi database untuk operasi penilaian, pembaruan durasi, dan finalisasi attempt.
- Periksa hasil setiap operasi dan lakukan rollback apabila gagal.
- Pastikan hook tidak menandai attempt selesai hanya berdasarkan `payload.status`.
- Cegah double-submit dan grading ganda.
- Pertahankan jawaban apabila proses penilaian gagal.
- Uji kegagalan insert/update nilai dan retry submit.

**Selesaikan Fase 1 dan laporkan hasilnya sebelum melanjutkan Fase 2.**

## FASE 2 — P1: Keamanan Database dan Pemulihan

### DB-001: Restore database tidak aman
File: `application/controllers/Dbpemulihan.php`

- Audit penggunaan `mysqli::multi_query()`.
- Jangan menganggap transaksi dapat me-rollback DDL MySQL.
- Validasi file backup sebelum restore.
- Rancang restore menggunakan database sementara atau staging.
- Verifikasi tabel, struktur, dan data penting sebelum aktivasi.
- Gunakan maintenance mode untuk mencegah transaksi ujian selama pemulihan.
- Pastikan prosedur pemulihan dapat dibatalkan dengan aman sebelum perubahan destruktif.
- Jangan menguji restore pada database produksi.

### DB-002: Restore berjalan meskipun backup pengaman gagal
File: `application/controllers/Dbpemulihan.php`

- Buat fungsi backup mengembalikan status keberhasilan yang dapat diverifikasi.
- Pastikan file backup benar-benar dibuat, dapat dibaca, dan lolos pemeriksaan dasar.
- Hentikan restore jika backup pengaman gagal.
- Tangani disk penuh, permission error, dan file backup rusak.

### SEC-003: MySQL root tanpa password
File: `application/config/database.php`

- Audit seluruh konfigurasi environment.
- Jangan menggunakan MySQL root untuk aplikasi produksi.
- Gunakan akun database khusus dengan privilege minimum.
- Ambil kredensial dari environment atau konfigurasi lokal yang aman.
- Jangan menampilkan atau mencetak password ke log.
- Jangan mengubah kredensial server aktif secara otomatis; sediakan langkah konfigurasi manual bila diperlukan.
- Pastikan koneksi database tetap kompatibel dengan deployment LAN.

## FASE 3 — P1/P2: Stabilitas Aplikasi

### EXAM-002: Endpoint event tanpa rate limit
File:
- `application/controllers/Examguard.php`
- `application/libraries/Exam_attempt_service.php`

Terapkan rate limit per peserta/attempt, deduplikasi event identik, dan pembatasan log tanpa menghilangkan event keamanan yang penting.

### EXAM-003: Perpanjangan waktu tidak konsisten
File: `application/libraries/Exam_attempt_service.php`

Pastikan pembaruan `cbt_exam_attempt`, `cbt_durasi_siswa`, dan audit event konsisten dalam transaksi. Tangani kegagalan salah satu update.

### IMPORT-001: Nomor soal duplikat saat impor paralel
File: `application/controllers/Excelsoal.php`

Gunakan locking database yang sesuai, termasuk `SELECT ... FOR UPDATE` dalam transaksi, sebelum menentukan nomor soal dan kapasitas bank. Evaluasi unique constraint setelah memeriksa data lama.

### IMPORT-002: Import Excel boros memori
File: `application/libraries/Excel_question_draft_importer.php`

Batasi pembacaan workbook sebelum `toArray()`, gunakan read filter/dimensi worksheet, dan cegah file besar menghabiskan memori PHP. Pertahankan batas 2.000 baris.

### CONFIG-001: Header proxy tidak tervalidasi
File: `application/config/config.php`

Gunakan host allowlist atau base URL tepercaya. Percayai `X-Forwarded-Proto` hanya dari reverse proxy yang tervalidasi. Pertahankan kompatibilitas LAN, HTTPS, Cloudflare Tunnel, dan konfigurasi Safe Exam Browser.

### AUTH-001: Lupa password tidak berfungsi
File:
- `application/config/ion_auth.php`
- `application/controllers/Auth.php`

Perbaiki alur reset password dengan pengiriman email yang benar, atau nonaktifkan fitur tersebut dan arahkan pengguna ke administrator. Jangan menampilkan sukses palsu.

## FASE 4 — Pengujian dan Regresi

Tambahkan pengujian sesuai pola yang sudah ada di repository, mencakup:

- Upload gambar valid dan payload berbahaya.
- SQL Injection pada parameter timer.
- Kegagalan grading dan retry submit.
- Double-submit.
- Backup gagal dan restore tidak dijalankan.
- Restore staging dengan dump rusak.
- Impor soal paralel ke bank yang sama.
- Perpanjangan waktu saat update gagal.
- Login, token, randomisasi, autosave, resume, dan submit normal.
- Isolasi jawaban antarjadwal dan antarpeserta.
- Pengujian 25 peserta serentak menggunakan database pengujian.

## Cara Kerja

1. Baca struktur repository dan identifikasi implementasi aktual.
2. Buat daftar temuan dengan status `CONFIRMED`, `NOT REPRODUCED`, atau `ALREADY FIXED`.
3. Perbaiki hanya temuan yang terkonfirmasi.
4. Kerjakan satu kelompok perubahan kecil.
5. Jalankan syntax check, pengujian relevan, dan regresi.
6. Catat file yang berubah serta dampaknya.
7. Jangan lanjut ke fase berikutnya apabila masih ada kegagalan kritis.
8. Jika suatu perubahan berisiko pada data produksi, hentikan perubahan tersebut dan jelaskan prosedur aman.

## Format Laporan

Setelah setiap fase, tampilkan:

- ID temuan dan status.
- Penyebab masalah.
- File/fungsi yang diperbaiki.
- Ringkasan perubahan.
- Hasil pengujian: PASS, FAIL, atau NOT TESTED.
- Risiko yang masih tersisa.
- Langkah manual jika diperlukan.

**Mulai sekarang dengan audit ulang Fase 1, lalu implementasikan hanya perbaikan P0 yang terkonfirmasi. Jangan langsung menjalankan seluruh fase sekaligus.**