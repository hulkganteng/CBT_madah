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

