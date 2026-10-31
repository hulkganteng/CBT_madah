# Desain Pendamping Ujian Pertama Admin

## Intent

Membantu admin pemula membuat ujian pertama dari nol dengan alur yang terlihat, interaktif, dan mudah dipulihkan ketika prasyarat belum lengkap. Perubahan berfokus pada dashboard dan navigasi admin.

## Tujuan

- Menampilkan urutan kerja yang benar dari data sekolah sampai ujian selesai disiapkan.
- Menunjukkan status setiap langkah: belum mulai, perlu perhatian, atau selesai.
- Menyediakan tombol tindakan yang langsung menuju halaman existing.
- Menjelaskan alasan sebuah langkah diperlukan dengan bahasa sederhana.
- Menjaga semua endpoint, ID, query, session, database, keamanan ujian, dan business logic tetap sama.

## Bukan tujuan

- Membuat wizard backend baru.
- Mengubah cara membuat bank soal, jadwal, alokasi, token, atau penilaian.
- Menghapus menu lama atau memaksa pengguna lama mengikuti dashboard baru.
- Menambah tabel, endpoint, atau status tersimpan baru.

## Pendekatan yang dipertimbangkan

1. **Pendamping interaktif di dashboard (dipilih).** Kartu langkah memakai data dashboard dan tautan existing. Perubahan kecil, dapat dipakai tanpa migrasi, dan aman untuk alur lama.
2. **Sidebar baru berdasarkan tahapan.** Lebih rapi, tetapi mengubah kebiasaan pengguna lama dan masih tidak menjelaskan prasyarat pada halaman kerja.
3. **Wizard pembuatan ujian.** Paling terpandu, tetapi membutuhkan penggabungan banyak form dan validasi sehingga berisiko menyentuh business logic CBT.

Pendekatan pertama dipilih karena memberi bantuan interaktif dengan risiko paling rendah.

## Bentuk interaksi

Dashboard admin menampilkan panel **Buat Ujian Pertama** dengan tujuh langkah:

1. Profil sekolah dan periode aktif.
2. Data master: mapel, guru, siswa, dan rombel.
3. Struktur ujian: jenis ujian, sesi, dan ruang.
4. Bank soal dan pemeriksaan jumlah soal.
5. Jadwal ujian dan kebijakan pengacakan/SEB.
6. Penempatan peserta, nomor peserta, pengawas, dan token.
7. Pemeriksaan akhir melalui aktivitas siswa, cetak, dan simulasi.

Setiap kartu berisi nomor, judul, alasan, status, checklist ringkas, dan tombol tindakan. Kartu langkah berikutnya diberi penekanan visual. Tombol **Lanjutkan langkah berikutnya** membuka tindakan pertama yang belum selesai.

Panel dapat diciutkan atau disembunyikan sementara pada halaman aktif; checklist manual disimpan di `localStorage` agar tidak hilang saat kembali ke dashboard. Tampilan ini tidak mengubah data ujian.

## Sumber status

Gunakan nilai yang sudah dikirim dashboard (`info_box`, `ujian_box`, jadwal hari ini, dan profil/periode aktif). Status yang tidak dapat dipastikan dari data existing ditampilkan sebagai **Periksa** dengan tautan halaman terkait, bukan dianggap selesai. Tidak ada endpoint baru dan tidak ada klaim bahwa konfigurasi sudah benar hanya karena jumlah data lebih dari nol.

## State yang harus dirancang

- Instalasi baru tanpa data: tampilkan langkah pertama dan instruksi singkat.
- Data sebagian: tandai langkah yang masih memiliki prasyarat kosong.
- Semua data dasar tersedia: arahkan ke bank soal/jadwal.
- Jadwal sudah ada tetapi belum siap: arahkan ke pemeriksaan peserta, pengawas, token, dan SEB.
- Jadwal siap: tampilkan tindakan simulasi dan pemantauan.
- Layar sempit: kartu tetap satu kolom dan tombol dapat dijangkau keyboard.
- Dashboard lama: semua menu dan tautan existing tetap tersedia.

## Aksesibilitas dan copy

- Status tidak hanya dibedakan dengan warna; gunakan teks dan ikon.
- Setiap kartu dan tombol memiliki nama yang jelas.
- Fokus keyboard terlihat.
- Label memakai objek dan tindakan, misalnya **Atur peserta dan ruang**, bukan **Lanjut** saja.
- Pesan peringatan menjelaskan apa yang kurang dan cara memperbaikinya.

## Batas perubahan

Perubahan awal hanya pada view dashboard, stylesheet UI admin, dan JavaScript tampilan bila diperlukan. Tidak menyentuh controller, model, endpoint, migration, proses randomisasi, penyimpanan jawaban, session ujian, timer, penilaian, atau keamanan SEB.

## Verifikasi

- PHP lint untuk view PHP yang diubah.
- Pemeriksaan bahwa URL/ID menu existing tetap sama.
- Uji dashboard kosong, sebagian, dan lengkap.
- Uji tombol setiap langkah pada desktop 1366x768 dan 1920x1080.
- Uji keyboard dasar dan penyembunyian panel.
- Regression security existing tetap dijalankan.
