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

