# PRD Modifikasi Garuda CBT — Khusus CBT Tanpa Rapor

**Product Requirements Document (PRD) — Garuda CBT, modifikasi khusus Computer-Based Test (CBT) saja, tanpa modul Rapor dan tanpa e-Learning.**

Dokumen ini menjadi acuan tunggal untuk memangkas Garuda CBT menjadi aplikasi ujian murni yang sesuai dengan hasil rapat penyesuaian sekolah: tiga tipe soal, jumlah soal baku per kelompok mata pelajaran, durasi 60 menit per mata pelajaran, dan pelaksanaan penuh di komputer laboratorium.

| Aspek | Ketentuan |
| --- | --- |
| Tipe soal | 3 — Pilihan Ganda, Pilihan Ganda Kompleks, Benar / Salah |
| Jumlah soal | Eksata 25 soal, Non-eksata 30 soal |
| Durasi ujian | 60 menit per mata pelajaran |
| Topologi lab | Hybrid — 1 server khusus + 25 PC peserta, diakses via Online (Cloudflare Tunnel) dan LAN lokal |

> **Keputusan inti:** Semua fitur Rapor (cetak rapor, leger rapor, buku induk yang terikat rapor) dan e-Learning (materi, tugas, nilai harian di luar ujian) dikeluarkan dari ruang lingkup. Yang dibangun, diuji, dan diterima hanyalah alur ujian: data master minimum, bank soal, jadwal dan token, pelaksanaan ujian terkunci, penilaian otomatis, analisis, dan rekap hasil ujian.

| Item | Keterangan |
| --- | --- |
| Produk dasar | Garuda CBT (open source, lisensi MIT) — https://github.com/garudacbt/cbt |
| Versi dokumen | 1.1 — 28 Oktober 2026 (revisi Hybrid) |
| Status | Siap ditinjau dan disahkan sekolah sebelum modifikasi dimulai |
| Penggunaan | Ujian berbasis komputer di laboratorium sekolah, model Hybrid: Online via Cloudflare Tunnel + LAN lokal |
| Disusun untuk | Kepala sekolah, waka kurikulum, guru mata pelajaran, proktor / teknisi lab |

Catatan lisensi: Garuda CBT berlisensi MIT, sehingga modifikasi diperbolehkan. Atribusi dan pemberitahuan lisensi asal wajib tetap dipertahankan di dalam aplikasi. Lihat Bab 14.

## Kontrol dokumen

### Riwayat versi

| Versi | Tanggal | Perubahan | Keterangan |
| --- | --- | --- | --- |
| 1.0 | 28 Okt 2026 | Dokumen awal: ruang lingkup CBT murni, spesifikasi rapat, arsitektur lab 1 server + 25 PC client, jalur utama LAN | Menunggu pengesahan sekolah |
| 1.1 | 28 Okt 2026 | Revisi Hybrid atas permintaan kepala sekolah untuk Full Online: satu server dan satu database diakses lewat dua jalur — Online via Cloudflare Tunnel (jalur resmi / pemantauan) dan LAN lokal (jalur lab dan fallback darurat). Ditambah konfigurasi `base_url` dinamis, dua shortcut client, prosedur pindah jalur, serta uji failover | Menunggu pengesahan sekolah |

### Pengesahan yang dibutuhkan

| Peran | Tanggung jawab pengesahan | Status |
| --- | --- | --- |
| Kepala sekolah / Waka Kurikulum | Ruang lingkup tanpa rapor, spesifikasi jumlah soal dan durasi | Belum disahkan |
| Perwakilan guru mapel | Tiga tipe soal dan kebutuhan bank soal per mapel | Belum disahkan |
| Proktor / Teknisi lab | Topologi server Hybrid, jaringan LAN, Cloudflare Tunnel, dan prosedur pindah jalur hari ujian | Belum disahkan |

> **Cara membaca dokumen ini:** Bab 4 adalah aturan baku dari rapat dan tidak boleh diubah oleh pengembang tanpa persetujuan sekolah. Bab 5 dan 6 adalah daftar pekerjaan modifikasi. Bab 10 memuat keputusan yang masih terbuka — bagian itu harus diputuskan sekolah sebelum fase pembangunan bank soal dimulai.

## Daftar isi

1. Ringkasan eksekutif
2. Tujuan, ruang lingkup, dan di luar ruang lingkup
3. Pengguna dan peran
4. Spesifikasi ujian hasil rapat sekolah
5. Kebutuhan fungsional
6. Rencana modifikasi Garuda CBT
7. Arsitektur dan deployment laboratorium
8. Kebutuhan non-fungsional
9. Alur ujian end-to-end
10. Penilaian, bobot, dan keputusan terbuka
11. Rencana implementasi bertahap
12. Pengujian dan kriteria penerimaan
13. Risiko dan mitigasi
14. Lisensi dan kepatuhan
15. Checklist operasional hari ujian

## 1. Ringkasan eksekutif

Sekolah membutuhkan aplikasi ujian berbasis komputer yang fokus, ringan, dan mudah dioperasikan di laboratorium. Garuda CBT dipilih sebagai basis karena open source, berlisensi MIT, dan sudah memiliki modul ujian yang lengkap. Namun Garuda CBT bawaan juga memuat modul Rapor dan e-Learning yang tidak dibutuhkan untuk kebutuhan ujian sekolah saat ini.

Modifikasi ini memangkas Garuda CBT menjadi CBT murni. Menu, peran, alur, dan laporan yang tidak berhubungan langsung dengan ujian disembunyikan atau dinonaktifkan. Hasilnya adalah aplikasi yang lebih sederhana untuk guru dan proktor, lebih ringan untuk PC server lab, dan lebih cepat dipelajari oleh siswa.

Pelaksanaan ujian menggunakan komputer laboratorium sepenuhnya: satu PC khusus sebagai server dan 25 PC sebagai client peserta, dengan model **Hybrid — satu server, satu database, dua jalur akses ke aplikasi yang sama**:

- **Jalur Online (jalur resmi, memenuhi permintaan kepala sekolah untuk Full Online):** domain HTTPS via Cloudflare Tunnel, contoh `https://cbt.bhydev.my.id`. Server dapat diakses dan dipantau dari mana saja, dan semua pihak memakai satu alamat resmi yang sama.
- **Jalur LAN (jalur lab dan fallback darurat):** alamat IP lokal server, contoh `http://192.168.1.10/garudacbt`. PC lab terhubung langsung ke server di ruangan yang sama tanpa muter lewat internet, sehingga lebih cepat dan tetap berjalan saat internet mati.

Dalam praktik lab, PC client diarahkan memakai jalur yang paling stabil pada hari ujian; bila jalur Online terganggu di tengah sesi, proktor mengalihkan seluruh client ke jalur LAN dan peserta melanjutkan dari jawaban terakhir yang tersimpan. Karena kedua jalur menunjuk ke server dan database yang sama, tidak ada pemisahan data antara mode Online dan mode LAN.

## 2. Tujuan, ruang lingkup, dan di luar ruang lingkup

### 2.1 Tujuan

- Menyediakan aplikasi CBT yang hanya memuat fungsi ujian, sesuai hasil rapat sekolah.
- Menjamin aturan rapat diterapkan oleh sistem, bukan oleh ingatan operator: jumlah soal, tipe soal, dan durasi dikunci dari konfigurasi.
- Memungkinkan 25 peserta ujian bersamaan dalam satu sesi di lab, dengan server yang tetap Full Online sesuai permintaan kepala sekolah dan ujian yang tidak berhenti hanya karena internet terganggu.
- Menyediakan dua jalur akses yang setara ke data yang sama: Online via Cloudflare Tunnel untuk akses resmi dan pemantauan dari mana saja, serta LAN lokal sebagai jalur lab dan fallback darurat.
- Menghasilkan nilai, analisis butir soal, dan rekap hasil ujian yang siap dipakai guru tanpa proses rapor.

### 2.2 Di dalam ruang lingkup

- Data master minimum untuk ujian: siswa, guru, kelas / rombel, mata pelajaran, tahun pelajaran, ruang, dan sesi.
- Bank soal untuk tiga tipe soal, jadwal ujian, token, pelaksanaan ujian terkunci, penilaian otomatis, analisis, dan rekap hasil ujian.
- Cetak yang berhubungan dengan ujian: kartu peserta, daftar hadir, berita acara, dan rekap nilai ujian.
- Akses Hybrid: Cloudflare Tunnel + domain Online, akses LAN lokal, `base_url` dinamis, dua shortcut client, dan prosedur pindah jalur.
- Backup dan restore database ujian.

### 2.3 Di luar ruang lingkup — dikeluarkan secara tegas

| Modul yang dikeluarkan | Perlakuan |
| --- | --- |
| Rapor (cetak rapor, leger, pengaturan rapor) | Menu dihapus dari navigasi, route dinonaktifkan, tidak diuji dan tidak diterima sebagai deliverable |
| Buku induk yang terikat proses rapor | Dikeluarkan. Data siswa tetap ada sebatas kebutuhan login dan peserta ujian |
| e-Learning (materi, tugas, nilai harian non-ujian) | Menu dihapus, route dinonaktifkan. Nilai yang disimpan hanyalah nilai hasil ujian CBT |
| Aplikasi HP / Exambro Android | Tidak dipakai. Peserta memakai PC lab dengan browser terkunci versi PC |

## 3. Pengguna dan peran

| Peran | Tugas utama | Hak akses yang dipertahankan |
| --- | --- | --- |
| Admin | Mengelola data master, pengguna, jadwal, dan backup | Semua menu CBT; tidak ada menu Rapor / e-Learning |
| Guru mapel | Menyusun bank soal, melihat hasil dan analisis mapelnya | Bank soal dan hasil ujian untuk mapel yang diampu saja |
| Proktor / Pengawas | Menjalankan sesi, membagikan token, memantau dan mereset peserta | Jadwal, token, status peserta, reset login, berita acara |
| Siswa | Login, memasukkan token, mengerjakan dan mengumpulkan ujian | Hanya ujian yang dijadwalkan untuk kelas dan sesinya |

Peran yang hanya melayani Rapor atau e-Learning dihapus dari pengelolaan pengguna. Satu akun tidak boleh melihat menu di luar perannya, dan percobaan membuka URL modul yang dinonaktifkan harus ditolak oleh server, bukan hanya disembunyikan dari menu.

## 4. Spesifikasi ujian hasil rapat sekolah

Spesifikasi berikut adalah hasil rapat penyesuaian sekolah tanggal 28 Oktober 2026 dan menjadi konfigurasi baku sistem.

| Aspek | Ketentuan baku |
| --- | --- |
| Tipe soal yang diizinkan | 1) Pilihan Ganda, 2) Pilihan Ganda Kompleks, 3) Benar / Salah. Tipe lain (menjodohkan, uraian, isian) dinonaktifkan dari bank soal agar tidak terpakai tanpa keputusan baru |
| Jumlah soal — Eksata | 25 soal per mata pelajaran per ujian |
| Jumlah soal — Non-eksata | 30 soal per mata pelajaran per ujian |
| Durasi ujian | 60 menit (1 jam) per mata pelajaran, dihitung mundur oleh sistem |
| Media pelaksanaan | Full komputer laboratorium — 25 PC client, bukan HP siswa / BYOD |
| Konsep penguncian | Konsep Exambro diterapkan di PC: browser terkunci (Safe Exam Browser atau Chrome Kiosk), peserta tidak dapat membuka aplikasi / tab lain selama ujian |
| Jalur jaringan | **Hybrid — dua jalur, satu server, satu database.** Jalur Online: domain HTTPS via Cloudflare Tunnel, contoh `https://cbt.bhydev.my.id` (jalur resmi Full Online sesuai permintaan kepala sekolah, untuk akses dan pemantauan dari mana saja). Jalur LAN: IP lokal server, contoh `http://192.168.1.10/garudacbt` (jalur langsung di lab dan fallback darurat bila Online terganggu) |
| Perpindahan jalur | Bila jalur Online putus di tengah ujian, proktor mengalihkan client ke jalur LAN, peserta login ulang, dan melanjutkan dari jawaban terakhir yang tersimpan dalam sisa waktu yang sama. Prosedur ini wajib diuji sebelum hari ujian (lihat Bab 7 dan 12) |
| Pengelompokan mapel | Setiap mata pelajaran wajib memiliki atribut kelompok: Eksata atau Non-eksata. Atribut ini yang menentukan jumlah soal otomatis |

> **Aturan kunci sistem:** Saat guru atau admin membuat ujian, sistem membaca kelompok mapel dan mengunci jumlah soal (25 atau 30) serta durasi (60 menit) secara otomatis. Perubahan angka hanya dapat dilakukan Admin melalui konfigurasi, dan setiap perubahan tercatat.

## 5. Kebutuhan fungsional

Prioritas memakai MoSCoW: M = Must (wajib ada), S = Should (sebaiknya ada), C = Could (boleh ada bila waktu cukup). Semua kebutuhan Must adalah syarat penerimaan.

### 5.1 Data master minimum untuk ujian (FR-01 s.d. FR-05)

| ID | Kebutuhan | Prioritas |
| --- | --- | :---: |
| FR-01 | Import dan kelola siswa, kelas / rombel, dan guru dari file spreadsheet, dengan validasi duplikasi username | M |
| FR-02 | Kelola mata pelajaran dengan atribut kelompok Eksata / Non-eksata dan kurikulum yang berlaku di sekolah | M |
| FR-03 | Kelola tahun pelajaran, ruang ujian, dan sesi ujian | M |
| FR-04 | Generate akun dan kartu peserta ujian per kelas / sesi | M |
| FR-05 | Reset password siswa dan reset status login peserta oleh Proktor | M |

### 5.2 Bank soal tiga tipe (FR-06 s.d. FR-11)

| ID | Kebutuhan | Prioritas |
| --- | --- | :---: |
| FR-06 | Buat, edit, import, dan preview soal Pilihan Ganda dengan satu kunci jawaban | M |
| FR-07 | Buat, edit, import, dan preview soal Pilihan Ganda Kompleks dengan lebih dari satu kunci jawaban | M |
| FR-08 | Buat, edit, import, dan preview soal Benar / Salah per pernyataan | M |
| FR-09 | Dukungan stimulus dan media pada soal: teks bacaan, gambar, dan tabel untuk literasi / numerasi | M |
| FR-10 | Bank soal terpisah per mapel dan per guru, dengan status jumlah soal siap pakai per mapel | S |
| FR-11 | Tipe soal di luar tiga tipe baku tidak muncul pada form pembuatan soal | M |

### 5.3 Jadwal, sesi, dan token (FR-12 s.d. FR-16)

| ID | Kebutuhan | Prioritas |
| --- | --- | :---: |
| FR-12 | Buat jadwal per mapel: sistem otomatis mengisi jumlah soal dari kelompok mapel (25 / 30) dan durasi 60 menit | M |
| FR-13 | Atur sesi, ruang, tanggal, dan jam mulai per jadwal; satu sesi menampung maksimal 25 peserta sesuai kapasitas lab | M |
| FR-14 | Generate token ujian per sesi; token hanya aktif pada jendela waktu sesinya | M |
| FR-15 | Acak urutan soal dan acak urutan jawaban per peserta | M |
| FR-16 | Pengawas dapat memperpanjang waktu peserta tertentu karena gangguan teknis, dengan catatan alasan | S |

### 5.4 Pelaksanaan ujian di PC client (FR-17 s.d. FR-22)

| ID | Kebutuhan | Prioritas |
| --- | --- | :---: |
| FR-17 | Login peserta, input token, dan halaman konfirmasi identitas sebelum timer berjalan | M |
| FR-18 | Timer 60 menit tampil selalu, jawaban tersimpan otomatis (autosave) per soal, dan navigasi / penanda ragu-ragu tersedia | M |
| FR-19 | Ujian terkunci di browser PC: mode kiosk / Safe Exam Browser; deteksi pindah tab atau aplikasi dicatat dan ditampilkan ke Pengawas | M |
| FR-20 | Jika PC client mati atau browser tertutup, peserta dapat login kembali di PC lain dan melanjutkan dari jawaban terakhir yang tersimpan, dalam sisa waktu yang sama | M |
| FR-21 | Tombol selesai dengan konfirmasi; ujian otomatis terkumpul (auto-submit) saat waktu habis | M |
| FR-22 | Dashboard pemantauan Pengawas: status belum mulai, sedang mengerjakan, selesai, dan pelanggaran tab per peserta secara real-time | M |

### 5.5 Hasil, analisis, dan cetak ujian (FR-23 s.d. FR-27)

| ID | Kebutuhan | Prioritas |
| --- | --- | :---: |
| FR-23 | Penilaian otomatis untuk ketiga tipe soal segera setelah ujian terkumpul | M |
| FR-24 | Rekap nilai per kelas, per mapel, dan per sesi; ekspor ke spreadsheet | M |
| FR-25 | Analisis butir soal: tingkat kesukaran dan daya serap per soal per mapel | S |
| FR-26 | Cetak kartu peserta, daftar hadir, dan berita acara per sesi | M |
| FR-27 | Backup database terjadwal dan restore yang teruji; backup wajib sebelum dan sesudah hari ujian | M |

### 5.6 Akses Hybrid — Online dan LAN (FR-28 s.d. FR-32)

| ID | Kebutuhan | Prioritas |
| --- | --- | :---: |
| FR-28 | Aplikasi dapat dibuka dengan benar lewat kedua alamat tanpa mengganti data: domain Online via Cloudflare Tunnel dan IP LAN lokal. Konfigurasi `base_url` dibuat dinamis mengikuti alamat yang dipakai pengakses, tidak dikunci permanen ke satu domain atau satu IP, agar tidak terjadi redirect ke alamat yang salah saat pindah jalur | M |
| FR-29 | Setiap PC client memiliki dua shortcut di desktop: **CBT Online** (domain HTTPS) dan **CBT Lokal (Darurat)** (alamat LAN), keduanya dalam mode browser terkunci yang sama | M |
| FR-30 | Sesi, token, jawaban tersimpan, dan sisa waktu peserta tetap sama di kedua jalur, karena keduanya memakai server dan database yang sama. Pindah jalur di tengah ujian cukup dengan login ulang, tanpa membuat sesi ujian baru | M |
| FR-31 | Dashboard pemantauan dapat diakses kepala sekolah / guru lewat jalur Online dari luar lab, dan oleh proktor lewat jalur LAN di dalam lab, dengan data status peserta yang sama | M |
| FR-32 | Proktor dapat mengalihkan seluruh client dari Online ke LAN secara massal (misalnya lewat Veyon) dan mengumumkan prosedur pindah jalur dengan satu instruksi yang jelas kepada peserta | M |

## 6. Rencana modifikasi Garuda CBT

Prinsip modifikasi: pangkas dari navigasi dan route, bukan menghapus kode secara membabi buta. Modul yang dikeluarkan dinonaktifkan dengan cara yang dapat diaudit dan, bila sekolah berubah keputusan di masa depan, dapat dievaluasi kembali tanpa merusak data ujian.

| Area Garuda CBT | Keputusan | Detail pekerjaan |
| --- | --- | --- |
| Dashboard CBT, Bank Soal, Jadwal, Sesi, Token, Status Ujian, Hasil, Analisis | Pertahankan | Menjadi menu utama. Urutan menu disusun mengikuti alur kerja: Master → Bank Soal → Jadwal → Pelaksanaan → Hasil |
| Data Master | Pertahankan, sederhanakan | Sisakan siswa, guru, kelas, mapel, tahun pelajaran, ruang, sesi. Tambahkan atribut kelompok mapel Eksata / Non-eksata |
| Rapor, Leger Rapor, Pengaturan Rapor | Hapus dari menu, nonaktifkan route | Hapus dari sidebar semua peran, blokir akses URL langsung, hapus dari daftar hak akses peran |
| e-Learning: Materi, Tugas, Nilai Harian | Hapus dari menu, nonaktifkan route | Sama seperti Rapor. Pastikan penghapusan tidak memutus tabel hasil ujian CBT |
| Buku Induk terikat rapor | Nonaktifkan | Data identitas siswa tetap di Data Siswa untuk kebutuhan peserta ujian |
| Tipe soal di luar PG, PG Kompleks, Benar / Salah | Sembunyikan | Hapus opsi dari form dan template import; soal lama bertipe lain tidak ikut dalam penyusunan ujian baru |
| Konfigurasi ujian baku | Tambah | Pengaturan terpusat: Eksata = 25 soal, Non-eksata = 30 soal, durasi = 60 menit. Dipakai otomatis oleh FR-12 |
| Konfigurasi `base_url` untuk Hybrid | Ubah | Jadikan dinamis mengikuti host yang dipakai (domain Online atau IP LAN), jangan dikunci ke satu alamat. Uji login, pemuatan aset, penyimpanan jawaban, dan pengumpulan dari kedua alamat sebelum diterima |
| Deteksi pindah tab | Pertahankan, pertegas | Peristiwa tercatat per peserta dan tampil di dashboard Pengawas (FR-19, FR-22) |
| Cetak ujian | Pertahankan | Kartu peserta, daftar hadir, berita acara, rekap nilai ujian. Template cetak rapor tidak ikut |
| Atribusi Garuda CBT | Wajib dipertahankan | Nama, copyright, dan lisensi MIT asal tetap tampil sesuai syarat lisensi |

> **Aturan pengaman modifikasi:** Sebelum satu baris pun diubah, buat salinan penuh folder aplikasi dan database Garuda CBT bawaan. Setiap tahap pemangkasan diuji ulang dengan skenario ujian lengkap. Aplikasi hasil modifikasi yang bermasalah adalah tanggung jawab pihak yang memodifikasi, sehingga backup versi bawaan tidak boleh dihapus selama masa ujian berlangsung.

## 7. Arsitektur dan deployment laboratorium

Topologi yang disahkan adalah **Hybrid: 1 PC server khusus + 25 PC client, satu server dan satu database yang diakses lewat dua jalur**. PC server tidak dipakai sebagai PC peserta, sehingga satu sesi penuh menampung 25 siswa dengan perbandingan 1 PC untuk 1 siswa.

```text
JALUR A — ONLINE (jalur resmi Full Online, permintaan kepala sekolah)
Peserta / Kepala Sekolah / Guru / Admin
  → internet → Cloudflare Edge → Cloudflare Tunnel (cloudflared)
  → PC Server — contoh alamat: https://cbt.bhydev.my.id

JALUR B — LAN (jalur langsung di lab + fallback darurat)
PC Client 1–25 → switch LAN lab → PC Server langsung, tanpa internet
  → contoh alamat: http://192.168.1.10/garudacbt

                ┌─────────────────────────────────────────┐
                │  PC SERVER KHUSUS (tidak dipakai ujian) │
                │  XAMPP (Apache + MySQL)                 │
                │  Garuda CBT Hasil Modifikasi            │
                │  cloudflared berjalan otomatis          │
                │  IP statis LAN, contoh: 192.168.1.10     │
                │  Folder aplikasi: garudacbt             │
                │  SATU database untuk kedua jalur        │
                └─────────────────────────────────────────┘

PC CLIENT 1–25 (PESERTA)          PC PROKTOR / PENGAWAS
Browser terkunci: Safe Exam       Dashboard pemantauan ujian
Browser / Chrome Kiosk            Veyon untuk memantau,
Dua shortcut di desktop:          mengalihkan jalur, dan
  1. CBT Online                   mengendalikan 25 layar client
  2. CBT Lokal (Darurat)
```

> **Prinsip Hybrid:** Kedua jalur bukan dua sistem. Keduanya membuka aplikasi dan database yang persis sama di PC server yang sama. Yang berbeda hanyalah alamat yang diketik di browser. Karena itu data peserta, token, jawaban, dan nilai tidak pernah terbagi dua.

### 7.1 Spesifikasi dan penyiapan server

| Item | Ketentuan |
| --- | --- |
| Sistem dan stack | Windows + XAMPP, PHP sesuai dukungan Garuda CBT (7.4–8.3). Apache dan MySQL berjalan sebagai layanan otomatis saat PC menyala |
| Alamat IP | IP statis di LAN lab dan tidak berubah selama periode ujian. Alamat contoh pada dokumen ini adalah 192.168.1.10; sesuaikan dengan skema jaringan lab yang sebenarnya dan catat di berita acara teknis |
| Daya dan restart | Nonaktifkan restart otomatis Windows Update pada hari ujian. Server wajib menyala sebelum sesi pertama dan tidak boleh dipakai untuk keperluan lain selama ujian |
| Cloudflare Tunnel | Dipasang di PC server yang sama, diarahkan ke layanan lokal Garuda CBT (`localhost`), dengan hostname domain resmi sekolah, contoh `cbt.bhydev.my.id`. `cloudflared` wajib berjalan otomatis saat PC menyala (sebagai service) dan statusnya diperiksa sebelum sesi pertama. Ini adalah komponen jalur utama Online, bukan lagi komponen opsional |
| `base_url` dinamis | Aplikasi harus bekerja penuh dari domain Online maupun IP LAN tanpa redirect paksa ke alamat satunya. Periksa konfigurasi aplikasi / framework agar URL aset, form login, dan pengumpulan jawaban mengikuti host yang sedang dipakai |
| Keamanan dasar | Hapus / nonaktifkan folder `install` setelah instalasi, ganti password admin bawaan, batasi akses database hanya dari localhost, dan lindungi halaman admin yang kini terekspos internet: password kuat dan pertimbangkan Cloudflare Access untuk admin |

### 7.2 Penyiapan 25 PC client

- **Dua shortcut ujian di desktop setiap PC:** (1) **CBT Online** — membuka domain HTTPS resmi dalam mode kiosk / Safe Exam Browser; (2) **CBT Lokal (Darurat)** — membuka alamat LAN server dalam mode terkunci yang sama. Kedua shortcut diberi nama persis seperti itu agar instruksi proktor tidak ambigu.
- Auto-start browser ujian dan pemantauan massal memakai Veyon dari PC proktor, agar proktor tidak perlu menyalakan dan membuka aplikasi satu per satu di 25 PC, termasuk saat harus mengalihkan jalur secara massal.
- Uji setiap PC dengan akun peserta contoh sebelum hari ujian, dari **kedua** shortcut: login, token, autosave, dan pengumpulan.

### 7.3 Prosedur pindah jalur (failover) di tengah ujian

Prosedur ini adalah konsekuensi utama model Hybrid dan wajib dilatih, bukan hanya ditulis:

1. Proktor memastikan gangguan memang pada jalur Online (internet / tunnel), bukan pada PC server — bila server yang bermasalah, pindah jalur tidak menolong.
2. Proktor mengumumkan satu instruksi yang sama kepada seluruh peserta dan, bila memungkinkan, mengalihkan client secara massal lewat Veyon ke shortcut **CBT Lokal (Darurat)**.
3. Peserta login ulang di alamat LAN dengan akun dan token sesi yang sama. Pindah alamat berarti sesi browser baru, sehingga login ulang memang diperlukan — hal ini harus disampaikan kepada peserta agar tidak dianggap error.
4. Peserta melanjutkan dari jawaban terakhir yang tersimpan (autosave FR-18) dengan sisa waktu yang sama (FR-20, FR-30). Proktor memeriksa dashboard: seluruh peserta kembali berstatus sedang mengerjakan sebelum ujian dilanjutkan.
5. Perpindahan jalur, jam terjadinya, dan penyebabnya dicatat di berita acara sesi tersebut.

## 8. Kebutuhan non-fungsional

| Aspek | Target yang harus dibuktikan saat pengujian |
| --- | --- |
| Kapasitas bersamaan | 25 peserta login, mengerjakan, dan mengumpulkan dalam satu sesi tanpa error dan tanpa kehilangan jawaban |
| Ketahanan jalur (Hybrid) | Bila internet atau Cloudflare Tunnel putus, ujian dialihkan ke jalur LAN sesuai prosedur 7.3 dan tetap selesai pada sesi yang sama. Bila LAN lokal bermasalah tetapi internet sehat, jalur Online tetap dapat dipakai. Kegagalan satu jalur saja tidak boleh membatalkan sesi |
| Konsistensi dua jalur | Login, token, autosave, sisa waktu, dan hasil yang terlihat dari domain Online dan dari IP LAN harus identik, karena bersumber dari satu database |
| Autosave | Jawaban tersimpan di server pada setiap perubahan; pemadaman client dan perpindahan jalur tidak menghapus jawaban yang sudah tersimpan |
| Kecepatan | Perpindahan antarsoal terasa seketika di jaringan LAN; pada jalur Online, halaman soal dengan gambar / stimulus tetap dapat dibaca tanpa menunggu lama. Bila jalur Online terasa lambat di lab, proktor boleh menetapkan jalur LAN sebagai jalur client pada sesi itu dan mencatatnya di berita acara |
| Keamanan sesi | Satu akun peserta hanya aktif di satu PC pada satu waktu; login kedua harus ditolak atau memerlukan reset oleh Proktor |
| Backup | Backup database dapat dibuat dari aplikasi dan hasil restore teruji di lingkungan uji sebelum dipakai di produksi |
| Kemudahan operasi | Proktor dapat menjalankan satu sesi penuh (token, pantau, reset, berita acara) hanya dari panduan satu halaman di Bab 15 |

## 9. Alur ujian end-to-end

1. **Persiapan master:** Admin import siswa, guru, kelas, dan memastikan setiap mapel berlabel Eksata / Non-eksata.
2. **Penyusunan soal:** Guru menyusun bank soal hanya dengan PG, PG Kompleks, dan Benar / Salah, lengkap dengan kunci dan stimulus bila ada.
3. **Penjadwalan:** Admin / Guru membuat jadwal; sistem mengunci jumlah soal (25 / 30) dan durasi 60 menit, lalu menetapkan sesi, ruang, dan peserta.
4. **Persiapan lab:** Proktor menyalakan server, memastikan `cloudflared` aktif dan domain Online dapat dibuka, memastikan 25 client dapat membuka **kedua** alamat (Online dan LAN), dan membuka dashboard pemantauan serta Veyon. Proktor menetapkan dan mengumumkan jalur yang dipakai pada sesi itu.
5. **Pelaksanaan:** Peserta login di browser terkunci lewat jalur yang ditetapkan, memasukkan token sesi, mengerjakan dengan autosave, dan mengumpulkan sebelum atau tepat saat waktu habis. Bila jalur yang dipakai terganggu di tengah sesi, berlaku prosedur pindah jalur pada Bab 7.3.
6. **Penutupan sesi:** Pengawas memeriksa status selesai semua peserta, mencetak daftar hadir dan berita acara.
7. **Hasil:** Guru membuka rekap nilai dan analisis butir soal per mapel; Admin membuat backup database penutup hari itu.

Tidak ada langkah rapor di dalam alur ini. Nilai yang dihasilkan berhenti sebagai rekap hasil ujian dan diekspor bila guru membutuhkannya di luar sistem.

## 10. Penilaian, bobot, dan keputusan terbuka

### 10.1 Aturan penilaian dasar

- **Pilihan Ganda:** benar mendapat poin penuh soal, salah atau kosong mendapat nol.
- **Benar / Salah:** setiap pernyataan dinilai benar / salah sesuai kunci; skor soal dihitung dari pernyataan yang tepat.
- **Pilihan Ganda Kompleks:** aturan skornya mengikuti keputusan sekolah pada Tabel 10.2. Sistem tidak boleh mengunci satu aturan sebelum keputusan itu diambil.
- Nilai akhir ditampilkan dalam skala 0–100 per mata pelajaran pada rekap hasil ujian.

### 10.2 Keputusan yang masih terbuka — wajib diputuskan sekolah

Hasil rapat sudah menetapkan tipe soal, jumlah soal, dan durasi. Dua hal berikut belum diputuskan dan sengaja tidak dianggap final di dalam dokumen ini:

| Keputusan terbuka | Pilihan yang perlu dipilih | Dampak bila belum diputuskan |
| --- | --- | --- |
| D-01. Komposisi per tipe soal dalam 25 soal Eksata dan 30 soal Non-eksata | Berapa soal PG, berapa PG Kompleks, berapa Benar / Salah untuk masing-masing kelompok mapel | Guru tidak dapat menyusun paket ujian yang seragam; validasi jumlah per tipe di sistem belum dapat dikunci |
| D-02. Bobot dan skor PG Kompleks | Semua kunci harus tepat untuk mendapat poin penuh, atau skor parsial per pilihan yang tepat | Mesin penilaian (FR-23) belum dapat difinalkan dan diuji |

> **Contoh ilustrasi saja — bukan keputusan rapat:** Sebagai bahan diskusi, komposisi dapat dicontohkan seperti Eksata 25 soal terbagi 15 PG + 5 PG Kompleks + 5 Benar / Salah, dan Non-eksata 30 soal terbagi 18 PG + 6 PG Kompleks + 6 Benar / Salah. Contoh ini tidak berlaku sampai sekolah menetapkannya secara tertulis, lalu dicatat sebagai pembaruan berikutnya dokumen ini.

### 10.3 Validasi penyusunan ujian

- Sistem menolak menerbitkan jadwal bila jumlah soal terpilih tidak sama dengan ketentuan kelompok mapel (25 / 30).
- Setelah D-01 diputuskan, sistem juga memvalidasi jumlah per tipe soal dan memberi peringatan bila bank soal mapel belum mencukupi.
- Soal tanpa kunci jawaban tidak dapat ikut dalam paket ujian yang diterbitkan.

## 11. Rencana implementasi bertahap

| Fase | Pekerjaan | Hasil yang dapat diperiksa |
| --- | --- | --- |
| Fase 0 | Instalasi Garuda CBT bawaan di PC server + backup penuh | Aplikasi bawaan jalan dan dapat dibuka dari alamat LAN; file backup tersimpan dan tercatat |
| Fase 1 | Pemangkasan modul: Rapor dan e-Learning keluar dari menu dan route | Tidak ada menu / URL Rapor dan e-Learning yang dapat diakses semua peran |
| Fase 2 | Pembatasan tipe soal + konfigurasi baku 25 / 30 soal dan 60 menit | Form soal hanya memuat 3 tipe; jadwal otomatis memakai angka baku |
| Fase 3 | Penyiapan lab Hybrid: IP statis, Cloudflare Tunnel + domain resmi, `base_url` dinamis, 25 client kiosk / SEB dengan dua shortcut (Online dan Lokal Darurat), Veyon | 25 client membuka halaman login dari **kedua** alamat; domain Online dapat dibuka dari luar sekolah (uji dari data seluler, bukan WiFi sekolah); latihan pindah jalur berhasil |
| Fase 4 | Uji coba satu kelas penuh + perbaikan | Berita acara uji coba dan daftar perbaikan yang sudah ditutup |
| Fase 5 | Pelatihan guru dan proktor + serah terima | Panduan Bab 15 teruji dipakai proktor tanpa pendampingan pengembang |

## 12. Pengujian dan kriteria penerimaan

Modifikasi dinyatakan diterima bila semua kriteria berikut lolos. Satu kriteria Must yang gagal berarti belum diterima.

| ID | Skenario uji | Hasil yang diharapkan |
| --- | --- | --- |
| UAT-01 | Buka semua peran dan coba akses URL Rapor / e-Learning secara langsung | Menu tidak ada; URL ditolak server |
| UAT-02 | Buat soal PG, PG Kompleks, Benar / Salah dengan stimulus gambar dan tabel | Ketiga tipe tersimpan, ter-preview, dan tampil benar di client |
| UAT-03 | Buat jadwal mapel Eksata dan Non-eksata | Otomatis 25 soal / 60 menit dan 30 soal / 60 menit |
| UAT-04 | Uji beban 25 peserta bersamaan dalam satu sesi | Semua login, autosave, dan submit berhasil; nilai keluar |
| UAT-05 | Putus internet sekolah di tengah uji coba sesi yang memakai jalur Online, lalu alihkan seluruh client ke jalur LAN sesuai prosedur 7.3 | Semua peserta login ulang di alamat LAN, melanjutkan dari jawaban terakhir dengan sisa waktu yang benar, dan ujian selesai pada sesi yang sama |
| UAT-06 | Matikan satu PC client di tengah ujian, login lagi di PC lain | Jawaban terakhir utuh, sisa waktu berlanjut dengan benar |
| UAT-07 | Pindah tab / aplikasi saat ujian dan cek dashboard Pengawas | Peristiwa tercatat dan terlihat oleh Pengawas |
| UAT-08 | Backup database, restore di lingkungan uji, bandingkan hasil ujian | Data hasil ujian sama setelah restore |
| UAT-09 | Kerjakan ujian yang sama bergantian dari domain Online dan dari IP LAN; bandingkan token, jawaban tersimpan, sisa waktu, dan hasil akhir | Kedua jalur menampilkan dan menyimpan data yang identik; tidak ada redirect paksa ke alamat satunya dan tidak ada aset / halaman yang rusak di salah satu jalur |
| UAT-10 | Buka domain Online dari luar sekolah (data seluler) dan pantau dashboard sebagai kepala sekolah / guru; matikan `cloudflared` sesaat untuk simulasi | Pemantauan dari luar berhasil saat tunnel aktif; saat tunnel mati, jalur LAN di lab tidak ikut terganggu |

## 13. Risiko dan mitigasi

| Risiko | Mitigasi yang wajib disiapkan |
| --- | --- |
| PC server mati / restart di tengah ujian | Server khusus yang tidak dipakai peserta, nonaktifkan restart otomatis, mulai server jauh sebelum sesi, backup sebelum sesi |
| Internet sekolah / Cloudflare Tunnel terputus di tengah ujian | Ini risiko utama model Full Online dan alasan model Hybrid dipilih. Mitigasi: prosedur pindah jalur 7.3, shortcut CBT Lokal (Darurat) di semua client, pengalihan massal lewat Veyon, dan latihan failover pada Fase 3–4. `cloudflared` berjalan sebagai service otomatis dan statusnya dicek sebelum sesi |
| `base_url` terkunci ke satu alamat | Aplikasi me-redirect peserta ke domain saat mereka memakai IP LAN (atau sebaliknya), sehingga pindah jalur gagal. Mitigasi: konfigurasi dinamis (FR-28) dan UAT-09 wajib lolos sebelum hari ujian |
| Peserta kebingungan saat pindah jalur | Mitigasi: nama shortcut yang baku, satu instruksi proktor yang sama untuk seluruh ruangan, penjelasan di awal sesi bahwa login ulang saat pindah jalur adalah prosedur normal, dan pencatatan di berita acara |
| Pemangkasan modul merusak modul ujian | Kerjakan bertahap per Fase 1, uji alur ujian lengkap setiap tahap, dan simpan backup versi bawaan (Bab 6) |
| Halaman admin terekspos lewat tunnel | Karena jalur Online kini adalah jalur resmi, halaman login dan admin dapat dijangkau dari internet. Mitigasi: password admin kuat, folder install dinonaktifkan, akses admin jarak jauh dibatasi (pertimbangkan Cloudflare Access), dan backup database rutin |
| Komposisi soal belum diputuskan (D-01 / D-02) | Putuskan sebelum Fase 2 selesai; catat keputusan sebagai pembaruan berikutnya dokumen ini |
| Listrik / jaringan lab bermasalah saat sesi | Uji 25 PC bersamaan pada Fase 4, siapkan PC cadangan dan prosedur lanjut di PC lain (FR-20) |

## 14. Lisensi dan kepatuhan

Garuda CBT dirilis dengan lisensi MIT: perangkat lunak boleh dipakai, disalin, dimodifikasi, dan didistribusikan, dengan syarat pemberitahuan copyright dan teks lisensi asal tetap disertakan. Di dalam aplikasi, atribusi kepada Garuda CBT wajib dipertahankan dan tidak boleh dihapus sebagai bagian dari modifikasi ini.

Konsekuensi praktisnya: sekolah bebas menyesuaikan aplikasi ini dengan kurikulum dan kebutuhan ujiannya, tanpa biaya lisensi. Namun pengembang asal tidak bertanggung jawab atas masalah yang timbul pada versi yang sudah dimodifikasi pihak lain — karena itu backup, pengujian bertahap, dan dokumentasi perubahan pada Bab 6 bersifat wajib, bukan opsional.

## 15. Checklist operasional hari ujian

- [ ] PC server menyala, Apache dan MySQL berjalan, `cloudflared` aktif sebagai service.
- [ ] Halaman login terbuka dari **kedua** alamat: domain Online dan alamat LAN. Jalur yang dipakai sesi ini ditetapkan dan diumumkan.
- [ ] Backup database sebelum sesi pertama selesai dibuat dan tersimpan.
- [ ] 25 PC client menyala, browser terkunci terbuka, kedua shortcut (CBT Online dan CBT Lokal Darurat) ada di desktop, Veyon menampilkan semua layar.
- [ ] Token sesi hari ini aktif; daftar hadir dan kartu peserta cadangan tercetak.
- [ ] Dashboard pemantauan Pengawas terbuka; prosedur reset peserta, lanjut di PC lain, dan pindah jalur (Bab 7.3) siap dijalankan.
- [ ] Setelah sesi: semua peserta berstatus selesai, berita acara tercetak (termasuk catatan bila terjadi pindah jalur), backup penutup dibuat.

---

**Lampiran konfigurasi baku:** Kelompok mapel Eksata = 25 soal / 60 menit; Non-eksata = 30 soal / 60 menit; tipe soal yang diizinkan = Pilihan Ganda, Pilihan Ganda Kompleks, Benar / Salah; kapasitas sesi = 25 peserta; model akses = Hybrid, satu server dan satu database; alamat peserta Online = domain HTTPS via Cloudflare Tunnel (contoh https://cbt.bhydev.my.id); alamat peserta LAN / darurat = alamat IP lokal PC server (contoh http://192.168.1.10/garudacbt); `base_url` = dinamis, tidak dikunci ke satu alamat.
