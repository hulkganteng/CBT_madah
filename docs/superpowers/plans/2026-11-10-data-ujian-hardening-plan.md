# Hardening Data Ujian

## Context

Audit Data Ujian menemukan enam kelompok masalah: otorisasi resource berbasis ID belum membatasi guru pada bank soal miliknya, operasi hapus masih memakai GET, validasi Jenis/Sesi/Ruang hanya mengandalkan browser, beberapa penulisan massal tidak transaksional, tombol submit dapat terkunci atau dikirim ganda, dan form memiliki error-state serta relasi label yang tidak lengkap. Target perubahan adalah menutup seluruh temuan tanpa refactor besar modul CBT dan tanpa mengubah mesin pengerjaan, penilaian, atau SEB.

## Intended outcome

- Admin tetap dapat mengelola seluruh Data Ujian.
- Guru hanya dapat membaca atau mengubah bank soal miliknya dan jadwal yang menggunakan bank tersebut.
- Resource dengan `bank_guru_id = NULL` hanya dapat dikelola admin.
- Operasi destruktif memakai POST + CSRF dan tidak dapat menghapus data ujian yang sudah digunakan.
- Tambah/edit Jenis, Sesi, dan Ruang divalidasi identik di server serta dilindungi constraint unik database.
- Operasi massal bersifat all-or-nothing.
- Form tidak dapat dikirim ganda, tidak kehilangan konteks saat gagal, dan dapat dioperasikan pembaca layar dengan lebih baik.

## Approach

### Phase 0 — Characterization and migration preflight

1. Tambahkan test karakterisasi sebelum mengubah controller untuk mengunci perilaku akses admin, guru pemilik, guru lain, dan resource tanpa pemilik.
2. Jalankan query preflight pada development dan production untuk mendeteksi kode kosong/duplikat secara case-insensitive pada `cbt_jenis.kode_jenis`, `cbt_sesi.kode_sesi`, dan `cbt_ruang.kode_ruang`.
3. Jika ditemukan data lama tidak valid, hentikan migrasi dan keluarkan daftar tabel/kode untuk dibersihkan manual. Jangan mengubah atau menggabungkan data lama otomatis.

### Phase 1 — Tracer bullet otorisasi dan lifecycle aman

1. Mulai dari satu vertical slice `Cbtbanksoal::deleteBank`: ubah request menjadi POST, ambil resource melalui `Cbt_model::getDataBankById`, lalu izinkan admin atau guru yang `id_guru`-nya sama dengan `bank_guru_id`.
2. Gunakan guard privat kecil di masing-masing controller untuk invariant kepemilikan:
   - `Cbtbanksoal`: admin bebas; guru wajib memiliki `bank_guru_id`; `NULL` ditolak untuk guru.
   - `Cbtjadwal`: turunkan pemilik dari join `cbt_jadwal.id_bank -> cbt_bank_soal.bank_guru_id` yang sudah tersedia di `getJadwalById`.
3. Setelah tracer bullet lolos test, terapkan guard pada seluruh endpoint bank/jadwal berbasis ID yang membaca atau mengubah detail, soal, impor/ekspor, salin, edit, arsip, dan hapus. Inventaris endpoint dilakukan eksplisit agar tidak ada jalur direct-ID yang tertinggal.
4. Hapus permanen hanya diizinkan untuk data yang benar-benar belum digunakan:
   - Bank yang sudah terhubung ke jadwal ditolak dari delete dan respons mengembalikan alasan serta `can_archive`.
   - Jadwal yang sudah mempunyai attempt/event, durasi, jawaban, nilai, atau rekap ditolak dari delete.
   - UI menawarkan aksi arsip/nonaktif terpisah; aksi tersebut hanya mengubah `status = 0` melalui POST.
5. Hapus resource yang belum digunakan dan seluruh cleanup terkait dijalankan dalam satu transaksi. Kegagalan satu query me-rollback semuanya dan tidak boleh mengembalikan sukses.
6. Pertahankan nama route bila memungkinkan agar perubahan caller kecil, tetapi endpoint hapus/arsip harus menolak GET dengan status 405/JSON yang jelas.

### Phase 2 — Validasi server dan constraint unik

1. Pada `Cbtjenis`, `Cbtsesi`, dan `Cbtruang`, validasi tambah maupun edit sebelum query database:
   - nama/kode wajib setelah `trim`;
   - kode dinormalisasi `strtoupper`;
   - panjang mengikuti kolom (`kode_jenis`/`kode_sesi`/`kode_ruang` maksimal 10, nama maksimal 50);
   - kode unik secara case-insensitive, dengan ID saat ini dikecualikan pada edit;
   - sesi memerlukan format waktu valid dan `waktu_akhir > waktu_mulai`.
2. Kembalikan kontrak JSON konsisten: `status`, `message`, dan `errors` per field; gunakan HTTP 422 untuk validasi dan 409 untuk konflik unik.
3. Tambahkan migration `20261110000100_exam_master_unique_codes.php` yang:
   - mengulang preflight dan berhenti dengan pesan actionable bila data lama bermasalah;
   - menambahkan unique index pada tiga kolom kode;
   - memiliki `down()` yang hanya menghapus index.
4. Naikkan `migration_version`, lalu sinkronkan `assets/app/db/master.sql` dan `assets/app/db/database.json` agar installer dan jalur update membawa constraint yang sama.

### Phase 3 — Transaksi operasi massal

1. Terapkan pola validate-all-before-write pada:
   - `Cbtnomorpeserta::saveNomor`;
   - `Cbtpengawas::savePengawas`;
   - `Cbtalokasi::saveAlokasi`;
   - `Cbtsesisiswa::editsesisiswa` dan `editsesikelas`.
2. Validasi seluruh ID referensi, bentuk array, nilai wajib, dan konflik sebelum `trans_begin`/`trans_start`.
3. Jalankan loop/batch write dalam transaksi InnoDB yang sudah dipakai seluruh tabel terkait. Bila satu write gagal, rollback semua dan kembalikan baris/field penyebab tanpa menyisakan state parsial.
4. Jangan membuat service/repository baru; pertahankan query di layer yang sekarang kecuali satu invariant memang dipakai lintas-controller.

### Phase 4 — Submit state dan error recovery

1. Perbarui handler AJAX Jenis/Sesi/Ruang dan form Data Ujian lain yang disentuh:
   - disable tombol saat request dimulai;
   - cegah submit kedua;
   - pertahankan label aksi dan tampilkan indikator sibuk tanpa layout shift;
   - restore tombol pada `complete` untuk sukses maupun gagal.
2. Tutup modal hanya saat `data.status === true`.
3. Saat 422/409/500, pertahankan modal dan input, tampilkan error per field serta ringkasan `role="alert"`; pesan jaringan menyediakan aksi coba lagi.
4. Perbarui caller hapus bank/jadwal menjadi POST ber-CSRF dan tampilkan pilihan arsip ketika server mengembalikan `can_archive`.

### Phase 5 — Accessibility and DOM correctness

1. Tambahkan `id` yang cocok untuk setiap `label for` pada modal Jenis/Sesi/Ruang, termasuk form edit.
2. Ubah ID ganda `durasi-ujian` pada form jadwal menjadi ID unik untuk durasi total dan durasi minimal, lalu perbarui selector JavaScript terkait.
3. Hubungkan error field dengan `aria-invalid` dan `aria-describedby`; ringkasan async memakai live region yang sesuai.
4. Pertahankan komponen Bootstrap/DataTables yang ada; tidak melakukan redesign visual atau mengganti library.

### Phase 6 — Verification and rollout

1. Jalankan seluruh regression test CBT dan test baru pada database development dengan fixture minimal: dua guru, dua bank, jadwal belum dipakai, jadwal sudah dipakai, serta resource tanpa pemilik.
2. Uji matriks otorisasi untuk setiap endpoint direct-ID: admin=allow, owner=allow, other guru=403, null owner=403 untuk guru, unauthenticated=redirect/401.
3. Uji fault injection pada tiap operasi massal: gagalkan write kedua dan buktikan state database sama dengan sebelum request.
4. Jalankan smoke test seluruh 10 menu Data Ujian, endpoint JSON pembacaan, tambah/edit valid, duplikat, pengarsipan, dan delete resource belum digunakan.
5. Sebelum production: pastikan tidak ada ujian aktif, buat backup database, jalankan preflight, terapkan migration, smoke test admin/guru, lalu buka kembali akses.

## Key decisions

- Seluruh enam temuan diselesaikan dalam satu rencana bertahap; keamanan dan integritas mendahului UX.
- Admin memiliki akses global; guru dibatasi oleh `bank_guru_id`, termasuk kepemilikan jadwal yang diturunkan melalui bank.
- `bank_guru_id = NULL` bersifat admin-only; tidak ada backfill pemilik otomatis.
- Data yang sudah digunakan diarsipkan dengan `status = 0`, bukan dihapus. Data belum digunakan boleh dihapus secara transaksional.
- Kode Jenis/Sesi/Ruang dinormalisasi uppercase dan unik case-insensitive; data lama tidak dibersihkan otomatis.
- Operasi massal harus all-or-nothing.
- Tidak menambah dependency, tabel arsip, soft-delete generik, service layer baru, atau refactor controller besar.
- Unique index tetap diperlukan untuk menutup race condition yang tidak dapat dicegah validasi aplikasi saja.

## Files to modify

### Authorization and lifecycle

- `application/controllers/Cbtbanksoal.php`: guard kepemilikan semua endpoint direct-ID, POST delete/archive, transaksi delete.
- `application/controllers/Cbtjadwal.php`: guard kepemilikan turunan, POST delete/archive, pemeriksaan seluruh bukti pemakaian, transaksi delete.
- `application/models/Cbt_model.php`: gunakan/persempit query resource existing agar selalu membawa `bank_guru_id`; tambah query pemeriksaan pemakaian hanya bila helper existing belum mencakup seluruh tabel.
- `application/views/cbt/banksoal/data.php`: caller POST, respons forbidden/archive, submit state.
- `application/views/cbt/jadwal/data.php`: caller POST, respons forbidden/archive, submit state.

### Validation and schema

- `application/controllers/Cbtjenis.php`, `Cbtsesi.php`, `Cbtruang.php`: validasi tambah/edit dan kontrak error JSON.
- `application/migrations/20261110000100_exam_master_unique_codes.php`: preflight dan unique indexes.
- `application/config/migration.php`: versi migration terbaru.
- `assets/app/db/master.sql`, `assets/app/db/database.json`: sinkronisasi schema installer/update.

### Transactional writes

- `application/controllers/Cbtnomorpeserta.php`
- `application/controllers/Cbtpengawas.php`
- `application/controllers/Cbtalokasi.php`
- `application/controllers/Cbtsesisiswa.php`

### UI and accessibility

- `assets/app/js/cbt/jenis/crud.js`, `assets/app/js/cbt/sesi/crud.js`, `assets/app/js/cbt/ruang/crud.js`: pending, restore, dan inline errors.
- `application/views/cbt/jenis/data.php`, `application/views/cbt/sesi/data.php`, `application/views/cbt/ruang/data.php`: ID/label dan error containers.
- `application/views/cbt/jadwal/add.php` dan selector jadwal terkait: ID durasi unik dan submit/error state.
- View inline-JavaScript untuk nomor peserta, pengawas, alokasi, dan ruang/sesi hanya bila perlu menampilkan respons transaksi yang baru.

### Tests

- `tests/ExamDataAuthorizationTest.php`: matriks role/ownership dan GET rejection.
- `tests/ExamMasterValidationTest.php`: required, uppercase, duplicate, time ordering, serta migration preflight.
- `tests/ExamBulkTransactionTest.php`: rollback operasi massal.
- Test CBT existing diperluas bila fixture/pola yang dibutuhkan sudah tersedia, untuk menghindari duplikasi bootstrap.

## Out of scope

- Mesin pengerjaan siswa, timer, randomisasi, penyimpanan jawaban, penilaian, rekap kalkulasi, dan SEB: tidak diperlukan untuk menutup enam temuan admin.
- Pembersihan atau penggabungan data kode lama: harus menjadi keputusan operator berdasarkan laporan preflight.
- Backfill `bank_guru_id`: kepemilikan tidak boleh ditebak.
- Riwayat arsip, tabel audit baru, recycle bin, dan restore UI: `status = 0` memenuhi kebutuhan saat ini.
- Redesign Data Ujian, penggantian Bootstrap/DataTables/jQuery, atau deobfuscation seluruh controller: blast radius tidak sebanding dengan perbaikan.
- Perubahan role selain admin/guru: kebijakan yang disepakati hanya menyentuh dua role tersebut.

## Assumptions and evidence

- **Verified:** CSRF global aktif di `application/config/config.php`; endpoint tulis POST akan memperoleh proteksi framework yang sudah ada.
- **Verified:** `Cbt_model::getJadwalById` sudah mengembalikan `bank_guru_id` melalui join jadwal → bank, sehingga kepemilikan jadwal tidak memerlukan kolom baru.
- **Verified:** tabel operasi massal yang diperiksa (`cbt_bank_soal`, `cbt_jadwal`, `cbt_kelas_ruang`, `cbt_nomor_peserta`, `cbt_pengawas`, `cbt_sesi_siswa`) memakai InnoDB pada production saat plan dibuat.
- **Verified:** preflight awal pada database development dan production tidak menemukan kode Jenis/Sesi/Ruang kosong atau duplikat case-insensitive. Migration tetap wajib mengulang pemeriksaan karena keadaan data dapat berubah.
- **Unverified until implementation:** `status = 0` tidak menampilkan bank/jadwal kepada siswa pada semua jalur. Ini adalah STOP condition, bukan izin untuk berasumsi.
- **Unverified until rollout:** tidak ada ujian aktif saat deploy dan backup production valid. Keduanya wajib dibuktikan sebelum migration production.

## Verification

- `php -l` pada seluruh controller, model, view PHP, dan migration yang berubah — expected: semua `No syntax errors detected`.
- `node --check` pada seluruh JavaScript CBT yang berubah — expected: exit code 0.
- `vendor\bin\phpunit tests\ExamDataAuthorizationTest.php tests\ExamMasterValidationTest.php tests\ExamBulkTransactionTest.php` — expected: seluruh test lulus.
- `vendor\bin\phpunit tests\ExamLogicIntegrityTest.php tests\QuestionImportTest.php tests\ExcelQuestionTemplateTest.php tests\ExcelQuestionDraftImporterTest.php tests\Phase1SubmitTest.php tests\Phase1SecurityTest.php tests\ImportSafetyTest.php` — expected: tidak ada regresi.
- Query duplicate preflight pada development dan production — expected: nol kode kosong/duplikat sebelum migration.
- PowerShell: `$env:CI_ENV='development'; php index.php migrate CreateMigration` — expected: migration version `20261110000100`, tiga unique index tersedia, production belum berubah.
- Smoke HTTP admin untuk 10 halaman Data Ujian dan endpoint JSON — expected: HTTP 200 tanpa PHP error.
- Smoke role guru — expected: resource sendiri berhasil, resource guru lain dan `NULL` menghasilkan 403 tanpa perubahan database.
- Snapshot count/checksum sebelum dan sesudah negative tests — expected: tidak berubah.
- Fault injection write kedua pada setiap operasi massal — expected: rollback penuh.

## Rollout and rollback

- Rollout production hanya setelah backup dan konfirmasi tidak ada ujian aktif.
- Deploy kode dan migration sebagai satu unit agar caller POST tidak mendahului endpoint baru.
- Bila smoke test gagal sebelum traffic dibuka, rollback kode dan jalankan `down()` migration untuk melepas unique index; restore database hanya diperlukan bila ada write setelah rollout.
- Arsip menggunakan status existing sehingga rollback kode tidak memerlukan transformasi data.

## STOP conditions

- Preflight menemukan kode kosong/duplikat: berhenti, laporkan daftar konflik, jangan menormalkan otomatis.
- Query resource existing tidak dapat menentukan `bank_guru_id`: berhenti; jangan menebak pemilik atau membuka akses guru.
- Ditemukan endpoint direct-ID lain yang dapat memodifikasi bank/jadwal tanpa melewati controller yang direncanakan: tambahkan ke inventory dan test sebelum lanjut.
- Nilai `status = 0` ternyata bukan semantik nonaktif pada bank/jadwal di alur siswa: berhenti dan konfirmasi lifecycle alternatif.
- Ada tabel dalam operasi massal yang bukan InnoDB pada environment target: berhenti karena rollback tidak dapat dijamin.
- Ada ujian aktif atau backup production gagal: jangan jalankan migration/deploy.

## Implementation notes

Catat setiap deviasi di `2026-11-10-data-ujian-hardening-implementation-notes.md` pada bagian `## Deviations`: tulis apa yang direncanakan, apa yang ditemukan di kode, dan opsi konservatif yang dipilih. Deviasi yang bukan STOP condition tidak menghentikan pekerjaan.

## Review notes — 2026-11-10

Initial triage: Completeness 5/5, Feasibility 5/5, Scope 5/5, Testability 4/5, Risk 5/5, Assumptions 4/5.

Resolution: sintaks migration development dibuat eksplisit; asumsi load-bearing dipisahkan menjadi verified/unverified dengan STOP condition. Final triage: seluruh enam dimensi 5/5.
