# CBT Madah — Rencana Smoke Test 1 Sesi / 50 Peserta

## Batas Aman

- Target hanya instance lokal staging dengan database `garuda_cbt_qa_staging`.
- Database `garuda_cbt_production` dan `garuda_cbt_development` tidak boleh ditulis.
- Akun uji memakai pola `TEST_STUDENT_001` sampai `TEST_STUDENT_050`.
- Seluruh autentikasi, CSRF, session, token, SEB, dan validasi kepemilikan jawaban tetap aktif.
- Pengujian beban dihentikan bila smoke test satu peserta gagal pada alur kritis.

## Konfigurasi Skenario

- Satu sesi ujian aktif dengan 50 peserta.
- Satu bank berisi 30 soal pilihan ganda dan empat opsi.
- Durasi jadwal 30 menit; pelaksanaan otomatis dipercepat tanpa mengubah aturan bisnis.
- Jawaban benar dibuat deterministik agar nilai dapat diverifikasi.
- Soal diacak sesuai konfigurasi aplikasi.

## Tahapan

1. Buat database staging dari skema repository dan jalankan migrasi yang berlaku.
2. Jalankan instance aplikasi terpisah pada port lokal dengan environment staging.
3. Buat fixture terisolasi: sesi, jadwal, bank, 30 soal, dan 50 akun.
4. Jalankan smoke test satu peserta: login, dashboard, validasi ujian, mulai, 30 soal, simpan, muat ulang, selesai, dan larangan perubahan setelah selesai.
5. Jika seluruh langkah kritis lulus, jalankan 50 peserta secara bersamaan memakai sesi HTTP/cookie/CSRF terpisah.
6. Verifikasi database secara read-only: 50 attempt selesai, 1.500 jawaban logis, nilai benar, tidak ada duplikat, orphan, atau data silang.
7. Rekam latensi, error HTTP/aplikasi, durasi, dan metrik server/database yang tersedia.
8. Tulis hasil aktual ke `CBT_MADAH_50_STUDENTS_TEST_REPORT.md`; status tidak boleh PASS tanpa bukti eksekusi.

## Kriteria Awal

- Login, mulai, dan selesai: 50/50.
- Soal dan jawaban final: 30 per peserta / 1.500 total.
- Kehilangan jawaban, kebocoran antar peserta, dan salah nilai: 0.
- Error kritis: 0.
- p95 request dan simpan jawaban: target awal maksimal 2 detik.

## Pemulihan

Fixture dapat dibersihkan hanya dari database staging. Database staging dipertahankan setelah pengujian bila bukti SQL masih diperlukan untuk laporan.
