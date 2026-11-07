-- Data dummy khusus development. Diimpor setelah onboarding admin selesai.

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `master_ekstra` (`id_ekstra`, `nama_ekstra`, `kode_ekstra`) VALUES (16,'Pramuka','UMU1');
INSERT INTO `master_ekstra` (`id_ekstra`, `nama_ekstra`, `kode_ekstra`) VALUES (17,'Paskibra','UMU2');
INSERT INTO `master_ekstra` (`id_ekstra`, `nama_ekstra`, `kode_ekstra`) VALUES (18,'Futsal','UMU3');
INSERT INTO `master_ekstra` (`id_ekstra`, `nama_ekstra`, `kode_ekstra`) VALUES (19,'Robotik','UMU4');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (91,'Matematika Umum','UMU-MTK','A','NON_EKSATA',0,0,0,900,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (92,'Bahasa Indonesia Umum','UMU-BIN','A','NON_EKSATA',0,0,0,901,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (93,'Bahasa Inggris Umum','UMU-BIG','A','NON_EKSATA',0,0,0,902,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (94,'IPA Umum','UMU-IPA','A','NON_EKSATA',0,0,0,903,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (95,'IPS Umum','UMU-IPS','A','NON_EKSATA',0,0,0,904,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (96,'PPKn Umum','UMU-PPKN','A','NON_EKSATA',0,0,0,905,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (97,'PAI Umum','UMU-PAI','A','NON_EKSATA',0,0,0,906,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (98,'PJOK Umum','UMU-PJOK','B','NON_EKSATA',0,0,0,907,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (99,'Seni Budaya Umum','UMU-SBD','B','NON_EKSATA',0,0,0,908,1,1,NULL);
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `kelompok_ujian`, `bobot_p`, `bobot_k`, `jenjang`, `urutan`, `status`, `deletable`, `urutan_tampil`) VALUES (100,'Informatika Umum','UMU-INF','B','NON_EKSATA',0,0,0,909,1,1,NULL);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `master_guru` (`id_guru`, `id_user`, `nip`, `nama_guru`, `email`, `kode_guru`, `username`, `password`, `no_ktp`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `agama`, `no_hp`, `alamat_jalan`, `rt_rw`, `dusun`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `kewarganegaraan`, `nuptk`, `jenis_ptk`, `tgs_tambahan`, `status_pegawai`, `status_aktif`, `status_nikah`, `tmt`, `keahlian_isyarat`, `npwp`, `foto`) VALUES (9941,NULL,'UMU00000001','Guru Umum Satu','umum.guru1@umum.local','UMU1','umum.guru1','$2y$10$nh44CjFVJb.hWLShvFClvODkTfSWjldwfhcXMJQ9a8DDyYafJ/wUe',NULL,NULL,NULL,'L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif',NULL,NULL,NULL,NULL,NULL);
INSERT INTO `master_guru` (`id_guru`, `id_user`, `nip`, `nama_guru`, `email`, `kode_guru`, `username`, `password`, `no_ktp`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `agama`, `no_hp`, `alamat_jalan`, `rt_rw`, `dusun`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `kewarganegaraan`, `nuptk`, `jenis_ptk`, `tgs_tambahan`, `status_pegawai`, `status_aktif`, `status_nikah`, `tmt`, `keahlian_isyarat`, `npwp`, `foto`) VALUES (9942,NULL,'UMU00000002','Guru Umum Dua','umum.guru2@umum.local','UMU2','umum.guru2','$2y$10$kAvAUadUlA6x72fDzAN/4.XeIfHIArRDmsm0/VJ7oqwJHcLLgzK.m',NULL,NULL,NULL,'L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif',NULL,NULL,NULL,NULL,NULL);
INSERT INTO `master_guru` (`id_guru`, `id_user`, `nip`, `nama_guru`, `email`, `kode_guru`, `username`, `password`, `no_ktp`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `agama`, `no_hp`, `alamat_jalan`, `rt_rw`, `dusun`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `kewarganegaraan`, `nuptk`, `jenis_ptk`, `tgs_tambahan`, `status_pegawai`, `status_aktif`, `status_nikah`, `tmt`, `keahlian_isyarat`, `npwp`, `foto`) VALUES (9943,NULL,'UMU00000003','Guru Umum Tiga','umum.guru3@umum.local','UMU3','umum.guru3','$2y$10$AcN5Cbia5zsWSBr2mSOjEurTfag5mOb8I64P9xwPjkHWR/VLu67DG',NULL,NULL,NULL,'L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif',NULL,NULL,NULL,NULL,NULL);
INSERT INTO `master_guru` (`id_guru`, `id_user`, `nip`, `nama_guru`, `email`, `kode_guru`, `username`, `password`, `no_ktp`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `agama`, `no_hp`, `alamat_jalan`, `rt_rw`, `dusun`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `kewarganegaraan`, `nuptk`, `jenis_ptk`, `tgs_tambahan`, `status_pegawai`, `status_aktif`, `status_nikah`, `tmt`, `keahlian_isyarat`, `npwp`, `foto`) VALUES (9944,NULL,'UMU00000004','Guru Umum Empat','umum.guru4@umum.local','UMU4','umum.guru4','$2y$10$Yfd9keFwguuguYyl8nOuZevUdJtJGAOxgIR8LBTP7xZdPBOhnRvD6',NULL,NULL,NULL,'L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif',NULL,NULL,NULL,NULL,NULL);
INSERT INTO `master_guru` (`id_guru`, `id_user`, `nip`, `nama_guru`, `email`, `kode_guru`, `username`, `password`, `no_ktp`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `agama`, `no_hp`, `alamat_jalan`, `rt_rw`, `dusun`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `kewarganegaraan`, `nuptk`, `jenis_ptk`, `tgs_tambahan`, `status_pegawai`, `status_aktif`, `status_nikah`, `tmt`, `keahlian_isyarat`, `npwp`, `foto`) VALUES (9945,NULL,'UMU00000005','Guru Umum Lima','umum.guru5@umum.local','UMU5','umum.guru5','$2y$10$MfNp0/uCATuk6fCYYn85tu2Eyu3TGQ3ai7oqnD66kjqu1rGgrucvG',NULL,NULL,NULL,'L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif',NULL,NULL,NULL,NULL,NULL);
INSERT INTO `master_guru` (`id_guru`, `id_user`, `nip`, `nama_guru`, `email`, `kode_guru`, `username`, `password`, `no_ktp`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `agama`, `no_hp`, `alamat_jalan`, `rt_rw`, `dusun`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `kewarganegaraan`, `nuptk`, `jenis_ptk`, `tgs_tambahan`, `status_pegawai`, `status_aktif`, `status_nikah`, `tmt`, `keahlian_isyarat`, `npwp`, `foto`) VALUES (9946,NULL,'UMU00000006','Guru Umum Enam','umum.guru6@umum.local','UMU6','umum.guru6','$2y$10$zEhBePrrXV6Rs/RDPLMpFunXRbTm4qugnExNWw5ZnVvPooMp5KLiG',NULL,NULL,NULL,'L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif',NULL,NULL,NULL,NULL,NULL);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (231,'127.0.0.1','umum.guru1','$2y$10$y5Zt/G4ogx7FbzwCxm/Qpu/tr0a6/3M4fAZzIEQId0PYaw8d6MQda','umum.guru1@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017903,NULL,1,'Guru Umum Satu','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (232,'127.0.0.1','umum.guru2','$2y$10$CJpXxw56iUOlsv9h5snSnekJr0AgPkKYj7BWk/zzabRTMXquQe1Da','umum.guru2@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017903,NULL,1,'Guru Umum Dua','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (233,'127.0.0.1','umum.guru3','$2y$10$uLdVb6HUolchWws/MjxUSewhk4zymmKXJPJsZWFX28nDHCr4BRzsC','umum.guru3@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017903,NULL,1,'Guru Umum Tiga','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (234,'127.0.0.1','umum.guru4','$2y$10$QyVUd8cUAyBmqKry1Xmkx.z1yNcW9YCo3lj87/K1oGGO/agAIt.UK','umum.guru4@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017903,NULL,1,'Guru Umum Empat','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (235,'127.0.0.1','umum.guru5','$2y$10$IPyh5ySFeNT2cATzvFiWUuAZ/09HCG855HpyCbr0Q5l8D7pZeMqvm','umum.guru5@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017903,NULL,1,'Guru Umum Lima','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (236,'127.0.0.1','umum.guru6','$2y$10$tUksOgZEVV11KWWiDfwNMuYy6HHiAj2T.BljrIK9GI8N7/hm83OEW','umum.guru6@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017903,NULL,1,'Guru Umum Enam','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (237,'127.0.0.1','umum.siswa01','$2y$10$4QRRN7/cVB3TTSwAz1tkRuQx0dqd9vddQKGxri3WF.E/I9l/v/xXu','umum.siswa01@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017903,NULL,1,'Siswa Umum 01','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (238,'127.0.0.1','umum.siswa02','$2y$10$9t6xzcAMUtZ8tLbUAhrKj.uQg/JXEc2CoUZIm6OZ98OlpgMsufiZm','umum.siswa02@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 02','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (239,'127.0.0.1','umum.siswa03','$2y$10$tIB5kRuSoxC9B4LC1vrH1eonvojL0tVwoQMA6ypxiCcV5Be/Ejcf2','umum.siswa03@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 03','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (240,'127.0.0.1','umum.siswa04','$2y$10$djz3A9HpJrA.rNTdrnX7z.w2viIMEnDY.0AxWGI5o6o3rUEy6f12i','umum.siswa04@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 04','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (241,'127.0.0.1','umum.siswa05','$2y$10$jLjR2L0sbKEDjqNGhHVWhuLeM6QMzk7MfE1TSz.BqPsEeBUAqrT3G','umum.siswa05@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 05','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (242,'127.0.0.1','umum.siswa06','$2y$10$NGufaEjRARVdKta4qhDD5O8Z9Mkf56qwah5bHBGsCCbXvIy3AJkum','umum.siswa06@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 06','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (243,'127.0.0.1','umum.siswa07','$2y$10$ObKqBJzNcN0PEiVJz93u8O18WMewTmsJZ0p4U0yOU6OPaUXZboAeG','umum.siswa07@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 07','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (244,'127.0.0.1','umum.siswa08','$2y$10$g3OmPUnyzAufI43w0jVFO.Oym8hGS71GnvVI7mpClcU7j9UwtDimy','umum.siswa08@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 08','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (245,'127.0.0.1','umum.siswa09','$2y$10$r.y4h8FPqxfE5RlRP1BTS.07CAcls/eOHr7Wc98e7kBj1EK44PD5y','umum.siswa09@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 09','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (246,'127.0.0.1','umum.siswa10','$2y$10$dIIErXvWapO7tclW17OJv.z8OUAkFEQUZnNe0eHmRRnHgfzfFTdRa','umum.siswa10@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 10','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (247,'127.0.0.1','umum.siswa11','$2y$10$w0HZaALwVTZ/bEaj.1Uf6.O70bsI/lInjocx02sbrofzvXdph.ui6','umum.siswa11@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 11','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (248,'127.0.0.1','umum.siswa12','$2y$10$5fDIDWvr6S.1YqDmktZ.EeedHaUlW.3zMJuw0Apb8TPSCaNBvYku6','umum.siswa12@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 12','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (249,'127.0.0.1','umum.siswa13','$2y$10$Bv6.pBpSF247owLPr0bIBeDpycKvOMAVyFW0PcnDfMcflj2NUGmbu','umum.siswa13@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 13','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (250,'127.0.0.1','umum.siswa14','$2y$10$kbLxqrNa6dYJKNzpCXJPAOCXlqfbhHBWxqrS.QKvG1ummNVzcjXzO','umum.siswa14@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 14','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (251,'127.0.0.1','umum.siswa15','$2y$10$Eg83ozO/rbmbS0ITY3uufuP7UCSXi.Z1bg16GFq4ke9vzcHFXtoze','umum.siswa15@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 15','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (252,'127.0.0.1','umum.siswa16','$2y$10$XdB1bczBijY51xT2aUJgfOEqs72bKImaLcinlJ39s4Q3Q02R1ampW','umum.siswa16@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 16','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (253,'127.0.0.1','umum.siswa17','$2y$10$K4GUYbxMXQAAXrFzJc3x9u8t0/8VNiSd9CBakmlWow1NC4EW.wJ26','umum.siswa17@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017904,NULL,1,'Siswa Umum 17','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (254,'127.0.0.1','umum.siswa18','$2y$10$Rudsc41TASX.mT09.aqZy.M3GQuvTyrhtY7bZsYkYa/.5Q9CjNTL.','umum.siswa18@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 18','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (255,'127.0.0.1','umum.siswa19','$2y$10$PCP8ydNYCv5z41xKP1bf2uUU4bSCZB5f4zymuNPGqC64W6Ihjn8Ry','umum.siswa19@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 19','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (256,'127.0.0.1','umum.siswa20','$2y$10$6LTRNerDFBmTxqarF9v.4u1iu9WY2sDQ2jYWCo1ME6w1vDxT6rbga','umum.siswa20@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 20','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (257,'127.0.0.1','umum.siswa21','$2y$10$xD6xIkDz4kw4OwjCdsJDl.H./7qKOqfekChTPuIVu8Z.iOoebCRz6','umum.siswa21@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 21','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (258,'127.0.0.1','umum.siswa22','$2y$10$8B5besqZHER/MMLQY0AUGubD4qh38hVeBp2Gi6WkRIDc5IiRM/P7e','umum.siswa22@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 22','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (259,'127.0.0.1','umum.siswa23','$2y$10$1h/lssRi4ifQ86VdUS3s2.8D90z/gK/0UhIT5SWCj1vOYgmp/E1OS','umum.siswa23@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 23','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (260,'127.0.0.1','umum.siswa24','$2y$10$.CZy07s2GCEHAhuiy7Qq2upf7z0y7iQ.QDZHZ/poGsBWLMFdFiNyK','umum.siswa24@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 24','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (261,'127.0.0.1','umum.siswa25','$2y$10$pbSoWVmr/b1QvY1EqYgZNeH7wV/kwovGf/yzg0CC8xg5poKOKKvBS','umum.siswa25@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 25','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (262,'127.0.0.1','umum.siswa26','$2y$10$o03F16xdV0KOWSYb6ehv7OoEddad02mm2gaPMxHtw5zjF1KtAG5FO','umum.siswa26@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 26','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (263,'127.0.0.1','umum.siswa27','$2y$10$x3TxXhEiy334dI5qG90lqOVBV9JKa41TgZ7iPw94PT5kFXIaaReym','umum.siswa27@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 27','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (264,'127.0.0.1','umum.siswa28','$2y$10$lfb/HTHBJaVjxXMt2kaeNeBK.vOUwwSypStilOvcYxQwWwe2lTcim','umum.siswa28@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 28','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (265,'127.0.0.1','umum.siswa29','$2y$10$g9Q6XcmkjZVghBfhAMttCOZ1fLXa9jrjhK7KJ7kYeFwDQZ04DhPbK','umum.siswa29@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 29','',NULL,NULL);
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES (266,'127.0.0.1','umum.siswa30','$2y$10$uWt7FLU2pfD95qVODIiVuOhd8YBTRMKjpUPaK/ek0Ovawjyq45hAC','umum.siswa30@umum.local',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1794017905,NULL,1,'Siswa Umum 30','',NULL,NULL);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (231,231,2);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (232,232,2);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (233,233,2);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (234,234,2);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (235,235,2);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (236,236,2);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (237,237,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (238,238,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (239,239,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (240,240,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (241,241,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (242,242,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (243,243,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (244,244,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (245,245,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (246,246,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (247,247,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (248,248,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (249,249,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (250,250,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (251,251,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (252,252,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (253,253,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (254,254,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (255,255,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (256,256,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (257,257,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (258,258,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (259,259,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (260,260,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (261,261,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (262,262,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (263,263,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (264,264,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (265,265,3);
INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES (266,266,3);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (231,'Guru Umum Satu','Guru',2,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (232,'Guru Umum Dua','Guru',2,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (233,'Guru Umum Tiga','Guru',2,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (234,'Guru Umum Empat','Guru',2,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (235,'Guru Umum Lima','Guru',2,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (236,'Guru Umum Enam','Guru',2,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (237,'Siswa Umum 01','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (238,'Siswa Umum 02','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (239,'Siswa Umum 03','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (240,'Siswa Umum 04','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (241,'Siswa Umum 05','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (242,'Siswa Umum 06','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (243,'Siswa Umum 07','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (244,'Siswa Umum 08','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (245,'Siswa Umum 09','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (246,'Siswa Umum 10','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (247,'Siswa Umum 11','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (248,'Siswa Umum 12','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (249,'Siswa Umum 13','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (250,'Siswa Umum 14','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (251,'Siswa Umum 15','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (252,'Siswa Umum 16','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (253,'Siswa Umum 17','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (254,'Siswa Umum 18','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (255,'Siswa Umum 19','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (256,'Siswa Umum 20','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (257,'Siswa Umum 21','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (258,'Siswa Umum 22','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (259,'Siswa Umum 23','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (260,'Siswa Umum 24','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (261,'Siswa Umum 25','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (262,'Siswa Umum 26','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (263,'Siswa Umum 27','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (264,'Siswa Umum 28','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (265,'Siswa Umum 29','Siswa',3,NULL);
INSERT INTO `users_profile` (`id_user`, `nama_lengkap`, `jabatan`, `level_access`, `foto`) VALUES (266,'Siswa Umum 30','Siswa',3,NULL);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `master_kelas` (`id_kelas`, `id_tp`, `id_smt`, `nama_kelas`, `kode_kelas`, `jurusan_id`, `level_id`, `guru_id`, `siswa_id`, `jumlah_siswa`, `set_siswa`) VALUES (9936,5,1,'X UMUM 1','UMU-X-1',5,10,9941,NULL,NULL,'0');
INSERT INTO `master_kelas` (`id_kelas`, `id_tp`, `id_smt`, `nama_kelas`, `kode_kelas`, `jurusan_id`, `level_id`, `guru_id`, `siswa_id`, `jumlah_siswa`, `set_siswa`) VALUES (9937,5,1,'X UMUM 2','UMU-X-2',5,10,9942,NULL,NULL,'0');
INSERT INTO `master_kelas` (`id_kelas`, `id_tp`, `id_smt`, `nama_kelas`, `kode_kelas`, `jurusan_id`, `level_id`, `guru_id`, `siswa_id`, `jumlah_siswa`, `set_siswa`) VALUES (9938,5,1,'XI UMUM 1','UMU-XI-1',5,11,9943,NULL,NULL,'0');
INSERT INTO `master_kelas` (`id_kelas`, `id_tp`, `id_smt`, `nama_kelas`, `kode_kelas`, `jurusan_id`, `level_id`, `guru_id`, `siswa_id`, `jumlah_siswa`, `set_siswa`) VALUES (9939,5,1,'XI UMUM 2','UMU-XI-2',5,11,9944,NULL,NULL,'0');
INSERT INTO `master_kelas` (`id_kelas`, `id_tp`, `id_smt`, `nama_kelas`, `kode_kelas`, `jurusan_id`, `level_id`, `guru_id`, `siswa_id`, `jumlah_siswa`, `set_siswa`) VALUES (9940,5,1,'XII UMUM 1','UMU-XII-1',5,12,9945,NULL,NULL,'0');
INSERT INTO `master_kelas` (`id_kelas`, `id_tp`, `id_smt`, `nama_kelas`, `kode_kelas`, `jurusan_id`, `level_id`, `guru_id`, `siswa_id`, `jumlah_siswa`, `set_siswa`) VALUES (9941,5,1,'XII UMUM 2','UMU-XII-2',5,12,9946,NULL,NULL,'0');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (210,'UMU00000001','UMU-01','Siswa Umum 01','L','umum.siswa01','Siswa#2026',9936,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000001','WNI','UIDUMU00000001');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (211,'UMU00000002','UMU-02','Siswa Umum 02','P','umum.siswa02','Siswa#2026',9936,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000002','WNI','UIDUMU00000002');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (212,'UMU00000003','UMU-03','Siswa Umum 03','L','umum.siswa03','Siswa#2026',9936,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000003','WNI','UIDUMU00000003');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (213,'UMU00000004','UMU-04','Siswa Umum 04','P','umum.siswa04','Siswa#2026',9936,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000004','WNI','UIDUMU00000004');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (214,'UMU00000005','UMU-05','Siswa Umum 05','L','umum.siswa05','Siswa#2026',9936,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000005','WNI','UIDUMU00000005');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (215,'UMU00000006','UMU-06','Siswa Umum 06','P','umum.siswa06','Siswa#2026',9937,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000006','WNI','UIDUMU00000006');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (216,'UMU00000007','UMU-07','Siswa Umum 07','L','umum.siswa07','Siswa#2026',9937,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000007','WNI','UIDUMU00000007');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (217,'UMU00000008','UMU-08','Siswa Umum 08','P','umum.siswa08','Siswa#2026',9937,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000008','WNI','UIDUMU00000008');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (218,'UMU00000009','UMU-09','Siswa Umum 09','L','umum.siswa09','Siswa#2026',9937,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000009','WNI','UIDUMU00000009');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (219,'UMU00000010','UMU-10','Siswa Umum 10','P','umum.siswa10','Siswa#2026',9937,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000010','WNI','UIDUMU00000010');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (220,'UMU00000011','UMU-11','Siswa Umum 11','L','umum.siswa11','Siswa#2026',9938,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000011','WNI','UIDUMU00000011');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (221,'UMU00000012','UMU-12','Siswa Umum 12','P','umum.siswa12','Siswa#2026',9938,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000012','WNI','UIDUMU00000012');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (222,'UMU00000013','UMU-13','Siswa Umum 13','L','umum.siswa13','Siswa#2026',9938,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000013','WNI','UIDUMU00000013');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (223,'UMU00000014','UMU-14','Siswa Umum 14','P','umum.siswa14','Siswa#2026',9938,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000014','WNI','UIDUMU00000014');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (224,'UMU00000015','UMU-15','Siswa Umum 15','L','umum.siswa15','Siswa#2026',9938,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000015','WNI','UIDUMU00000015');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (225,'UMU00000016','UMU-16','Siswa Umum 16','P','umum.siswa16','Siswa#2026',9939,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000016','WNI','UIDUMU00000016');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (226,'UMU00000017','UMU-17','Siswa Umum 17','L','umum.siswa17','Siswa#2026',9939,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000017','WNI','UIDUMU00000017');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (227,'UMU00000018','UMU-18','Siswa Umum 18','P','umum.siswa18','Siswa#2026',9939,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000018','WNI','UIDUMU00000018');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (228,'UMU00000019','UMU-19','Siswa Umum 19','L','umum.siswa19','Siswa#2026',9939,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000019','WNI','UIDUMU00000019');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (229,'UMU00000020','UMU-20','Siswa Umum 20','P','umum.siswa20','Siswa#2026',9939,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000020','WNI','UIDUMU00000020');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (230,'UMU00000021','UMU-21','Siswa Umum 21','L','umum.siswa21','Siswa#2026',9940,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000021','WNI','UIDUMU00000021');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (231,'UMU00000022','UMU-22','Siswa Umum 22','P','umum.siswa22','Siswa#2026',9940,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000022','WNI','UIDUMU00000022');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (232,'UMU00000023','UMU-23','Siswa Umum 23','L','umum.siswa23','Siswa#2026',9940,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000023','WNI','UIDUMU00000023');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (233,'UMU00000024','UMU-24','Siswa Umum 24','P','umum.siswa24','Siswa#2026',9940,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000024','WNI','UIDUMU00000024');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (234,'UMU00000025','UMU-25','Siswa Umum 25','L','umum.siswa25','Siswa#2026',9940,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000025','WNI','UIDUMU00000025');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (235,'UMU00000026','UMU-26','Siswa Umum 26','P','umum.siswa26','Siswa#2026',9941,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000026','WNI','UIDUMU00000026');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (236,'UMU00000027','UMU-27','Siswa Umum 27','L','umum.siswa27','Siswa#2026',9941,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000027','WNI','UIDUMU00000027');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (237,'UMU00000028','UMU-28','Siswa Umum 28','P','umum.siswa28','Siswa#2026',9941,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000028','WNI','UIDUMU00000028');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (238,'UMU00000029','UMU-29','Siswa Umum 29','L','umum.siswa29','Siswa#2026',9941,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000029','WNI','UIDUMU00000029');
INSERT INTO `master_siswa` (`id_siswa`, `nisn`, `nis`, `nama`, `jenis_kelamin`, `username`, `password`, `kelas_awal`, `tahun_masuk`, `sekolah_asal`, `tempat_lahir`, `tanggal_lahir`, `agama`, `hp`, `email`, `foto`, `anak_ke`, `status_keluarga`, `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `kode_pos`, `nama_ayah`, `tgl_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `nohp_ayah`, `alamat_ayah`, `nama_ibu`, `tgl_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `nohp_ibu`, `alamat_ibu`, `nama_wali`, `tgl_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `nohp_wali`, `alamat_wali`, `nik`, `warga_negara`, `uid`) VALUES (239,'UMU00000030','UMU-30','Siswa Umum 30','P','umum.siswa30','Siswa#2026',9941,'2026',NULL,NULL,NULL,NULL,NULL,NULL,'siswa.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'NIKUMU00000030','WNI','UIDUMU00000030');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (1,5,1,210,9936);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (2,5,1,211,9936);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (3,5,1,212,9936);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (4,5,1,213,9936);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (5,5,1,214,9936);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (6,5,1,215,9937);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (7,5,1,216,9937);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (8,5,1,217,9937);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (9,5,1,218,9937);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (10,5,1,219,9937);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (11,5,1,220,9938);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (12,5,1,221,9938);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (13,5,1,222,9938);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (14,5,1,223,9938);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (15,5,1,224,9938);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (16,5,1,225,9939);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (17,5,1,226,9939);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (18,5,1,227,9939);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (19,5,1,228,9939);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (20,5,1,229,9939);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (21,5,1,230,9940);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (22,5,1,231,9940);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (23,5,1,232,9940);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (24,5,1,233,9940);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (25,5,1,234,9940);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (26,5,1,235,9941);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (27,5,1,236,9941);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (28,5,1,237,9941);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (29,5,1,238,9941);
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_siswa`, `id_kelas`) VALUES (30,5,1,239,9941);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `cbt_bank_soal` (`id_bank`, `bank_jenis_id`, `bank_kode`, `bank_level`, `bank_kelas`, `bank_mapel_id`, `bank_jurusan_id`, `bank_guru_id`, `bank_nama`, `kkm`, `jml_soal`, `jml_esai`, `tampil_pg`, `tampil_esai`, `bobot_pg`, `bobot_esai`, `opsi`, `date`, `status`, `soal_agama`, `id_tp`, `id_smt`, `deskripsi`, `jml_kompleks`, `tampil_kompleks`, `tampil_bs`, `bobot_bs`, `bobot_kompleks`, `jml_jodohkan`, `tampil_jodohkan`, `bobot_jodohkan`, `jml_isian`, `tampil_isian`, `bobot_isian`, `status_soal`) VALUES (27,1,'UMU-ADD','SMA','a:6:{i:0;a:1:{s:8:\"kelas_id\";i:9936;}i:1;a:1:{s:8:\"kelas_id\";i:9937;}i:2;a:1:{s:8:\"kelas_id\";i:9938;}i:3;a:1:{s:8:\"kelas_id\";i:9939;}i:4;a:1:{s:8:\"kelas_id\";i:9940;}i:5;a:1:{s:8:\"kelas_id\";i:9941;}}',91,5,9941,'UMUM - Latihan Tambah Soal',75,0,0,0,0,100,0,4,'2026-11-07 02:18:25',1,NULL,5,1,'Bank soal latihan fitur Data Umum',0,0,0,0,0,0,0,0,0,0,0,0);
INSERT INTO `cbt_bank_soal` (`id_bank`, `bank_jenis_id`, `bank_kode`, `bank_level`, `bank_kelas`, `bank_mapel_id`, `bank_jurusan_id`, `bank_guru_id`, `bank_nama`, `kkm`, `jml_soal`, `jml_esai`, `tampil_pg`, `tampil_esai`, `bobot_pg`, `bobot_esai`, `opsi`, `date`, `status`, `soal_agama`, `id_tp`, `id_smt`, `deskripsi`, `jml_kompleks`, `tampil_kompleks`, `tampil_bs`, `bobot_bs`, `bobot_kompleks`, `jml_jodohkan`, `tampil_jodohkan`, `bobot_jodohkan`, `jml_isian`, `tampil_isian`, `bobot_isian`, `status_soal`) VALUES (28,1,'UMU-IMP','SMA','a:6:{i:0;a:1:{s:8:\"kelas_id\";i:9936;}i:1;a:1:{s:8:\"kelas_id\";i:9937;}i:2;a:1:{s:8:\"kelas_id\";i:9938;}i:3;a:1:{s:8:\"kelas_id\";i:9939;}i:4;a:1:{s:8:\"kelas_id\";i:9940;}i:5;a:1:{s:8:\"kelas_id\";i:9941;}}',91,5,9941,'UMUM - Latihan Import Soal',75,0,0,0,0,100,0,4,'2026-11-07 02:18:25',1,NULL,5,1,'Bank soal latihan fitur Data Umum',0,0,0,0,0,0,0,0,0,0,0,0);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

