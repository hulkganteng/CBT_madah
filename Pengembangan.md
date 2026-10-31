# Identifikasi dan pengembangan GarudaCBT

Dokumen ini menjelaskan kondisi aktual proyek GarudaCBT, bagian yang sudah diterapkan, risiko yang masih terbuka, dan arah pengembangan berikutnya. Pengelola sekolah, proktor, dan pengembang dapat menggunakannya sebagai dasar untuk menentukan apakah aplikasi siap memasuki pengujian atau produksi.

Dokumen ini bertipe **penjelasan teknis**. Petunjuk operasional terperinci tetap tersedia dalam [panduan aplikasi](docs/panduan/index.md), sedangkan kebutuhan produk lengkap tersedia dalam [PRD CBT murni](prd%20%281%29.md).

## Ringkasan kondisi

Proyek ini merupakan modifikasi GarudaCBT 1.5.2 menjadi aplikasi CBT murni berbasis CodeIgniter 3.1.13. Implementasi inti ujian dan pengamanan P0 sudah tersedia, tetapi aplikasi belum dapat dinyatakan siap produksi sampai tim menyelesaikan pengujian basis data, UAT, simulasi 25 peserta, dan pemindahan arsip sensitif ke penyimpanan nonpublik.

Status proyek saat dokumen ini dibuat adalah **implementasi teknis, belum terverifikasi secara menyeluruh**. Pemeriksaan sintaks berhasil untuk komponen utama, tetapi layanan web lokal tidak aktif saat pemeriksaan sehingga alur ujian dan koneksi basis data belum dapat diuji dari awal sampai akhir.

## Identitas proyek

Tabel berikut merangkum identitas dan sasaran utama aplikasi berdasarkan kode dan dokumen proyek.

| Aspek | Kondisi aktual |
| --- | --- |
| Nama produk | GarudaCBT — CBT murni |
| Versi dasar | GarudaCBT 1.5.2 |
| Lisensi | MIT |
| Framework | CodeIgniter 3.1.13 |
| Runtime lokal | PHP 8.2.12 dan MySQL/MariaDB melalui XAMPP |
| Antarmuka | AdminLTE, Bootstrap, dan jQuery |
| Peran | Administrator, guru, proktor/pengawas, dan siswa |
| Kapasitas laboratorium | 1 server dan maksimal 25 PC peserta per sesi |
| Jalur akses | Online melalui Cloudflare Tunnel dan lokal melalui LAN |
| Model data | Satu server dan satu basis data untuk kedua jalur akses |
| Fokus produk | Bank soal, jadwal, token, pelaksanaan, pemantauan, penilaian, analisis, dan rekap ujian |
| Di luar lingkup | Rapor, buku induk berbasis rapor, materi, tugas, dan nilai harian non-CBT |

## Aturan produk

Aplikasi menerapkan aturan sekolah pada sisi server agar operator tidak dapat melewatinya dengan mengubah formulir atau permintaan HTTP.

| Aturan | Nilai baku | Implementasi |
| --- | ---: | --- |
| Soal Eksata | 25 soal | Konfigurasi dan validasi server-side |
| Soal Non-eksata | 30 soal | Konfigurasi dan validasi server-side |
| Durasi ujian | 60 menit | Diisi otomatis saat jadwal disimpan |
| Kapasitas sesi | 25 peserta | Diperiksa saat peserta atau kelas dialokasikan |
| Tipe soal | PG, PG Kompleks, Benar/Salah | Tipe lain dinolkan dan tidak dipakai dalam ujian baru |
| Jalur ujian | Online dan LAN | `base_url` mengikuti host yang digunakan |
| Browser ujian | Safe Exam Browser opsional atau wajib | Config Key dan Browser Exam Key divalidasi |

Nilai baku berada dalam [konfigurasi ujian](application/config/exam_baku.php). Penegakannya berada dalam [hook keamanan ujian](application/hooks/Exam_security_hook.php), sehingga validasi tetap berjalan meskipun pengguna mengirim permintaan langsung tanpa antarmuka aplikasi.

## Arsitektur aplikasi

Semua peserta dan pengelola menggunakan aplikasi serta basis data yang sama. Perbedaan antara jalur Online dan LAN hanya terletak pada alamat akses.

```text
Pengguna dari internet
        │
        ▼
Cloudflare Tunnel ───────────────┐
                                 │
                                 ▼
                         Server GarudaCBT
                                 │
                                 ▼
                           MySQL/MariaDB
                                 ▲
                                 │
PC peserta melalui LAN ──────────┘
```

Model ini menjaga token, jawaban, status, dan sisa waktu tetap sama ketika peserta berpindah dari jalur Online ke LAN. Peserta perlu login kembali, tetapi sistem melanjutkan percobaan ujian yang masih aktif.

## Struktur implementasi

Komponen utama dipisahkan berdasarkan tanggung jawab, walaupun sebagian controller dan model lama masih berukuran besar.

| Lokasi | Tanggung jawab |
| --- | --- |
| `application/controllers` | Alur HTTP untuk admin, guru, proktor, dan siswa |
| `application/models` | Akses data master, ujian, pengguna, dan pengaturan |
| `application/views` | Tampilan dashboard, bank soal, monitoring, dan ujian siswa |
| `application/hooks/Exam_security_hook.php` | Otorisasi tambahan, aturan baku, pembatasan modul, dan penjagaan alur ujian |
| `application/libraries/Exam_attempt_service.php` | Percobaan ujian, sesi, heartbeat, perangkat, SEB, dan pencatatan aktivitas |
| `application/migrations` | Perubahan skema untuk keamanan, monitoring, Benar/Salah, dan SEB |
| `application/config/exam_baku.php` | Jumlah soal, durasi, dan kapasitas baku |
| `installer/client/Buat-Shortcut-CBT.ps1` | Pembuatan shortcut Online dan LAN pada PC peserta |
| `tools/buat-seb.php` | Pembuatan konfigurasi Safe Exam Browser |
| `docs/panduan` | Panduan penggunaan dan operasional aplikasi |

## Status implementasi

Status berikut membedakan keberadaan kode dari hasil pengujian. “Diterapkan” berarti implementasi ditemukan dalam kode, sedangkan “Belum terverifikasi” berarti alur tersebut masih memerlukan pengujian pada aplikasi dan basis data aktif.

| Area | Status | Bukti atau catatan |
| --- | --- | --- |
| Data master siswa, guru, kelas, dan mapel | Diterapkan | Controller dan model data master tersedia |
| Kelompok mapel Eksata/Non-eksata | Diterapkan | Migrasi menambah `kelompok_ujian` pada `master_mapel` |
| Jumlah soal 25/30 dan durasi 60 menit | Diterapkan | Hook menimpa nilai formulir dan memvalidasi bank soal |
| Tiga tipe soal baku | Diterapkan | PG, PG Kompleks, dan Benar/Salah dihitung; tipe lain dinonaktifkan |
| Kapasitas 25 peserta | Diterapkan | Alokasi peserta diperiksa pada sisi server |
| Modul rapor dan e-learning | Diterapkan | Menu disembunyikan dan URL langsung menghasilkan HTTP 403 |
| Kepemilikan bank soal guru | Diterapkan | Akses baca, ubah, dan hapus diperiksa terhadap pemilik bank |
| Akses jadwal sesuai peran | Diterapkan | Guru dan pengawas dibatasi pada jadwal yang menjadi kewenangannya |
| Percobaan dan kelanjutan ujian | Diterapkan | Tabel percobaan, nonce sesi, deadline, perangkat, dan heartbeat tersedia |
| Autosave dan auto-submit | Diterapkan | Jawaban disimpan per soal dan ujian dikumpulkan saat waktu habis |
| Penanda ragu-ragu | Diterapkan | Kolom dan endpoint perubahan status tersedia |
| Monitoring dan pelanggaran tab | Diterapkan | Event ujian serta tindakan proktor tersedia |
| Perpanjangan waktu peserta | Diterapkan | Menyimpan tambahan menit dan alasan |
| Safe Exam Browser | Diterapkan | Mendukung mode `OFF`, `OPTIONAL`, dan `REQUIRED` |
| Akses Online dan LAN | Diterapkan sebagian | URL dinamis tersedia, tetapi failover belum diuji pada layanan aktif |
| Backup dan restore | Diterapkan sebagian | Fitur dan berkas backup ada, tetapi lokasi penyimpanan belum aman |
| Penilaian dan rekap Benar/Salah | Diterapkan | Migrasi dan perubahan tampilan/rekap tersedia |
| Pengujian otomatis aplikasi | Belum tersedia | Test yang ditemukan hanya milik dependensi dalam `vendor` |
| UAT lengkap | Belum terverifikasi | Belum ada bukti hasil uji peran dan alur ujian end-to-end |
| Uji beban 25 peserta | Belum terverifikasi | Belum ada hasil pengukuran server dan jaringan |
| Uji failover Online ke LAN | Belum terverifikasi | Harus diuji dengan sesi, jawaban, dan timer aktif |

## Keamanan

Kontrol P0 berikut sudah diterapkan. Pemindahan fisik backup dan arsip sumber tetap dianjurkan agar perlindungan tidak hanya bergantung pada konfigurasi Apache.

| Prioritas | Temuan | Dampak | Tindakan yang diperlukan |
| --- | --- | --- | --- |
| Dim mitigasi | Backup SQL ZIP masih berada dalam `backups/` di document root | Pengunjung dapat mencoba mengunduh data sekolah dan kredensial yang tersimpan dalam backup | Akses HTTP dan directory listing sudah ditolak dengan status 403; pindahkan berkas ke luar document root saat lokasi penyimpanan disepakati |
| Dim mitigasi | `garudacbt.zip` masih berada dalam document root | Pengunjung dapat mengunduh seluruh sumber aplikasi dan kemungkinan konfigurasi sensitif | Akses HTTP sudah ditolak dengan status 403 dan arsip diabaikan Git; pindahkan atau hapus setelah salinan aman tersedia |
| Selesai | Kunci enkripsi sebelumnya memiliki nilai cadangan dalam kode | Semua instalasi dapat memakai kunci yang sama | Nilai bawaan dihapus; produksi memakai `GARUDACBT_ENCRYPTION_KEY` atau berkas kunci unik yang diprovisikan |
| Selesai | Migrasi otomatis sebelumnya selalu aktif | Permintaan aplikasi dapat memicu perubahan skema saat deployment belum siap | Produksi sekarang menonaktifkan migrasi otomatis kecuali diizinkan melalui environment variable |

CSRF dan hook keamanan sudah aktif. Hook juga melindungi endpoint instalasi, pembaruan, migrasi, penghapusan massal, kepemilikan bank soal, identitas siswa, jadwal, sesi, dan percobaan ujian.

## Kompatibilitas runtime

Runtime dan dependensi Composer sekarang kompatibel dengan PHP 8.2.12. `composer check-platform-reqs` lulus untuk PHP dan seluruh ekstensi yang dibutuhkan.

Pembaruan dependensi menaikkan PHPWord ke 1.4.0, PhpSpreadsheet ke 5.10.0, Dompdf ke 3.1.6, dan PHPUnit ke 11.5.57. Laminas Escaper lama yang membatasi runtime sudah tidak menjadi dependensi proyek.

Pemeriksaan berikut sudah lulus setelah pembaruan:

| Pemeriksaan | Hasil |
| --- | --- |
| Validasi `composer.json` | Lulus |
| Persyaratan platform PHP 8.2 | Lulus |
| Audit advisory Composer | Tidak menemukan advisory setelah pembaruan |
| Pembuatan spreadsheet XLSX sederhana | Lulus |
| Pembuatan dokumen Word sederhana | Lulus |
| Render PDF sederhana | Lulus |

Smoke test tidak menggantikan UAT. Tim masih harus menguji impor dan ekspor nyata dari setiap menu karena pembaruan pustaka melintasi beberapa versi mayor.

## Kualitas dan kemudahan pemeliharaan

Kode modifikasi baru cukup terpusat pada hook, layanan percobaan, controller monitoring, konfigurasi, dan migrasi. Namun sebagian besar kode warisan masih terobfusikasi dan beberapa controller atau model melebihi puluhan ribu karakter, sehingga perubahan langsung pada kode lama memiliki risiko regresi tinggi.

Folder proyek juga tidak memiliki metadata Git. Tanpa riwayat versi, tim sulit meninjau perubahan, membuat rollback, membandingkan rilis, atau memastikan berkas produksi sama dengan berkas yang diuji.

Pengembangan berikutnya perlu menambahkan repositori Git privat, aturan `.gitignore` untuk konfigurasi dan backup, test suite aplikasi, serta proses rilis yang menghasilkan artefak tanpa data produksi.

## Prioritas pengembangan

Urutan berikut menempatkan keamanan dan kepastian data sebelum penambahan fitur baru.

| Fase | Sasaran | Hasil yang diharapkan |
| --- | --- | --- |
| P0 — Pengamanan | Lindungi backup dan kode sumber, atur kunci unik, kendalikan migrasi, dan selesaikan kompatibilitas PHP | Aplikasi aman untuk mulai diuji melalui jaringan |
| P1 — Verifikasi fungsi | Aktifkan Apache/MySQL, jalankan migrasi, dan uji seluruh peran serta alur ujian | Semua kebutuhan wajib PRD memiliki bukti lulus |
| P2 — Simulasi laboratorium | Uji 25 peserta, SEB, gangguan client, perpindahan Online/LAN, dan pemulihan jawaban | Pelaksanaan ujian tetap berjalan pada kondisi normal dan gangguan |
| P3 — Otomasi kualitas | Tambahkan integration test, smoke test, pemeriksaan tautan, dan pemeriksaan keamanan dasar | Perubahan berikutnya dapat diverifikasi berulang kali |
| P4 — Pemeliharaan | Kurangi ketergantungan pada kode terobfusikasi dan pisahkan controller/model besar secara bertahap | Risiko regresi dan waktu diagnosis menurun |

Fitur baru tidak menjadi prioritas sampai fase P0 sampai P2 selesai. Penambahan fitur sebelum fondasi tersebut teruji akan memperluas permukaan kegagalan pada hari ujian.

## Kriteria kesiapan produksi

Aplikasi siap dipertimbangkan untuk produksi setelah semua kriteria berikut memiliki bukti yang dapat diperiksa.

| Kriteria | Bukti minimum |
| --- | --- |
| Persetujuan kebutuhan | PRD ditandatangani kepala sekolah/waka kurikulum, guru, dan proktor/teknisi |
| Keamanan berkas | Backup dan arsip sumber tidak dapat diakses melalui HTTP |
| Rahasia aplikasi | Kunci enkripsi unik berasal dari environment variable atau berkas lokal yang dilindungi dan diabaikan Git |
| Skema basis data | Lima migrasi berhasil dan versi migrasi tercatat sesuai konfigurasi |
| Kompatibilitas | `composer check-platform-reqs` lulus pada PHP produksi |
| Uji peran | Admin, guru, proktor, dan siswa hanya dapat mengakses kewenangannya |
| Uji ujian | Login, token, konfirmasi, autosave, ragu-ragu, selesai, dan auto-submit lulus |
| Uji penilaian | PG, PG Kompleks, dan Benar/Salah menghasilkan nilai dan rekap yang benar |
| Uji SEB | Mode wajib menolak browser biasa dan menerima konfigurasi yang sah |
| Uji kapasitas | 25 peserta aktif tanpa kegagalan simpan, timeout, atau kehilangan jawaban |
| Uji pemulihan | Peserta dapat melanjutkan pada PC lain dengan sisa waktu yang benar |
| Uji failover | Peserta berpindah dari Online ke LAN tanpa kehilangan jawaban atau membuat percobaan baru |
| Uji backup | Backup dapat dipulihkan pada basis data uji dan menghasilkan data yang sama |
| Observabilitas | Proktor dapat melihat status, heartbeat, pelanggaran, dan tindakan per peserta |

Keputusan produksi harus berdasarkan hasil pengujian tersebut, bukan hanya keberadaan menu atau keberhasilan membuka halaman login.

## Sumber acuan

Dokumen berikut menjadi sumber utama untuk memahami produk dan menjalankan pengujian lanjutan.

- [README proyek](README.md) menjelaskan versi, fitur, lisensi, dan ruang lingkup CBT murni.
- [PRD CBT murni](prd%20%281%29.md) menjelaskan kebutuhan, penerimaan, risiko, dan keputusan sekolah.
- [Panduan aplikasi](docs/panduan/index.md) mengarahkan pembaca ke panduan setiap peran dan operasi.
- [Panduan keamanan ujian](docs/panduan/keamanan-ujian.md) menjelaskan kontrol selama pelaksanaan.
- [Panduan laboratorium hybrid](docs/panduan/lab-hybrid.md) menjelaskan penggunaan jalur Online dan LAN.
- [Panduan Safe Exam Browser](docs/panduan/safe-exam-browser.md) menjelaskan konfigurasi browser terkunci.
- [Panduan pemeliharaan](docs/panduan/pemeliharaan.md) menjelaskan backup, pembaruan, dan perawatan aplikasi.

## Langkah lanjutan

Setelah dokumen ini disetujui, tim perlu menyelesaikan fase P0 terlebih dahulu, lalu mencatat hasil UAT dan simulasi laboratorium sebagai bukti kesiapan produksi. Setiap perubahan aturan sekolah juga harus memperbarui PRD, konfigurasi ujian, test case, dan dokumen ini dalam perubahan yang sama.
