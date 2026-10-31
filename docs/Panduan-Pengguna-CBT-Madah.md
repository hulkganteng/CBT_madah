# Panduan Pengguna CBT Madah

**Berbasis GarudaCBT 1.5.2**

Panduan ini ditujukan untuk administrator, guru, proktor atau pengawas, teknisi server, dan siswa. Gunakan bab sesuai peran dan ikuti urutan kerja sebelum membuka ujian resmi.

**Versi dokumen:** 1.0  
**Tanggal:** 31 Oktober 2026

## Daftar isi

- Instalasi dan persiapan server
- Administrasi sekolah dan data master
- Pengelolaan bank soal dan jadwal
- Operasional ujian
- Panduan siswa
- Safe Exam Browser
- Laboratorium Online dan LAN
- Backup, pembaruan, dan pemulihan
- Pemecahan masalah
- Keamanan dan batas perlindungan

---

# Memulai dan memasang GarudaCBT

Tutorial ini memandu administrator server memasang GarudaCBT pada XAMPP atau lingkungan Apache, PHP, dan MySQL/MariaDB yang setara. Setelah selesai, Anda dapat masuk sebagai administrator dan melanjutkan pengaturan sekolah.

## Prasyarat

Siapkan server dan akses berikut sebelum membuka installer.

- Apache dengan dukungan `.htaccess` dan `mod_rewrite`.
- PHP 7.0 atau lebih baru dengan ekstensi `mysqli`, `dom`, `fileinfo`, `zip`, `json`, `gd`, `iconv`, dan `calendar`.
- MySQL atau MariaDB serta akun yang boleh membuat dan mengubah database.
- Ruang tulis untuk folder `application/cache`, `application/logs`, `backups`, dan `uploads`.
- HTTPS untuk server produksi, terutama jika menggunakan Safe Exam Browser.

## Mulai cepat

Ikuti langkah singkat ini untuk memasang aplikasi melalui installer web.

1. Salin folder aplikasi ke direktori web, misalnya `C:\xampp\htdocs\garudacbt`.
2. Nyalakan Apache dan MySQL dari XAMPP Control Panel.
3. Buka `http://localhost/garudacbt` pada browser.
4. Isi host database, pengguna, kata sandi, dan nama database, lalu klik **INSTALL / UPDATE**.
5. Lengkapi akun administrator dan profil sekolah sampai installer menampilkan tahap selesai.

Setelah instalasi selesai, buka halaman masuk dan gunakan akun administrator yang Anda buat.

## Pasang secara manual

Gunakan cara manual jika installer web tidak dapat membuat database atau menulis konfigurasi.

1. Buat database kosong dengan encoding UTF-8 melalui phpMyAdmin atau klien MySQL.
2. Impor `assets/app/db/master.sql` ke database tersebut.
3. Buka `application/config/database.php`.
4. Isi `hostname`, `username`, `password`, dan `database` sesuai server.
5. Buka kembali alamat aplikasi untuk menyelesaikan data administrator dan sekolah.
6. Jalankan migrasi keamanan dari folder aplikasi:

   ```powershell
   C:\xampp\php\php.exe index.php migrate CreateMigration
   ```

Perintah berhasil jika menampilkan pesan bahwa berkas migrasi telah dijalankan.

## Atur keamanan produksi

Sebelum siswa menggunakan aplikasi melalui jaringan, gunakan konfigurasi produksi berikut.

1. Pasang sertifikat TLS dan arahkan pengguna ke alamat `https://`.
2. Atur environment variable `GARUDACBT_ENCRYPTION_KEY` dengan nilai acak yang panjang pada konfigurasi web server.
3. Batasi akses publik ke folder `application`, `system`, `backups`, dan berkas konfigurasi.
4. Nonaktifkan tampilan error PHP pada produksi dan aktifkan pencatatan error ke berkas.
5. Buat backup database sebelum memasukkan data sekolah.

Jangan mengganti encryption key setelah sesi pengguna aktif karena perubahan tersebut dapat membatalkan sesi.

## Verifikasi instalasi

Periksa komponen berikut sebelum melanjutkan ke data master.

1. Masuk sebagai administrator.
2. Buka **Pengaturan > Profile Sekolah** dan pastikan data sekolah tampil.
3. Buka **Data Umum > Tahun Pelajaran** dan pastikan satu tahun serta semester berstatus aktif.
4. Buka **Pengaturan > Database > Backup** dan pastikan halaman dapat dimuat.
5. Jika memakai SEB, pastikan migrasi `20260812000100` telah membuat tabel `cbt_exam_attempt` dan `cbt_exam_event`.

## Langkah berikutnya

Lanjutkan pengaturan aplikasi melalui panduan berikut.

- [Panduan administrator](administrator.md) untuk mengisi data master dan akun pengguna.
- [Operasional ujian](operasional-ujian.md) untuk menyiapkan pelaksanaan CBT pertama.
- [Safe Exam Browser](safe-exam-browser.md) untuk membatasi lingkungan perangkat ujian.
- [Pemeliharaan](pemeliharaan.md) untuk membuat backup dan memperbarui aplikasi.


---

# Panduan administrator

Panduan ini membantu administrator menyiapkan data sekolah, pengguna, kelas, dan CBT. Kerjakan data yang saling bergantung sesuai urutan pada halaman ini. Modul Rapor, Buku Induk, dan e-Learning tidak termasuk cakupan aplikasi ini; menu dan rutenya sudah dihapus dan diblok server-side.

## Siapkan profil dan periode aktif

Periode aktif menentukan data kelas, guru, dan siswa yang digunakan oleh aplikasi.

1. Buka **Pengaturan > Profile Sekolah**.
2. Lengkapi identitas sekolah, kepala sekolah, alamat, jenjang, dan satuan pendidikan.
3. Klik **Simpan**.
4. Buka **Data Umum > Tahun Pelajaran**.
5. Tambahkan tahun pelajaran dan semester jika belum tersedia.
6. Aktifkan tepat satu tahun pelajaran dan satu semester yang sedang digunakan.

Jika periode aktif salah, siswa dapat terlihat tanpa kelas atau tidak mendapatkan jadwal ujian.

## Siapkan data master

Tambahkan data master dari entitas paling umum ke entitas yang bergantung padanya.

1. Buka **Data Umum > Mata Pelajaran** dan tambahkan mata pelajaran.
2. Pada setiap mata pelajaran, tetapkan **Kelompok Ujian**: `EKSATA` (25 soal) atau `NON_EKSATA` (30 soal). Nilai ini dipakai otomatis saat membuat bank soal dan jadwal.
3. Jika sekolah menggunakan peminatan, buka **Data Umum > Jurusan** dan tambahkan jurusan.
4. Buka **Data Umum > Guru** dan masukkan data guru beserta mata pelajarannya.
5. Buka **Data Umum > Siswa** dan tambahkan siswa satu per satu atau gunakan impor yang tersedia.
6. Buka **Data Umum > Kelas / Rombel** dan buat rombongan belajar.
7. Buka detail setiap kelas, lalu tempatkan siswa dan wali kelas.
8. Jika diperlukan, buka **Data Umum > Ekstrakurikuler** dan tambahkan kegiatan.

Gunakan NIS, NISN, dan username yang unik agar akun tidak tertukar.

## Kelola akun pengguna

Data guru atau siswa dan akun login merupakan dua hal yang terkait tetapi memiliki fungsi berbeda.

1. Buka **Pengaturan > User Management**.
2. Pilih **Administrator**, **Guru**, atau **Siswa**.
3. Buat atau sinkronkan akun untuk data yang sudah tersedia.
4. Pastikan setiap akun berada pada grup yang tepat.
5. Uji satu akun guru dan satu akun siswa sebelum membagikan kredensial.

Jangan menggunakan satu akun siswa untuk beberapa peserta. Lapisan keamanan ujian mengikat percobaan ujian pada identitas akun yang masuk.

## Siapkan struktur CBT

Sesi dan ruang harus tersedia sebelum administrator menempatkan peserta ujian.

1. Buka **Data Ujian > Jenis Ujian** dan buat jenis seperti PTS, PAS, atau Try Out.
2. Buka **Data Ujian > Sesi** dan atur waktu mulai serta selesai.
3. Buka **Data Ujian > Ruang** dan tambahkan ruang ujian.
4. Buka **Data Ujian > Atur Ruang/Sesi** dan tempatkan setiap siswa.
5. Buka **Data Ujian > Nomor Peserta** dan buat nomor peserta.
6. Buka **Data Ujian > Pengawas** dan tentukan guru pengawas untuk jadwal, ruang, dan sesi.

Periksa kembali penempatan jika siswa tidak melihat ujian yang seharusnya tersedia. Satu sesi menampung maksimal 25 peserta; server menolak penempatan ke-26.

## Pantau aktivitas siswa

1. Buka **Data Ujian > Aktivitas Siswa** untuk melihat status peserta per jadwal secara real-time.
2. Periksa kolom heartbeat, pelanggaran pindah tab, dan waktu tersisa.
3. Gunakan **Keluarkan**, **Izinkan Masuk Kembali**, atau **Perpanjang Waktu** sesuai prosedur proktor.

## Kelola backup database

1. Buka **Pengaturan > Pemulihan Database**.
2. Klik **Backup Sekarang** sebelum dan sesudah hari ujian. Berkas tersimpan pada folder `backups`.
3. Untuk memulihkan, pilih berkas dan konfirmasi. Restore menimpa database aktif.

Lakukan backup penuh folder aplikasi dan database sebelum setiap perubahan besar.

## Tinjau hak akses

Gunakan pembagian peran berikut sebagai batas operasional minimum.

| Peran | Tanggung jawab utama | Jangan diberikan |
|---|---|---|
| Administrator | Konfigurasi, data master, pengguna, database | Kredensial kepada siswa |
| Guru | Bank soal, jadwal, nilai sesuai tugas | Akses database server |
| Proktor/pengawas | Token, status peserta, izin, perpanjangan waktu, pemantauan ujian | Perubahan kunci jawaban |
| Siswa | Ujian miliknya | Akun bersama |

## Langkah berikutnya

Gunakan panduan berikut setelah data dasar siap.

- [Panduan guru](guru.md) untuk membuat bank soal dan memeriksa hasil.
- [Operasional ujian](operasional-ujian.md) untuk menjalankan ujian dari persiapan hingga rekap.
- [Safe Exam Browser](safe-exam-browser.md) untuk mewajibkan lingkungan ujian terkunci.
- [Pemeliharaan](pemeliharaan.md) untuk backup dan pembaruan rutin.


---

# Panduan guru

Panduan ini membantu guru membuat bank soal, menjadwalkan ujian, dan memantau peserta pada Garuda CBT murni. Modul Rapor, Buku Induk, dan e-Learning tidak termasuk dalam cakupan aplikasi ini.

## Buat bank soal

Pastikan mata pelajaran dan kelas tujuan sudah ditetapkan oleh administrator.

1. Buka **Ulangan / Ujian > Bank Soal**.
2. Klik tombol tambah bank soal.
3. Pilih mata pelajaran, kelas, tahun pelajaran, semester, dan **jumlah soal** per tipe. Jumlah soal PG + PG Kompleks + Benar/Salah wajib sama dengan ketentuan kelompok mapel: Eksata 25 soal, Non-eksata 30 soal.
4. Atur pengacakan soal atau opsi jawaban sesuai kebutuhan.
5. Simpan bank soal.
6. Buka detail bank, lalu tambah soal secara manual atau gunakan menu impor.
7. Periksa teks, stimulus, gambar, opsi, kunci jawaban, bobot, dan jenis setiap soal.

Tipe soal baku hanya tiga: **Pilihan Ganda**, **Pilihan Ganda Kompleks**, dan **Benar / Salah**. Tipe lain (menjodohkan, isian singkat, uraian) tidak dipakai dan disembunyikan pada form. Stimulus berupa teks bacaan, gambar, atau tabel diisi pada kolom **Stimulus** dan tampil di atas soal peserta. Lakukan pratinjau setelah mengimpor soal dengan gambar atau rumus.

Pada template Excel, kolom **JENIS** hanya menerima `1` (Pilihan Ganda), `2` (Pilihan Ganda Kompleks), dan `6` (Benar / Salah). Untuk Benar / Salah, kolom OPSI dibiarkan kosong dan **KUNCI** diisi `B` atau `S`. Baris dengan jenis lain ditolak saat pratinjau, bukan diimpor diam-diam.

## Impor soal sendiri

Guru dapat mengimpor soal sendiri tanpa bantuan administrator:

1. Buka **Bank Soal**, lalu klik **Import Soal** pada kartu bank yang Anda buat.
2. Unduh **Template Excel**, isi satu baris per soal, lalu unggah.
3. Klik **Preview** untuk memeriksa hasil baca. Baris yang salah jenis atau kurang kolom akan dilaporkan di sini.
4. Pilih mode **Tambah ke soal yang ada** atau **Ganti seluruh soal**, lalu klik **Import Soal**.

Berkas dibatasi 5 MB dan 2.000 soal. Cara lama lewat dokumen Word (`Template Soal`) dan panel **Upload Soal** di halaman yang sama juga tetap tersedia.

Bank soal adalah milik guru pembuatnya. Akun guru hanya dapat membuka, mengubah, menghapus, dan mengimpor soal ke bank miliknya sendiri; permintaan ke `id_bank` milik guru lain ditolak server dengan `403`. Administrator tetap dapat mengelola semua bank dari menu administrator.

## Buat jadwal ujian

Buat jadwal setelah jumlah soal pada bank memenuhi jumlah soal yang akan ditampilkan.

1. Buka **Ulangan / Ujian > Jadwal**.
2. Klik tambah jadwal dan pilih bank serta jenis ujian.
3. Isi tanggal mulai dan tanggal berakhir. Jumlah soal (25 / 30) dan durasi 60 menit terisi otomatis dari kelompok mapel dan tidak dapat diubah dari form.
4. Aktifkan token, pengacakan, tampilan hasil, atau reset izin sesuai kebijakan sekolah.
5. Pilih kebijakan Safe Exam Browser.
6. Aktifkan status jadwal, lalu klik **Simpan**.

Jika memilih SEB **Wajib**, isi Config Key atau Browser Exam Key sebelum menyimpan.

## Pantau peserta

Gunakan halaman pemantauan selama ujian berlangsung.

1. Buka **Ulangan / Ujian > Aktivitas Siswa**.
2. Pilih jadwal yang sedang berlangsung.
3. Periksa siswa yang belum masuk, sedang mengerjakan, terputus, atau selesai.
4. Gunakan tindakan **Keluarkan**, **Izinkan Masuk Kembali**, atau **Perpanjang Waktu** hanya setelah memverifikasi identitas siswa dan perangkatnya. Perpanjangan waktu wajib menyertakan alasan.
5. Catat setiap intervensi proktor dalam berita acara.

Perpindahan tab, keluar fullscreen, kondisi offline, dan heartbeat disimpan sebagai peristiwa per peserta. Data tersebut merupakan indikator untuk pemeriksaan, bukan bukti tunggal kecurangan.

## Periksa hasil ujian

Ketiga tipe soal dinilai otomatis.

1. Buka **Ulangan / Ujian > Hasil Ujian**.
2. Pilih jadwal dan siswa.
3. Periksa nilai PG, PG Kompleks, dan Benar/Salah per siswa.
4. Buka **Analisis Soal** untuk melihat tingkat kesukaran dan daya serap per butir.
5. Buka **Rekap Nilai** untuk mengekspor hasil ke spreadsheet.

Jangan mengubah bank soal atau kunci saat jadwal sedang dipakai peserta.

## Langkah berikutnya

Gunakan dokumen berikut untuk pelaksanaan dan penanganan masalah.

- [Operasional ujian](operasional-ujian.md) untuk daftar periksa proktor.
- [Safe Exam Browser](safe-exam-browser.md) untuk konfigurasi ujian terkunci.
- [Pemecahan masalah](troubleshooting.md) untuk masalah soal, timer, login, atau hasil.


---

# Menjalankan ujian

Panduan ini memberikan prosedur kerja administrator, guru, proktor, dan pengawas dari persiapan sampai rekap. Gunakan daftar periksa untuk setiap jadwal agar perubahan dapat dilacak.

## Siapkan data ujian

Selesaikan data yang menjadi prasyarat sebelum membuat jadwal.

1. Aktifkan tahun pelajaran dan semester yang benar.
2. Periksa kelas, siswa, mata pelajaran, guru, sesi, dan ruang.
3. Tempatkan siswa pada ruang dan sesi.
4. Buat nomor peserta.
5. Tetapkan pengawas.
6. Buat jenis ujian.

## Siapkan bank dan jadwal

Gunakan bank yang sudah ditinjau oleh guru mata pelajaran.

1. Pastikan jumlah soal pada setiap jenis memenuhi jumlah yang akan ditampilkan.
2. Pastikan setiap soal objektif memiliki kunci dan bobot yang benar.
3. Buat jadwal dengan tanggal, durasi, dan durasi minimum yang sesuai.
4. Tentukan pengacakan soal dan opsi.
5. Aktifkan token jika token akan diumumkan di ruang ujian.
6. Atur SEB ke **Opsional** saat simulasi dan **Wajib** setelah seluruh perangkat lulus uji.
7. Aktifkan jadwal setelah pemeriksaan selesai.

## Lakukan simulasi

Jalankan simulasi menggunakan kondisi jaringan dan perangkat yang sama dengan ujian.

1. Buat akun dan jadwal khusus simulasi.
2. Masuk dari setiap jenis perangkat yang digunakan.
3. Uji login, token, pemuatan soal, penyimpanan jawaban, timer, dan selesai ujian.
4. Jika memakai SEB, uji Config Key pada setiap platform.
5. Putuskan jaringan satu perangkat selama satu menit, lalu sambungkan kembali.
6. Periksa hasil dan event audit setelah simulasi.
7. Catat kapasitas perangkat dan jaringan yang berhasil digunakan bersamaan.

## Buka pelaksanaan

Pada hari ujian, lakukan pemeriksaan berikut sebelum membagikan token.

1. Pastikan waktu server sesuai zona `Asia/Jakarta`.
2. Pastikan Apache, MySQL/MariaDB, penyimpanan, dan jaringan aktif.
3. Pastikan `cloudflared` berstatus `Running` dan domain Online dapat dibuka; pastikan juga alamat LAN dapat dibuka. Tetapkan jalur yang dipakai sesi ini dan umumkan.
4. Buka **Aktivitas Siswa** pada perangkat proktor.
5. Pastikan jadwal berstatus aktif serta tanggal dan sesi sesuai.
6. Pastikan pengawas memiliki daftar peserta dan berita acara.
7. Bagikan berkas `.seb` lebih dahulu jika jadwal mewajibkan SEB.
8. Tampilkan token hanya setelah pengawas mengizinkan mulai.

## Pantau ujian

Gunakan status aplikasi bersama pengawasan fisik di ruang ujian.

1. Pantau kolom status, heartbeat, waktu tersisa, dan pelanggaran pindah tab per peserta.
2. Cocokkan permintaan **Izinkan Masuk Kembali** atau **Perpanjang Waktu** dengan nama dan nomor peserta.
3. Catat waktu, alasan, perangkat, dan tindakan untuk setiap intervensi.
4. Jika jaringan terputus, minta siswa mempertahankan halaman ujian. Bila perlu pindah jalur, ikuti prosedur failover pada [Laboratorium Hybrid](lab-hybrid.md#6-prosedur-pindah-jalur-di-tengah-ujian).
5. Jika server bermasalah luas, hentikan perubahan dan pulihkan layanan sebelum meminta siswa memuat ulang.

Jangan menilai satu event perpindahan tab sebagai bukti tunggal. Periksa pola event, catatan pengawas, dan kondisi perangkat.

## Pindah jalur (Online ke LAN)

Lakukan hanya bila gangguan memang pada jalur Online, bukan pada PC server.

1. Umumkan satu instruksi yang sama ke seluruh peserta.
2. Alihkan client secara massal lewat Veyon ke shortcut **CBT Lokal (Darurat)**.
3. Minta peserta login ulang di alamat LAN dengan akun dan token yang sama; sesi browser baru memang diperlukan.
4. Periksa **Aktivitas Siswa**: semua peserta harus kembali berstatus *sedang mengerjakan* sebelum ujian dilanjutkan.
5. Catat jalur, jam perpindahan, dan penyebabnya di berita acara.

## Tutup ujian

Setelah sesi selesai, pastikan jawaban dan status peserta telah tersimpan.

1. Tunggu seluruh siswa menekan selesai atau timer berakhir.
2. Cocokkan jumlah peserta selesai dengan daftar hadir.
3. Catat siswa yang tidak selesai atau mengalami kendala.
4. Nonaktifkan jadwal atau hentikan distribusi token.
5. Buka hasil, analisis soal, dan rekap nilai.
6. Buat backup database.

## Tangani insiden

Gunakan tingkat respons berikut agar tindakan proporsional dengan dampak.

| Dampak | Contoh | Tindakan pertama |
|---|---|---|
| Satu siswa | Login, SEB, atau perangkat gagal | Pindahkan perangkat atau lakukan reset terverifikasi |
| Satu ruang | Access point atau listrik ruang gagal | Catat waktu, pertahankan halaman, pulihkan jaringan |
| Semua peserta | Database atau web server berhenti | Hentikan instruksi siswa, pulihkan server, verifikasi data |
| Dugaan kompromi | Key tersebar atau akun dipakai pihak lain | Nonaktifkan jadwal, rotasi token/key, simpan bukti |

## Langkah berikutnya

Setelah pelaksanaan, lanjutkan dengan tindakan berikut.

- [Laboratorium Hybrid](lab-hybrid.md) untuk penyiapan server, Cloudflare Tunnel, Veyon, dan dua shortcut client.
- [Pemeliharaan](pemeliharaan.md) untuk backup dan pemulihan.
- [Pemecahan masalah](troubleshooting.md) untuk mendiagnosis kegagalan spesifik.
- [Cara kerja keamanan ujian](keamanan-ujian.md) untuk menafsirkan event audit.


---

# Panduan siswa

Panduan ini membantu siswa masuk, memeriksa jadwal, mengerjakan ujian, dan menyelesaikannya dengan aman. Gunakan hanya akun Anda sendiri dan ikuti arahan proktor.

## Sebelum ujian

Siapkan perangkat dan akses sebelum waktu mulai.

1. Pastikan perangkat tersambung ke jaringan ujian dan memiliki daya yang cukup.
2. Siapkan username, kata sandi, nomor peserta, dan token jika diminta.
3. Jika jadwal mewajibkan SEB, buka berkas konfigurasi `.seb` atau Start URL yang diberikan proktor.
4. Tutup aplikasi yang tidak diizinkan sebelum membuka SEB.
5. Masuk menggunakan akun Anda sendiri.

Hubungi proktor jika nama, kelas, mata pelajaran, atau durasi pada layar tidak sesuai.

## Mulai ujian

Halaman konfirmasi memeriksa jadwal, token, browser, dan kebijakan keamanan.

1. Buka menu CBT atau daftar ujian.
2. Pilih ujian yang tersedia.
3. Periksa mata pelajaran, kelas, jumlah soal, dan durasi.
4. Masukkan token jika kolom token tampil.
5. Tunggu sampai status keamanan browser selesai diperiksa.
6. Klik **MULAI**.

Jika tombol **MULAI** tetap nonaktif pada jadwal SEB wajib, jangan pindah ke browser biasa. Hubungi proktor untuk memeriksa konfigurasi SEB.

## Kerjakan soal

Jawaban tersimpan ke server selama sesi ujian masih aktif.

1. Baca stimulus (wacana, gambar, atau tabel) lalu soal, kemudian pilih jawaban.
2. Tunggu respons penyimpanan sebelum berpindah cepat ke soal berikutnya.
3. Gunakan navigasi nomor untuk meninjau soal yang belum dijawab, dan tombol **Ragu-ragu** untuk menandai soal yang ingin ditinjau ulang.
4. Perhatikan sisa waktu pada layar.
5. Tetap berada di halaman ujian dan dalam mode fullscreen jika diminta.

Aplikasi mencatat perpindahan tab, keluar fullscreen, status offline, dan gangguan heartbeat. Pencatatan tidak otomatis menyatakan siswa curang, tetapi proktor dapat meninjaunya bersama kejadian di ruang ujian.

## Selesaikan ujian

Pastikan semua jawaban yang ingin dikirim sudah terisi sebelum menyelesaikan ujian.

1. Buka nomor terakhir atau tombol selesai.
2. Tinjau pemberitahuan soal yang belum dijawab.
3. Klik konfirmasi selesai jika Anda yakin.
4. Tunggu sampai aplikasi kembali ke daftar ujian atau menampilkan status selesai.
5. Jangan menutup perangkat sebelum proktor mengizinkan.

Status selesai bersifat final untuk percobaan tersebut kecuali proktor melakukan tindakan pemulihan sesuai kebijakan sekolah.

## Tangani gangguan jaringan

Jika koneksi terputus, pertahankan halaman ujian agar jawaban lokal tidak hilang.

1. Beri tahu proktor tanpa menutup browser atau SEB.
2. Tunggu jaringan kembali tersambung.
3. Setelah online, periksa jawaban terakhir dan timer.
4. Muat ulang halaman hanya jika proktor meminta.

Jika PC mati atau browser tertutup, Anda dapat login kembali di PC lain dan melanjutkan dari jawaban terakhir yang tersimpan dengan sisa waktu yang sama. PC lama harus sudah tidak aktif lebih dari 60 detik; jika masih terbuka, PC baru akan ditolak sampai PC lama ditutup atau didiamkan.

## Langkah berikutnya

Gunakan panduan berikut jika Anda mengalami masalah.

- [Pemecahan masalah](troubleshooting.md) untuk pesan SEB, login, soal kosong, atau koneksi.
- [Cara kerja keamanan ujian](keamanan-ujian.md) untuk memahami data yang dicatat selama ujian.


---

# Menggunakan Safe Exam Browser

Panduan ini membantu administrator dan proktor menghubungkan jadwal GarudaCBT dengan Safe Exam Browser (SEB). Integrasi memvalidasi Config Key atau Browser Exam Key melalui header HTTP maupun JavaScript API resmi SEB.

## Kebijakan SEB global

Semua jadwal memakai satu kebijakan SEB global dan satu file `.seb` resmi sekolah.

| Kebijakan | Perilaku |
|---|---|
| **Wajib** (`REQUIRED`) | Portal siswa dan ujian ditolak bila browser atau key SEB tidak cocok |

## Buat konfigurasi SEB

Gunakan SEB Config Tool pada platform yang dipakai sekolah.

1. Atur Start URL ke halaman aplikasi melalui HTTPS, misalnya `https://ujian.sekolah.sch.id/`.
2. Batasi navigasi, aplikasi lain, clipboard, tangkapan layar, dan keluar dari SEB sesuai kebijakan sekolah.
3. Aktifkan **Use Browser & Config Keys (send in HTTP header)** untuk kompatibilitas browser engine klasik.
4. Simpan konfigurasi sebagai berkas `.seb` dan lindungi perubahan konfigurasi dengan kata sandi admin.
5. Salin **Config Key** dari SEB Config Tool.
6. Jika memakai Browser Exam Key, salin key untuk setiap versi dan platform yang diizinkan.

Config Key lebih mudah dikelola karena tetap sama lintas platform selama isi konfigurasi tidak berubah. Browser Exam Key dapat berubah menurut versi SEB.

## Satu berkas .seb untuk semua jalur

Setiap PC client memakai satu berkas `.seb` dengan Start URL global yang sama untuk Online dan LAN:

| Berkas | Start URL | Dipakai shortcut |
|---|---|---|
| `GarudaCBT.seb` | `https://ujian.sekolah.sch.id/` | **Semua akses** |

Pastikan DNS/routing sekolah membuat URL tersebut dapat dijangkau dari kedua jaringan. Simpan berkas di `C:\CBT\` pada setiap PC client.

### Membuat kedua berkas dari server

Kedua berkas dapat dibuat ulang tanpa membuka SEB Config Tool:

```powershell
cd C:\xampp\htdocs\garudacbt
C:\xampp\php\php.exe tools\buat-seb.php online C:\CBT\GarudaCBT.seb
```

Perintah ini mencetak **Config Key** tiap berkas. Keluaran bersifat deterministik: menjalankan ulang perintah yang sama menghasilkan Config Key yang sama selama isi konfigurasi tidak berubah.

Kata sandi administrator dan kata sandi keluar SEB diambil dari variabel lingkungan, sehingga tidak tersimpan di dalam repo:

```powershell
$env:SEB_ADMIN_PASSWORD = 'kata-sandi-admin-sekolah'
$env:SEB_QUIT_PASSWORD  = 'kata-sandi-keluar'
C:\xampp\php\php.exe tools\buat-seb.php online C:\CBT\SEB-Online.seb
```

Tanpa `SEB_QUIT_PASSWORD`, peserta dapat keluar dari SEB tanpa kata sandi. Isi keduanya untuk pelaksanaan sesungguhnya.

Berkas dibuat dengan prefiks `plnd` (tidak dienkripsi, hanya dikompresi ganda) sesuai spesifikasi berkas SEB. Artinya peserta yang memahami format dapat membaca pengaturan di dalamnya. Yang menjaga ujian tetap terkunci adalah Config Key global pada server, bukan kerahasiaan berkas. Bila sekolah memerlukan berkas terenkripsi kata sandi, buka berkas hasil alat ini di SEB Config Tool, lalu simpan ulang dengan kata sandi; Config Key akan berubah dan harus diperbarui pada environment server.


## Hubungkan key global ke server

Pasang key hasil pembuatan file `.seb` pada environment server. Operator tidak mengisi key pada jadwal.

```powershell
$env:GARUDACBT_SEB_START_URL = 'https://ujian.sekolah.sch.id/'
$env:GARUDACBT_SEB_CONFIG_KEY = '<64 karakter Config Key>'
$env:GARUDACBT_SEB_BROWSER_EXAM_KEYS = '<opsional, satu key per baris>'
```

Setelah itu semua jadwal otomatis memakai kebijakan SEB global.

## Uji konfigurasi

Gunakan akun siswa uji dan perangkat yang sama dengan pelaksanaan sebenarnya.

1. Buka berkas `.seb`.
2. Masuk sebagai siswa uji.
3. Buka jadwal ujian.
4. Pastikan halaman konfirmasi menampilkan **Safe Exam Browser terverifikasi**.
5. Mulai ujian dan tunggu lebih dari 30 detik untuk menguji heartbeat.
6. Uji muat ulang halaman, putus-sambung jaringan, dan penyelesaian ujian.
7. Ulangi pada setiap platform dan versi SEB yang diizinkan.

Jangan memakai akun atau jadwal produksi untuk simulasi jika hasilnya akan masuk ke rekap nilai resmi.

## Rotasi konfigurasi

Perubahan apa pun pada berkas `.seb` dapat mengubah Config Key.

1. Simpan konfigurasi SEB yang baru.
2. Salin Config Key baru.
3. Perbarui `GARUDACBT_SEB_CONFIG_KEY` pada environment server.
4. Uji kembali dengan akun siswa uji.
5. Distribusikan berkas `.seb` baru dan tarik versi lama.

## Cara validasi bekerja

SEB mengirim hash yang bergantung pada URL halaman. Server menghitung hash yang sama dari URL absolut dan key yang disimpan, lalu membandingkannya secara aman.

Implementasi mendukung dua jalur resmi:

- `X-SafeExamBrowser-ConfigKeyHash` dan `X-SafeExamBrowser-RequestHash` untuk engine yang mengirim header.
- `SafeExamBrowser.security.configKey` dan `browserExamKey` untuk JavaScript API, termasuk WKWebView modern.

Lihat [dokumentasi Config Key SEB](https://safeexambrowser.org/developer/seb-config-key.html) dan [spesifikasi Browser Exam Key](https://safeexambrowser.org/developer/documents/SEB-Specification-BrowserExamKey.pdf) untuk rumus dan kompatibilitas resmi.

## Langkah berikutnya

Gunakan dokumen berikut untuk pelaksanaan aman.

- [Laboratorium Hybrid](lab-hybrid.md) untuk memasang dua shortcut dan berkas `.seb` per jalur.
- [Operasional ujian](operasional-ujian.md) untuk simulasi dan daftar periksa proktor.
- [Cara kerja keamanan ujian](keamanan-ujian.md) untuk memahami batas perlindungan SEB.
- [Pemecahan masalah](troubleshooting.md) untuk key tidak cocok atau tombol mulai nonaktif.

---

# Laboratorium Hybrid: Online dan LAN

Panduan ini menyiapkan satu PC server dan 25 PC client agar Garuda CBT dapat dibuka dari **dua jalur** dengan data yang sama: domain HTTPS (Cloudflare Tunnel) dan alamat IP LAN.

Kedua jalur bukan dua sistem. Keduanya membuka aplikasi dan database yang persis sama di PC server yang sama. Yang berbeda hanya alamat yang diketik di browser.

```text
JALUR A - ONLINE                              JALUR B - LAN
peserta -> internet -> Cloudflare Edge        PC client 1-25 -> switch lab
  -> Cloudflare Tunnel (cloudflared)            -> PC server langsung (tanpa internet)
  -> PC server https://cbt.bhydev.my.id         http://192.168.1.10/garudacbt
```

## 1. Siapkan PC server

| Item | Ketentuan |
|---|---|
| Stack | Windows + XAMPP, PHP 7.4-8.3, Apache dan MySQL berjalan otomatis saat PC menyala |
| IP | IP statis di LAN lab, tidak berubah selama periode ujian (contoh `192.168.1.10`) |
| Daya | Restart otomatis Windows Update dinonaktifkan pada hari ujian; PC server tidak dipakai peserta |
| Folder | Aplikasi di `htdocs/garudacbt`, satu database `garuda_cbt` untuk kedua jalur |

Tetapkan IP statis lewat **Control Panel > Network > Adapter > IPv4 > Use the following IP address**, lalu catat di berita acara teknis.

## 2. Pasang Cloudflare Tunnel sebagai service

`cloudflared` wajib hidup otomatis saat PC menyala, karena ia komponen jalur utama Online.

```powershell
# 1) Unduh cloudflared (winget atau MSI resmi Cloudflare)
winget install --id Cloudflare.cloudflared

# 2) Login ke akun Cloudflare sekolah (hanya sekali, membuka browser)
cloudflared tunnel login

# 3) Buat tunnel dan arahkan ke Apache lokal
cloudflared tunnel create garudacbt
cloudflared tunnel route dns garudacbt cbt.bhydev.my.id
```

Buat berkas `C:\ProgramData\cloudflared\config.yml`:

```yaml
tunnel: garudacbt
credentials-file: C:\Users\<user>\.cloudflared\<tunnel-id>.json
ingress:
  - hostname: cbt.bhydev.my.id
    service: http://localhost:80
  - service: http_status:404
```

Pasang sebagai service supaya tidak bergantung pada sesi login:

```powershell
cloudflared --config C:\ProgramData\cloudflared\config.yml service install
Start-Service cloudflared
Get-Service cloudflared          # harus Running
```

Periksa **sebelum sesi pertama**: buka `https://cbt.bhydev.my.id/` dari luar lab. Bila gagal, jalur Online belum siap dan sesi harus dijalankan lewat LAN.

> Keamanan: halaman admin kini terekspos internet. Ganti password admin bawaan, pakai password kuat, dan pertimbangkan **Cloudflare Access** untuk membatasi `/auth` dan menu pengaturan.

## 3. `base_url` dinamis

Aplikasi tidak boleh mengunci alamat. `application/config/config.php` membangun `base_url` dari host yang sedang dipakai, sehingga aset, form login, dan pengumpulan jawaban mengikuti jalur yang aktif.

Bukti cepat dari PC server:

```powershell
(Invoke-WebRequest 'http://localhost/garudacbt/auth' -UseBasicParsing).Content -match 'http://localhost/garudacbt/'
(Invoke-WebRequest 'http://127.0.0.1/garudacbt/auth' -UseBasicParsing).Content -match 'http://127.0.0.1/garudacbt/'
```

Keduanya harus `True`. Bila salah satu `False`, `base_url` dikunci ke satu alamat dan peserta akan terlempar ke jalur yang salah.

## 4. Siapkan 25 PC client

Setiap PC client punya **dua shortcut** di desktop dengan nama persis:

1. **CBT Online** - domain HTTPS resmi.
2. **CBT Lokal (Darurat)** - alamat LAN server.

Nama tidak boleh diubah agar instruksi proktor tidak ambigu. Buat keduanya dengan skrip yang disertakan:

```powershell
powershell -ExecutionPolicy Bypass -File C:\CBT\Buat-Shortcut-CBT.ps1 `
  -OnlineUrl "https://cbt.bhydev.my.id/" `
  -LanUrl    "http://192.168.1.10/garudacbt/" `
  -SebOnline "C:\CBT\SEB-Online.seb" `
  -SebLokal  "C:\CBT\SEB-Lokal.seb"
```

Bila berkas `.seb` tersedia, shortcut menjalankan Safe Exam Browser. Bila belum, skrip jatuh ke Chrome/Edge mode kiosk supaya PC tetap terpakai saat simulasi. Lihat [Safe Exam Browser](safe-exam-browser.md) untuk membuat berkas `.seb` dan Config Key.

Untuk 25 PC sekaligus, jalankan skrip itu dari PC proktor lewat **Veyon > Execute command on selected computers**.

## 5. Pantau dan alihkan lewat Veyon

Veyon dipakai proktor untuk menyalakan, memantau, dan mengalihkan client secara massal.

1. Pasang Veyon Master di PC proktor dan Veyon Service di setiap client.
2. Beri nama client sesuai nomor PC (`PC-01` ... `PC-25`) agar cocok dengan daftar hadir.
3. Sebelum sesi: **Start all computers**, lalu buka semua layar untuk memastikan tidak ada yang tertinggal di desktop.
4. Saat pindah jalur: pilih semua client, jalankan perintah
   `"C:\Users\Public\Desktop\CBT Lokal (Darurat).lnk"`
   atau klik kanan shortcut lewat remote control.
5. Setelah sesi: matikan client dari Veyon setelah semua peserta berstatus selesai.

## 6. Prosedur pindah jalur di tengah ujian

Wajib dilatih, bukan hanya ditulis.

1. Pastikan gangguan memang pada jalur Online (internet / tunnel), bukan pada PC server. Bila server yang bermasalah, pindah jalur tidak menolong.
2. Umumkan **satu** instruksi yang sama ke seluruh peserta, lalu alihkan client secara massal lewat Veyon ke **CBT Lokal (Darurat)**.
3. Peserta login ulang di alamat LAN dengan akun dan token sesi yang sama. Pindah alamat berarti sesi browser baru, jadi login ulang memang diperlukan; sampaikan agar tidak dianggap error.
4. Peserta melanjutkan dari jawaban terakhir yang tersimpan dengan sisa waktu yang sama. Proktor memeriksa **Aktivitas Siswa**: seluruh peserta harus kembali berstatus *sedang mengerjakan* sebelum ujian dilanjutkan.
5. Catat perpindahan jalur, jam, dan penyebabnya di berita acara sesi.

## 7. Periksa sebelum sesi

- [ ] `Get-Service cloudflared` = `Running`.
- [ ] Login terbuka dari **kedua** alamat; jalur sesi ini ditetapkan dan diumumkan.
- [ ] `base_url` dinamis terbukti (langkah 3).
- [ ] Kedua shortcut ada di desktop **semua** 25 client.
- [ ] Veyon menampilkan 25 layar.
- [ ] Backup database sebelum sesi pertama dibuat.
- [ ] Token sesi aktif; daftar hadir dan kartu peserta cadangan tercetak.
- [ ] Dashboard **Aktivitas Siswa** terbuka di PC proktor.

## Langkah berikutnya

- [Operasional ujian](operasional-ujian.md) untuk urutan pelaksanaan dan penutupan sesi.
- [Pemeliharaan](pemeliharaan.md) untuk backup sebelum dan sesudah hari ujian.
- [Pemecahan masalah](troubleshooting.md) bila salah satu jalur gagal.

---

# Backup, pembaruan, dan pemulihan

Panduan ini membantu operator server melindungi data dan memperbarui GarudaCBT. Selalu hentikan pelaksanaan ujian sebelum operasi database atau penggantian kode.

## Buat backup

Buat backup sebelum pembaruan, impor massal, koreksi nilai massal, dan penghapusan data.

1. Masuk sebagai administrator.
2. Buka **Pengaturan > Database > Backup**.
3. Jalankan backup database.
4. Unduh atau salin hasil backup ke media di luar server aplikasi.
5. Salin folder `uploads` jika backup aplikasi tidak menyertakan lampiran.
6. Catat tanggal, versi aplikasi, dan periode data.
7. Uji pemulihan pada server nonproduksi secara berkala.

Backup yang hanya tersimpan pada disk server yang sama tidak melindungi data dari kerusakan disk atau ransomware.

## Perbarui aplikasi

Gunakan jendela pemeliharaan ketika tidak ada siswa atau guru yang aktif.

1. Umumkan waktu pemeliharaan.
2. Buat backup database dan folder aplikasi.
3. Simpan salinan `application/config/database.php` dan konfigurasi web server.
4. Ganti kode aplikasi dengan versi baru tanpa menghapus folder `uploads` atau backup.
5. Pastikan konfigurasi database tetap menunjuk ke database yang benar.
6. Jalankan pembaruan database lewat **Pengaturan > Database > Update** sebagai administrator (halaman ini menolak akses tanpa login admin).
7. Jalankan migrasi keamanan:

   ```powershell
   C:\xampp\php\php.exe index.php migrate CreateMigration
   ```

8. Uji login administrator, guru, dan siswa.
9. Uji satu jadwal simulasi sebelum membuka layanan.

## Pulihkan database

Dua jalur tersedia: menu **Pemulihan Database** (`dbpemulihan`) untuk restore dari berkas `.sql.zip` di folder `backups/`, dan `mysqldump`/`mysql` untuk restore manual. Keduanya sudah diuji.

Pulihkan hanya dari backup yang telah diverifikasi dan sesuai dengan versi kode.

1. Hentikan akses pengguna ke aplikasi.
2. Buat salinan kondisi database saat ini untuk kebutuhan investigasi.
3. Impor backup ke database pemulihan terpisah terlebih dahulu.
4. Periksa jumlah pengguna, jadwal, jawaban, dan hasil.
5. Arahkan konfigurasi aplikasi ke database pemulihan atau ganti database produksi sesuai prosedur server.
6. Jalankan migrasi yang diperlukan untuk versi kode saat ini.
7. Uji alur login dan ujian sebelum membuka akses.

## Bersihkan data

Menu **Data Manager** sudah dinonaktifkan karena menjalankan `drop_table` pada semua tabel lewat `GET` tanpa konfirmasi. Membersihkan data sekarang dilakukan lewat backup, koreksi manual, atau restore, bukan lewat menu aplikasi.

1. Pastikan tidak ada ujian aktif.
2. Buat dan unduh backup penuh.
3. Tentukan tabel dan rentang data yang akan dibersihkan; catat di berita acara.
4. Minta persetujuan operator kedua untuk data nilai atau jawaban ujian.
5. Jalankan `DELETE` bersyarat lewat `mysql.exe`, jangan `DROP`/`TRUNCATE` seluruh tabel.

```powershell
# contoh: hapus jawaban satu jadwal yang dibatalkan
C:\xampp\mysql\bin\mysql.exe -u root -e "DELETE FROM garuda_cbt.cbt_soal_siswa WHERE id_jadwal=123;"
```

Setelah pembersihan, jalankan ulang rekap agar nilai tidak menyisakan baris yatim.

## Backup terjadwal

PRD FR-27 mewajibkan backup terjadwal, bukan hanya manual.

```powershell
# Daftarkan tugas harian pukul 05:30 dan 21:30 (sebelum & sesudah sesi ujian)
schtasks /Create /TN "GarudaCBT Backup Pagi" /TR "C:\xampp\php\php.exe C:\xampp\htdocs\garudacbt\index.php dbpemulihan backup" /SC DAILY /ST 05:30 /RU SYSTEM /RL HIGHEST /F
schtasks /Create /TN "GarudaCBT Backup Malam" /TR "C:\xampp\php\php.exe C:\xampp\htdocs\garudacbt\index.php dbpemulihan backup" /SC DAILY /ST 21:30 /RU SYSTEM /RL HIGHEST /F
schtasks /Query /TN "GarudaCBT Backup Pagi" /V /FO LIST
```

Berkas hasil tersimpan di `backups/` dengan nama `backup-db-YYYY-mm-dd-HH-MM-SS.sql.zip`. Salin hasil ke media di luar server minimal sekali sehari.

## Pantau kapasitas

Periksa sumber daya server sebelum dan selama periode ujian.

- Sisa ruang disk untuk database, log, backup, dan unggahan.
- Penggunaan memori serta CPU Apache dan MySQL/MariaDB.
- Ukuran tabel jawaban, attempt, dan event ujian.
- Pertumbuhan `application/logs`.
- Keberhasilan backup terbaru.

## Langkah berikutnya

Gunakan panduan berikut untuk verifikasi lanjutan.

- [Referensi konfigurasi](referensi.md) untuk lokasi konfigurasi dan tabel keamanan.
- [Pemecahan masalah](troubleshooting.md) untuk kegagalan database atau migrasi.
- [Operasional ujian](operasional-ujian.md) untuk daftar periksa sebelum membuka layanan.


---

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


---

# Cara kerja keamanan ujian

GarudaCBT mengurangi peluang manipulasi dengan memindahkan keputusan penting ke server dan mendukung Safe Exam Browser. Perlindungan ini harus dipadukan dengan pengawasan, jaringan yang dikelola, kebijakan sekolah, dan prosedur insiden.

## Batas kepercayaan

Browser siswa tidak menjadi sumber kebenaran untuk identitas, waktu, atau kepemilikan soal.

Server melakukan pemeriksaan berikut:

- Mengambil identitas siswa dari session akun yang masuk.
- Mengganti ID siswa, jadwal, dan bank yang dikirim form dengan data server.
- Memastikan soal jawaban dimiliki siswa pada jadwal dan bank tersebut.
- Menghitung waktu berjalan serta deadline dari server.
- Menolak attempt yang selesai, kedaluwarsa, atau terikat pada browser lain.
- Menyaring kunci jawaban dari payload pemuatan soal.

## Siklus attempt

Satu siswa memiliki paling banyak satu attempt untuk satu jadwal.

```text
Belum mulai
    |
    v
IN_PROGRESS ---- deadline terlewati ----> EXPIRED
    |
    +---- selesai berhasil -------------> SUBMITTED
```

Heartbeat memperbarui tanda aktivitas, tetapi tidak memperpanjang deadline.

## Peran Safe Exam Browser

SEB membatasi lingkungan perangkat, sedangkan server memeriksa bahwa konfigurasi SEB sesuai dengan jadwal.

Server menerima validasi melalui header klasik atau JavaScript API. Nilai hash bergantung pada URL, sehingga key asli tidak dikirim sebagai teks biasa pada setiap request.

SEB tidak menggantikan HTTPS, keamanan akun, pengawasan fisik, atau pengelolaan perangkat. Perangkat yang sudah dikompromikan tetap memerlukan kontrol tambahan di luar aplikasi.

## Arti event audit

Event browser merupakan sinyal yang perlu dikorelasikan dengan data lain.

Contohnya, `PAGE_HIDDEN` dapat terjadi karena siswa berpindah aplikasi, tetapi juga dapat dipicu dialog sistem. `OFFLINE` dapat menandakan gangguan jaringan ruang, bukan tindakan siswa.

Gunakan event bersama:

- Catatan waktu proktor.
- Rekaman gangguan jaringan atau listrik.
- Status attempt dan heartbeat.
- Pola berulang pada satu siswa dibandingkan seluruh ruang.
- Kebijakan sekolah yang telah diumumkan.

## Data sensitif

Config Key, Browser Exam Key, kredensial database, encryption key, backup, dan akun administrator merupakan data sensitif.

Batasi akses berdasarkan tugas, jangan kirim key lengkap melalui grup siswa, dan rotasi konfigurasi jika key tersebar. Simpan backup di lokasi terenkripsi dengan akses terbatas.

## Keterbatasan

Tidak ada aplikasi web yang dapat menjamin kecurangan tidak terjadi.

Kontrol saat ini tidak dapat sepenuhnya mencegah kamera eksternal, bantuan orang lain di luar pengawasan, perangkat kedua, kebocoran soal sebelum ujian, atau kompromi sistem operasi. Gunakan variasi soal, pengacakan, durasi wajar, pengawasan, dan analisis hasil untuk melengkapi kontrol teknis.


---

## Lisensi

CBT Madah mempertahankan lisensi MIT dari GarudaCBT. Hak cipta dan pemberitahuan lisensi wajib dipertahankan pada salinan atau bagian substansial perangkat lunak. Lihat [LICENCE.md](../LICENCE.md) dan [license.txt](../license.txt).
