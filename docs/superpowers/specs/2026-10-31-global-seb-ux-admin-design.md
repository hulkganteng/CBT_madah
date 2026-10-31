# Desain SEB Global dan UX Admin Jadwal

## Tujuan

GarudaCBT memakai satu konfigurasi Safe Exam Browser global dengan satu Start URL. Peserta selalu masuk melalui file `.seb` yang sama; operator tidak mengisi Config Key pada setiap jadwal. Keamanan ujian tetap ditegakkan server-side.

## Batasan

- Algoritma randomisasi, penyimpanan jawaban, session ujian, timer backend, penilaian, dan skema soal tidak berubah.
- Kolom SEB lama pada `cbt_jadwal` dipertahankan untuk kompatibilitas data lama.
- Browser biasa tetap ditolak pada portal siswa dan halaman ujian.
- Satu Start URL harus digunakan untuk akses Online dan LAN.

## Pendekatan yang dipilih

Konfigurasi SEB dipindahkan menjadi kebijakan global aplikasi. Form jadwal tidak lagi menampilkan `seb_mode`, `seb_config_key`, atau `seb_browser_exam_keys`. Hook dan `Exam_attempt_service` memvalidasi identitas SEB terhadap konfigurasi global, sementara data jadwal lama hanya menjadi fallback kompatibilitas dan tidak menjadi input operator baru.

## Perubahan yang direncanakan

1. Tambahkan sumber konfigurasi global untuk mode wajib, Config Key/Browser Exam Key, dan Start URL.
2. Ubah validasi SEB agar jadwal otomatis memakai kebijakan global.
3. Pertahankan penolakan browser biasa dan verifikasi saat resume/heartbeat.
4. Sederhanakan form jadwal dengan kartu informasi “SEB wajib — gunakan file `.seb` resmi”.
5. Audit label dan urutan UX admin agar alur pembuatan jadwal berfokus pada bank soal, jadwal, kelas/peserta, sesi, dan publikasi.
6. Tambahkan pemeriksaan konfigurasi global yang gagal aman jika kunci atau Start URL belum diisi.

## Tidak dikerjakan

- Tidak membuat tabel attempt baru.
- Tidak mengubah API ujian, randomisasi, jawaban, timer, atau penilaian.
- Tidak mengubah branding siswa atau menambahkan fitur dashboard baru.

## Verifikasi

- Browser biasa ditolak.
- File `.seb` global dengan Start URL yang sama diterima.
- Jadwal baru tanpa input Config Key dapat dibuat.
- Jadwal lama tetap dapat dibuka atau ditolak dengan pesan migrasi yang jelas bila konfigurasi global belum lengkap.
- Regression security dan lint PHP dijalankan.
