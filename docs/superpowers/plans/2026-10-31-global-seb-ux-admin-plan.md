# Implementasi SEB Global dan UX Admin Jadwal

## Context

SEB sudah diwajibkan pada portal siswa, tetapi validasi kunci masih dibaca dari setiap baris `cbt_jadwal`. Form jadwal juga meminta mode, Config Key, dan Browser Exam Key sehingga operator harus memahami detail SEB yang seharusnya menjadi kebijakan aplikasi. Dengan satu Start URL yang sama untuk Online dan LAN, kebijakan tersebut dapat dipusatkan.

## Approach

1. Tambahkan konfigurasi global di `application/config/exam_baku.php` untuk mode `REQUIRED`, Start URL kanonik, Config Key, dan Browser Exam Key. Nilai rahasia dibaca dari environment atau berkas konfigurasi lokal yang tidak dipublikasikan.
2. Ubah `Exam_attempt_service` agar `verifySeb()`, `verifySebClient()`, dan pengumpulan key memakai konfigurasi global. Kunci jadwal lama hanya dipakai sebagai fallback kompatibilitas bila kebijakan global belum diprovisikan, dengan pesan error yang jelas.
3. Ubah `Exam_security_hook` agar input SEB per jadwal tidak lagi wajib dan jadwal baru selalu dipaksa mengikuti kebijakan global.
4. Sederhanakan `application/views/cbt/jadwal/add.php` menjadi informasi read-only bahwa SEB wajib dan gunakan file `.seb` resmi; jangan hapus kolom database lama.
5. Audit label/urutan pada view jadwal admin tanpa mengubah endpoint, randomisasi, penyimpanan jawaban, timer, session, atau penilaian.
6. Tambahkan pemeriksaan konfigurasi yang gagal aman jika global Start URL dan minimal satu kunci valid belum tersedia.

## Key decisions

- Satu Start URL kanonik dipakai oleh file `.seb` di Online dan LAN.
- Mode global selalu `REQUIRED`; tidak ada pilihan `OFF` atau `OPTIONAL` pada UI jadwal.
- Tidak ada migrasi schema baru pada tahap ini.
- Rahasia tidak ditulis ke source control; konfigurasi lokal/environment menjadi sumbernya.
- Perubahan dilakukan pada service/hook/view yang sudah menjadi titik validasi, bukan refactor controller/model lama.

## Files to modify

- `application/config/exam_baku.php`: nilai kebijakan SEB global.
- `application/libraries/Exam_attempt_service.php`: pembacaan kebijakan global dan verifikasi hash/URL.
- `application/hooks/Exam_security_hook.php`: normalisasi input dan pemaksaan kebijakan global saat simpan jadwal.
- `application/views/cbt/jadwal/add.php`: UX informasi SEB global.
- `docs/panduan/safe-exam-browser.md`: instruksi satu file `.seb` dan satu Start URL.

## Out of scope

- Randomisasi, `cbt_soal_siswa`, jawaban, timer, attempt, submit, penilaian, dan database ujian tidak diubah.
- Tidak membuat endpoint baru atau tabel baru.
- Tidak mengubah keamanan jaringan, Cloudflare Tunnel, atau konfigurasi SEB client selain dokumentasi Start URL.

## Verification

- `php -l` untuk setiap PHP yang diubah.
- `php tools/test-security-regressions.php` harus menghasilkan `Security regression checks: OK`.
- SQL/inspection memastikan jadwal baru tidak lagi memerlukan field SEB per jadwal.
- Browser biasa ditolak pada login siswa dan halaman ujian.
- File `.seb` global dengan Start URL yang sama diterima pada Online dan LAN.
- Jadwal lama dengan kunci per jadwal tetap dapat dibuka selama fallback kompatibilitas aktif.
- Konfigurasi global tanpa kunci valid ditolak dengan pesan aman, tanpa membuka ujian melalui browser biasa.

## STOP conditions

- Berhenti sebelum mengubah kode bila satu Start URL tidak identik pada dua jalur.
- Berhenti bila sumber kunci global belum ditentukan atau akan tersimpan di repository.
- Berhenti bila perubahan memerlukan penghapusan kolom atau perubahan tabel ujian.

## Plan Review

| Dimensi | Skor | Catatan |
|---|---:|---|
| Completeness | 5/5 | Config, service, hook, UI, dokumentasi, fallback, dan verifikasi tercakup. |
| Feasibility | 5/5 | Mengikuti titik konfigurasi dan validasi SEB yang sudah ada. |
| Scope | 5/5 | Tidak menyentuh alur soal, jawaban, attempt, atau schema. |
| Testability | 5/5 | Lint, regression, browser biasa, SEB, dan dua jalur akses ditentukan. |
| Risk | 5/5 | Fail-closed, rahasia di luar source control, dan fallback lama dibatasi. |
| Assumptions | 5/5 | Satu Start URL dan sumber kunci global dinyatakan eksplisit. |

Review date: 2026-10-31
