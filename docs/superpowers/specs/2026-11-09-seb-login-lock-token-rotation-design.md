# Penguncian Login SEB dan Rotasi Token Ujian

## Tujuan

Mengurangi peluang siswa keluar dari Safe Exam Browser (SEB), berpindah perangkat, lalu melanjutkan ujian tanpa izin pengawas. Siswa dengan ujian aktif dikunci ketika logout atau kehilangan heartbeat selama 90 detik. Hanya admin atau proktor yang dapat membuka kunci. Token masuk ujian diganti oleh server setiap 15 menit.

## Batasan

- Penguncian hanya berlaku ketika siswa memiliki attempt berstatus `IN_PROGRESS`; logout biasa di luar ujian tetap normal.
- Jawaban, nomor soal, dan waktu ujian tidak dihapus ketika siswa dikunci atau dibuka kembali. Waktu ujian tetap berjalan.
- SEB yang tertutup paksa tidak dapat selalu mengirim notifikasi terakhir. Server memakai tiga heartbeat yang hilang (90 detik) sebagai bukti keluar atau terputus.
- CSRF, pemeriksaan SEB, satu akun satu perangkat, dan audit event yang sudah ada tetap dipakai.
- Token baru hanya diperlukan untuk memulai ujian. Attempt yang sudah berjalan tidak dihentikan saat token berubah.

## Pendekatan

1. Mengunci pada heartbeat pertama yang gagal. Paling ketat, tetapi terlalu mudah mengunci siswa karena jaringan sesaat.
2. Membolehkan perangkat baru mengambil alih otomatis setelah heartbeat basi. Ini perilaku sekarang dan terlalu longgar untuk tujuan pencegahan kecurangan.
3. **Dipilih:** heartbeat tetap setiap 30 detik; logout mengunci segera dan kehilangan heartbeat selama 90 detik mengunci pada kontak berikutnya. Admin/proktor harus membuka kunci sebelum siswa dapat login dan mengikat perangkat lagi.

Token dapat diganti oleh timer browser, cron, atau secara server-side saat token dibaca. **Dipilih:** rotasi server-side secara lazy dan atomik. Permintaan pertama setelah usia token mencapai 15 menit mengganti token di database. Cara ini tidak membutuhkan cron dan tetap bekerja ketika halaman token admin ditutup.

## Perilaku Login dan SEB

1. Saat attempt dimulai, heartbeat tetap dikirim setiap 30 detik.
2. Endpoint heartbeat membandingkan waktu server dengan `last_heartbeat_at` sebelum memperbaruinya.
3. Jika jeda lebih dari 90 detik, server menandai attempt terkunci, mencatat event `HEARTBEAT_MISSED`, dan mengunci login siswa.
4. Perangkat atau sesi baru tidak lagi boleh mengambil alih attempt yang heartbeat-nya basi. Percobaan tersebut mengunci login dan ditolak.
5. Logout siswa dengan attempt aktif langsung mengunci attempt dan login sebelum sesi aplikasi dihapus.
6. Login dengan akun terkunci ditolak dengan pesan agar siswa menghubungi pengawas.
7. Admin dapat memakai tombol Reset Login yang sudah ada. Proktor memakai tombol buka kunci pada Aktivitas Ujian. Keduanya membersihkan kegagalan login, membuka login siswa, menghapus binding perangkat lama, dan membuka `kicked_at` pada attempt terkait.
8. Reset wajib memiliki alasan. Event mencatat operator, waktu, alasan, siswa, dan attempt. Setelah reset, SEB baru dapat mengikat nonce baru.

Status kunci disimpan eksplisit pada data siswa melalui kolom waktu, alasan, dan attempt sumber. Status attempt tetap memakai `kicked_at` agar seluruh endpoint ujian yang ada langsung menolak akses. Migrasi harus aman dijalankan pada database yang sudah berisi ujian aktif.

## Rotasi Token 15 Menit

1. Sumber kebenaran tetap satu baris `cbt_token` di database.
2. Semua pembacaan dan pemeriksaan token melewati satu fungsi model yang mengunci baris, menghitung usia berdasarkan waktu server, lalu mengganti token bila usia sudah 15 menit.
3. Transaksi dan row lock memastikan dua request bersamaan tidak menghasilkan dua token berbeda.
4. Pengaturan otomatis diaktifkan dengan interval tetap 15 menit. Tombol ganti token manual tetap tersedia.
5. Halaman admin menampilkan token dan hitung mundur dari nilai server, tetapi tidak menjadi mesin rotasi.

## Antarmuka

- Status Aktivitas Ujian membedakan `Mengerjakan`, `Terputus`, dan `Terkunci—perlu reset`.
- Tombol buka kunci diberi label/tooltip `Reset Login` dan meminta alasan sebelum mengirim permintaan.
- Login siswa yang terkunci menampilkan pesan singkat: `Login dikunci karena siswa keluar dari sesi ujian. Hubungi pengawas untuk Reset Login.`

## Validasi

- Heartbeat dengan jeda sampai 90 detik diterima; jeda di atas 90 detik mengunci attempt dan login.
- Logout ketika attempt aktif mengunci; logout tanpa attempt aktif tidak mengunci.
- Sesi/perangkat baru tidak dapat mengambil alih sebelum reset.
- Reset admin dan proktor membuka login, menghapus binding lama, serta mempertahankan jawaban dan deadline.
- Token tidak berubah sebelum 15 menit, berubah pada request pertama setelah 15 menit, dan tetap tunggal pada request bersamaan.
- Siswa yang sudah mengerjakan tetap dapat menyimpan jawaban setelah token global berganti.

## Bukan Bagian Perubahan

- Tidak menambahkan cron, layanan antrean, atau dependency baru.
- Tidak memperpanjang waktu otomatis akibat gangguan jaringan.
- Tidak mengubah format soal, penilaian, atau hasil ujian.
