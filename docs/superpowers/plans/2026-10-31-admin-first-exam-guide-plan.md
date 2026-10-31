# Implementasi Pendamping Ujian Pertama Admin

## Context

Admin pemula harus menebak urutan kerja dari sidebar yang tersebar. Dashboard sudah menerima ringkasan data master dan CBT sehingga dapat menampilkan panduan interaktif tanpa endpoint baru.

## Approach

Tambahkan panel panduan pada `application/views/dashboard.php`, hitung status dasar dari data yang sudah tersedia, simpan checklist manual hanya di `localStorage`, dan tambahkan gaya responsif di `assets/app/css/modern-ui.css`. Semua CTA memakai URL menu existing.

## Key decisions

- Tidak membuat wizard backend, tabel, endpoint, atau migrasi.
- Status yang tidak dapat dipastikan dari data dashboard diberi label **Periksa**, bukan dianggap selesai.
- Panel tampil default dan dapat diciutkan atau disembunyikan sementara pada halaman aktif.
- Menu lama tetap dipertahankan sebagai jalur alternatif.

## Files to modify

- `application/views/dashboard.php`: markup kartu langkah, status otomatis, dan checklist manual.
- `assets/app/css/modern-ui.css`: layout, status, focus, dan responsive state.
- `assets/app/js/admin-first-exam-guide.js`: hitung progress, localStorage, dan interaksi panel.

## Out of scope

Controller, model, endpoint, database, randomisasi, penyimpanan jawaban, session ujian, timer, penilaian, dan SEB tidak diubah.

## Verification

- `php -l application/views/dashboard.php`
- `php tools/test-security-regressions.php`
- Pastikan semua CTA mempertahankan URL existing.
- Uji dashboard kosong, sebagian, lengkap, keyboard, 1366x768, dan 1920x1080.

## STOP conditions

Berhenti dan laporkan bila status dashboard tidak menyediakan data yang diperlukan atau perubahan membutuhkan controller/endpoint baru.

## Implementation notes

See `2026-10-31-admin-first-exam-guide-implementation-notes.md`.
