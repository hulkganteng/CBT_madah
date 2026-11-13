-- OPSIONAL: isi pemetaan kelas_awal -> id_kelas untuk periode tujuan sebelum menjalankan INSERT.
-- Skrip tidak mengubah master_siswa.kelas_awal dan memprioritaskan kelas_siswa yang sudah ada.
SET @id_tp = 0;
SET @id_smt = 0;

DROP TEMPORARY TABLE IF EXISTS tmp_kelas_awal_map;
CREATE TEMPORARY TABLE tmp_kelas_awal_map (
  kelas_awal INT NOT NULL PRIMARY KEY,
  id_kelas INT NOT NULL
);

-- Contoh, sesuaikan lalu buka komentar:
-- INSERT INTO tmp_kelas_awal_map (kelas_awal, id_kelas) VALUES (10, 101), (11, 102), (12, 103);

INSERT INTO kelas_siswa (id_tp, id_smt, id_siswa, id_kelas)
SELECT @id_tp, @id_smt, s.id_siswa, m.id_kelas
FROM master_siswa s
JOIN tmp_kelas_awal_map m ON m.kelas_awal = s.kelas_awal
JOIN master_kelas k ON k.id_kelas = m.id_kelas AND k.id_tp = @id_tp AND k.id_smt = @id_smt
LEFT JOIN kelas_siswa ks ON ks.id_tp = @id_tp AND ks.id_smt = @id_smt AND ks.id_siswa = s.id_siswa
WHERE @id_tp > 0 AND @id_smt > 0 AND ks.id_siswa IS NULL;

-- Laporan siswa yang tetap Tanpa Kelas.
SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.kelas_awal, j.nama_jurusan, s.program_mak
FROM master_siswa s
LEFT JOIN master_jurusan j ON j.id_jurusan = s.peminatan_id
LEFT JOIN kelas_siswa ks ON ks.id_tp = @id_tp AND ks.id_smt = @id_smt AND ks.id_siswa = s.id_siswa
WHERE ks.id_siswa IS NULL
ORDER BY s.nama;
