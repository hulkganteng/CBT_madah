# CBT Madah — 50 Students Test Report

## 1. Executive Summary

**Status: CONDITIONAL PASS**

Alur ujian aktual berhasil dijalankan untuk satu sesi berisi 50 peserta, masing-masing 30 soal pilihan ganda. Seluruh 50 peserta berhasil login, memulai ujian, menyimpan 1.500 jawaban, membuka kembali ujian, dan menyelesaikan ujian. Pemeriksaan database tidak menemukan jawaban hilang, duplikat logis, orphan, attempt ganda, atau pencampuran peserta.

Status belum dinaikkan menjadi PASS penuh karena eksekusi ini merupakan smoke/load fungsional dipercepat melalui sesi HTTP stateful pada PHP built-in server lokal. Soak 30 menit melalui Playwright serta observasi p50/p95/p99, CPU, RAM, disk, dan worker produksi belum dilakukan.

## 2. Test Environment

| Komponen | Nilai |
|---|---|
| Aplikasi | CBT Madah / GarudaCBT |
| Framework | CodeIgniter 3 |
| PHP | 8.2.12 |
| Database | MariaDB lokal |
| Database target | `garuda_cbt_qa_staging` |
| Server uji | PHP built-in server, `127.0.0.1:8081` |
| Jadwal uji | ID 29 |
| Environment produksi | Tidak digunakan untuk data ujian |

Database staging dibuat dari `assets/app/db/master.sql` dan dimigrasikan sampai versi `20261111000400`. Fixture minimum sekolah, guru, level, sesi, bank soal, jadwal, dan peserta dibuat khusus pada staging.

## 3. Test Scope

- Login siswa melalui portal siswa dengan cookie, session, CSRF, dan pemeriksaan SEB aktif.
- Validasi ujian dan pembuatan/resume attempt.
- Pengambilan dan penyimpanan 30 soal per peserta.
- Randomisasi soal.
- Resume halaman ujian tanpa membuat attempt baru.
- Penyelesaian ujian serentak.
- Verifikasi kepemilikan jawaban, nilai, durasi, dan status akhir.

## 4. Test Data

| Data | Jumlah |
|---|---:|
| Peserta | 50 (`TEST_STUDENT_001`–`TEST_STUDENT_050`) |
| Sesi aktif | 1 |
| Jadwal | 1 |
| Soal PG | 30 |
| Opsi per soal | 4 |
| Jawaban yang diharapkan | 1.500 |

Kunci semua soal adalah opsi A agar hasil penilaian dapat diverifikasi secara deterministik. Kredensial fixture tidak dicantumkan dalam laporan.

## 5. Smoke Test Results

Gerbang satu peserta dengan 30 soal dijalankan sebelum beban 50 peserta.

| Pemeriksaan | Hasil |
|---|---|
| Halaman login siswa | PASS |
| Login valid | PASS |
| Validasi ujian | PASS |
| Mulai ujian | PASS |
| 30 soal tersedia | PASS |
| 30 jawaban tersimpan | PASS |
| Resume tidak menggandakan attempt | PASS |
| Penyelesaian dan nilai | PASS |
| SEB verified | PASS |

## 6. Concurrent Login Results

- Halaman login: 50/50 berhasil.
- Login: 50/50 berhasil.
- Session/cookie dipisahkan per peserta.
- Tidak ada kegagalan autentikasi pada eksekusi final.

## 7. Exam Start Results

- Validasi ujian: 50/50 berhasil.
- Halaman ujian: 50/50 berhasil.
- Attempt unik: 50 untuk 50 peserta.
- Alokasi sesi: 50 peserta pada tepat 1 sesi.
- Randomisasi: 50 urutan unik, tetap mencakup 30 soal yang benar.

## 8. Answer Submission Results

- Setiap gelombang soal berhasil dimuat 50/50.
- Setiap gelombang jawaban berhasil disimpan 50/50.
- Jawaban final: 1.500/1.500.
- Duplikat jawaban logis: 0.
- Jawaban orphan: 0.
- Kebocoran jawaban ke jadwal lain: 0.

## 9. Exam Completion Results

- Resume halaman ujian: 50/50.
- Submit akhir: 50/50.
- Attempt `SUBMITTED`: 50/50.
- Durasi berstatus selesai: 50/50.
- Attempt terverifikasi SEB: 50/50.

## 10. Database Integrity Results

| Pemeriksaan SQL read-only | Aktual | Harapan | Hasil |
|---|---:|---:|---|
| Peserta fixture | 50 | 50 | PASS |
| Peserta teralokasi | 50 | 50 | PASS |
| Sesi berbeda | 1 | 1 | PASS |
| Attempt unik | 50 | 50 | PASS |
| Attempt submitted | 50 | 50 | PASS |
| Jawaban fisik/logis | 1.500/1.500 | 1.500/1.500 | PASS |
| Orphan | 0 | 0 | PASS |
| Nilai tersimpan | 50 | 50 | PASS |
| PG benar minimum/maksimum | 30/30 | 30/30 | PASS |
| Nilai minimum/maksimum | 100/100 | 100/100 | PASS |

## 11. Performance Metrics

- Waktu alur 50 peserta dipercepat: **381,1 detik**.
- Koneksi database maksimum yang teramati sejak server hidup: **39** (`Max_used_connections`).
- Koneksi saat validasi akhir: **1**.
- Error transaksi bisnis pada eksekusi final: **0**.
- p50/p95/p99 per endpoint: **NOT TESTED**.
- CPU, RAM, swap, disk I/O, network, dan saturasi PHP worker: **NOT TESTED**.

Angka 381,1 detik bukan durasi ujian pengguna dan bukan ukuran latency per request; harness menjalankan 30 gelombang muat+simpan secara berurutan dengan 50 request bersamaan per gelombang.

## 12. Identified Defects

### QA-ENV-001 — CI_ENV tidak diwariskan oleh konfigurasi PHP CLI lokal

- Status: **CONFIRMED**.
- Dampak: server staging pertama jatuh ke environment production walaupun environment process telah disetel.
- Bukti: request login percobaan tercatat pada `garuda_cbt_production.login_attempts`, bukan staging.
- Data terdampak: tiga catatan login gagal untuk identitas fixture `TEST_STUDENT_001`; tidak ada akun, soal, jadwal, jawaban, atau nilai dibuat di production.
- Koreksi test: server diulang menggunakan `tests/staging-router.php` yang menetapkan `CI_ENV=development` sebelum front controller dimuat.
- Tindakan lanjutan: hapus tiga login-attempt tersebut hanya setelah persetujuan operator.

## 13. Root Cause Analysis

- **CONFIRMED:** `index.php` membaca environment dari `$_SERVER['CI_ENV']`, sedangkan konfigurasi PHP lokal tidak memetakan environment process ke `$_SERVER`.
- **CONFIRMED:** kegagalan awal login bukan masalah kredensial atau fitur ujian; request menuju database environment yang salah.
- **CONFIRMED:** setelah router staging eksplisit digunakan, smoke satu peserta dan simulasi 50 peserta lulus.

## 14. Risk Assessment

- Risiko fungsional untuk satu sesi 50 peserta pada skenario yang diuji: rendah.
- Risiko kapasitas produksi: sedang dan belum dapat disimpulkan, karena PHP built-in server tidak mewakili Apache/PHP-FPM produksi dan metrik latency/resource belum dikumpulkan.
- Risiko perilaku jangka panjang: belum diketahui karena soak 30 menit belum dijalankan.

## 15. Recommended Improvements

1. Jalankan ulang pada topologi staging yang sama dengan produksi (Apache/PHP worker dan konfigurasi database yang setara).
2. Rekam p50/p95/p99 per endpoint serta CPU, RAM, disk, koneksi, slow query, dan lock contention.
3. Tambahkan satu browser Playwright untuk bukti UI dan screenshot, sambil load HTTP stateful berjalan.
4. Jalankan soak 30 menit dengan think-time acak sebelum keputusan kapasitas final.
5. Gunakan bootstrap staging eksplisit atau ubah front controller agar `getenv('CI_ENV')` menjadi fallback yang tervalidasi.

## 16. Final Readiness Assessment

**CONDITIONAL PASS.** Bukti saat ini menunjukkan alur bisnis dan integritas database mampu menangani satu sesi 50 peserta × 30 soal tanpa kehilangan atau pencampuran data pada staging lokal. Bukti belum cukup untuk menyatakan kapasitas infrastruktur produksi memenuhi target p95 ≤ 2 detik selama ujian nyata 30 menit.
