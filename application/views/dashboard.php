<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper modern-dashboard">
    <section class="content-header dashboard-overview">
        <div class="container-fluid">
            <div class="dashboard-heading">
                <div>
                    <span class="dashboard-kicker">Ringkasan sekolah</span>
                    <h1>Dashboard</h1>
                    <p><?= buat_tanggal(date('D, d M Y')) ?> Â· Data tahun pelajaran aktif</p>
                </div>
                <a class="btn btn-primary dashboard-primary-action" href="<?= base_url('settings') ?>">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
            <div class="dashboard-stats" aria-label="Ringkasan data">
                <?php foreach ($info_box as $info) : ?>
                    <a class="dashboard-stat" href="<?= base_url() . $info->url ?>">
                        <span class="dashboard-stat-label"><?= $info->title; ?></span>
                        <strong><?= $info->total; ?></strong>
                        <span class="dashboard-stat-link">Lihat detail <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php
            $guideCounts = [];
            foreach (array_merge($info_box ?? [], $ujian_box ?? []) as $guideInfo) {
                $guideCounts[strtolower(trim($guideInfo->title))] = (int) $guideInfo->total;
            }
            $guideHas = static function ($keys) use ($guideCounts) {
                foreach ((array) $keys as $key) {
                    if (($guideCounts[strtolower($key)] ?? 0) < 1) {
                        return false;
                    }
                }
                return true;
            };
            $guideSteps = [
                ['key' => 'periode', 'title' => 'Profil dan periode aktif', 'text' => 'Pastikan identitas sekolah, tahun pelajaran, dan semester sudah dipilih.', 'url' => 'settings', 'status' => !empty($tp_active) && !empty($smt_active) ? 'done' : 'check'],
                ['key' => 'master', 'title' => 'Data mapel, guru, siswa, dan rombel', 'text' => 'Data ini menjadi dasar penentuan peserta dan bank soal.', 'url' => 'datasiswa', 'status' => $guideHas(['siswa', 'rombel', 'guru', 'mapel']) ? 'done' : 'check'],
                ['key' => 'struktur', 'title' => 'Jenis ujian, sesi, dan ruang', 'text' => 'Siapkan struktur pelaksanaan sebelum menempatkan peserta.', 'url' => 'cbtruang', 'status' => $guideHas(['ruang ujian', 'sesi']) ? 'done' : 'check'],
                ['key' => 'bank', 'title' => 'Bank soal', 'text' => 'Buat atau periksa soal, kunci, bobot, dan pengacakan.', 'url' => 'cbtbanksoal', 'status' => $guideHas('bank soal') ? 'done' : 'check'],
                ['key' => 'jadwal', 'title' => 'Jadwal ujian', 'text' => 'Pilih bank soal, waktu, token, dan kebijakan SEB sesuai kebutuhan.', 'url' => 'cbtjadwal', 'status' => $guideHas('jadwal') ? 'done' : 'check'],
                ['key' => 'peserta', 'title' => 'Peserta, nomor, pengawas, dan token', 'text' => 'Periksa penempatan siswa dan siapkan informasi yang dibagikan di ruang.', 'url' => 'cbtalokasi', 'status' => 'manual'],
                ['key' => 'cek', 'title' => 'Pemeriksaan akhir dan simulasi', 'text' => 'Uji satu akun siswa, pantau aktivitas, dan cetak dokumen yang diperlukan.', 'url' => 'examactivity', 'status' => 'manual'],
            ];
            ?>
            <section class="admin-first-guide" id="admin-first-exam-guide" aria-labelledby="admin-first-guide-title">
                <div class="admin-first-guide-header">
                    <div>
                        <span class="dashboard-kicker">Pendamping admin baru</span>
                        <h2 id="admin-first-guide-title">Buat ujian pertama dari nol</h2>
                        <p>Ikuti langkah berurutan. Tombol hanya membuka halaman yang sudah ada.</p>
                    </div>
                    <div class="admin-first-guide-actions">
                        <button type="button" class="btn btn-default btn-sm" id="admin-guide-toggle" aria-expanded="true">Ciutkan</button>
                        <button type="button" class="btn btn-link btn-sm" id="admin-guide-dismiss">Sembunyikan</button>
                    </div>
                </div>
                <div class="admin-first-guide-body">
                    <div class="admin-first-guide-progress" aria-live="polite">
                        <div class="d-flex justify-content-between mb-1"><span id="admin-guide-progress-label">Memeriksa langkah...</span><span id="admin-guide-progress-percent">0%</span></div>
                        <div class="progress" aria-hidden="true"><div id="admin-guide-progress-bar" class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="width:0%"></div></div>
                    </div>
                    <div class="admin-first-guide-steps">
                        <?php foreach ($guideSteps as $index => $step) : ?>
                            <article class="admin-guide-step is-<?= $step['status'] ?>" data-guide-step="<?= html_escape($step['key']) ?>" data-guide-status="<?= html_escape($step['status']) ?>">
                                <div class="admin-guide-step-number" aria-hidden="true"><?= $index + 1 ?></div>
                                <div class="admin-guide-step-content">
                                    <div class="admin-guide-step-title-row">
                                        <h3><?= html_escape($step['title']) ?></h3>
                                        <span class="admin-guide-step-status" data-guide-status-label><?= $step['status'] === 'done' ? 'Selesai' : ($step['status'] === 'manual' ? 'Periksa' : 'Perlu perhatian') ?></span>
                                    </div>
                                    <p><?= html_escape($step['text']) ?></p>
                                    <div class="admin-guide-step-footer">
                                        <a class="btn btn-primary btn-sm" href="<?= base_url($step['url']) ?>">Buka <?= html_escape($step['title']) ?> <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
                                        <?php if ($step['status'] === 'manual') : ?>
                                            <label class="admin-guide-check-label"><input type="checkbox" class="admin-guide-manual-check" data-guide-check="<?= html_escape($step['key']) ?>"> Saya sudah memeriksa</label>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="admin-first-guide-next">
                        <span><i class="fas fa-lightbulb mr-1" aria-hidden="true"></i>Mulai dari langkah yang belum selesai.</span>
                        <a id="admin-guide-next" class="btn btn-success btn-sm" href="<?= base_url('settings') ?>">Lanjutkan langkah berikutnya <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
                    </div>
                </div>
            </section>
        </div>
    </section>
    <!-- Main content -->
    <section class="content dashboard-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-success my-shadow">
                        <div class="card-header">
                            <div class="card-title">Aktifitas</div>
                            <div class="card-tools">
                                <button type="button" onclick="hapusLogAktivitas()" class="btn btn-sm">
                                    <i class="fa fa-trash text-white"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="log-list">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-danger my-shadow">
                        <div class="card-header">
                            <h4 class="card-title">Penilaian</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($ujian_box as $info) : ?>
                                    <div class="col-md-4 col-6" style="min-height: 60px">
                                        <a href="<?= base_url() . $info->url ?>">
                                            <div class="info-box border p-1" style="min-height: 60px">
                                                <div class="info-box-content p-1 text-danger">
                                                    <span class="info-box-text"><?= $info->title; ?></span>
                                                    <h5 class="info-box-number m-0"><?= $info->total; ?></h5>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-md-4 col-6" style="min-height: 60px">
                                    <a href="<?= base_url('cbttoken') ?>">
                                        <div class="info-box border p-1" style="min-height: 60px">
                                            <div class="info-box-content p-1 text-danger">
                                                <span class="info-box-text">Token
                                                    <small class="float-right d-none" id="interval">-- : -- : --</small></span>
                                                <h5 class="info-box-number m-0"
                                                    id="token-view"><?= $token->token != null ? $token->token : '- - - - - -' ?></h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-center"><b>PENILAIAN HARI INI</b></h6>
                                </div>
                                <div class="col-12 table-responsive">
                                    <?php
                                    //echo '<pre>';
                                    //var_dump($kelas_ujian);
                                    //echo '</pre>';
                                    $no = 1;
                                    $jadwal_ujian = $jadwals_ujian[date('Y-m-d')] ?? [];
                                    if (count($jadwal_ujian) > 0) : ?>
                                        <table id="tbl-penilaian" class="table table-bordered table-sm">
                                            <tr>
                                                <th class="text-center align-middle">NO</th>
                                                <th class="text-center align-middle">RUANG</th>
                                                <th class="text-center align-middle">SESI</th>
                                                <th class="text-center align-middle">MATA PELAJARAN</th>
                                                <th class="text-center align-middle">PENGAWAS</th>
                                            </tr>
                                            <?php
                                            foreach ($ruangs as $ruang => $sesis) :
                                                foreach ($sesis as $sesi) :
                                                    foreach ($jadwal_ujian as $jadwal) :
                                                        $id_guru = isset($pengawas[$jadwal[0]->id_jadwal])
                                                        && isset($pengawas[$jadwal[0]->id_jadwal][$ruang]) &&
                                                        isset($pengawas[$jadwal[0]->id_jadwal][$ruang][$sesi->sesi_id])
                                                            ? explode(',', $pengawas[$jadwal[0]->id_jadwal][$ruang][$sesi->sesi_id]->id_guru ?? '')
                                                            : [];

                                                        $badge_kelas = '';
                                                        $total_peserta = 0;
                                                        foreach ($jadwal as $jdw) {
                                                            $bank_kelass = $jdw->bank_kelas;
                                                            foreach ($bank_kelass as $bank_kelas) {
                                                                foreach ($jdw->peserta as $peserta) {
                                                                    $cnt = isset($peserta[$ruang]) && isset($peserta[$ruang][$sesi->sesi_id]) ?
                                                                        count($peserta[$ruang][$sesi->sesi_id]) : 0;
                                                                    if ($bank_kelas['kelas_id'] != null && $cnt > 0) {
                                                                        $total_peserta += $cnt;
                                                                        //$badge_kelas .= ' <span class="badge badge-info">' . $kelases[$bank_kelas['kelas_id']] . ' ' . $cnt . ' siswa</span>';
                                                                        $nama_kls = isset($kelases[$bank_kelas['kelas_id']]) ? $kelases[$bank_kelas['kelas_id']] : '- -';
                                                                        $badge_kelas .= ' <span class="badge badge-info">' . $nama_kls . ' ' . $cnt . ' siswa</span>';
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($total_peserta > 0) :
                                                            ?>
                                                            <tr>
                                                                <td class="text-center align-middle"><?= $no ?></td>
                                                                <td class="text-center align-middle"><?= $sesi->nama_ruang ?></td>
                                                                <td class="text-center align-middle"><?= $sesi->nama_sesi ?></td>
                                                                <td class="text-center align-middle"><?= $jadwal[0]->kode ?></td>
                                                                <td class="align-middle crop-text-table">
                                                                    <?php foreach ($id_guru as $ig) {
                                                                        echo isset($gurus[$ig]) ? '<p class="p-0 m-0">' . $gurus[$ig] . '</p>' : '';
                                                                    } ?>
                                                                </td>
                                                            </tr>
                                                        <?php endif; endforeach; endforeach;
                                                $no++; endforeach; ?>
                                        </table>
                                    <?php else: ?>
                                        <table class="w-100 table-bordered">
                                            <tr>
                                                <td class="text-center">Tidak ada jadwal penilaian</td>
                                            </tr>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-light my-shadow mb-3">
                        <div class="card-header"><b>INFO/PENGUMUMAN</b></div>
                        <div class="card-body">
                            <div class="konten-pengumuman">
                                <div id="pengumuman">
                                </div>
                                <p id="loading-post" class="text-center d-none">
                                    <br/><i class="fa fa-spin fa-circle-o-notch"></i> Loading....
                                </p>
                                <div id="loadmore-post"
                                     onclick="getPosts()"
                                     class="text-center mt-4 loadmore d-none">
                                    <div class="btn btn-default">Muat Pengumuman lainnya ...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>

<div class="modal fade" id="komentarModal" tabindex="-1" role="dialog" aria-labelledby="komentarLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="komentarLabel">Tulis Komentar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img class="img-fluid img-circle img-sm" src="<?= base_url('assets/img/siswa.png') ?>" alt="Alt Text">
                <div class="img-push">
                    <?= form_open('create', array('id' => 'komentar')) ?>
                    <input type="hidden" id="id-post" name="id_post" value="">
                    <div class="input-group">
                        <input type="text" name="text" placeholder="Tulis komentar ..."
                               class="form-control form-control-sm" required>
                        <span class="input-group-append">
                                <button type="submit" class="btn btn-success btn-sm">Komentari</button>
                            </span>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="balasanModal" tabindex="-1" role="dialog" aria-labelledby="balasanLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="balasanLabel">Tulis Balasan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img class="img-fluid img-circle img-sm" src="<?= base_url('assets/img/siswa.png') ?>" alt="Alt Text">
                <div class="img-push">
                    <?= form_open('create', array('id' => 'balasan')) ?>
                    <input type="hidden" id="id-comment" name="id_comment" value="">
                    <div class="input-group">
                        <input type="text" name="text" placeholder="Tulis balasan ...."
                               class="form-control form-control-sm" required>
                        <span class="input-group-append">
                                <button type="submit" class="btn btn-success btn-sm">Balas</button>
                            </span>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    adaJadwalUjian = '<?=count($ada_ujian)?>';
    localStorage.setItem('ada_jadwal_ujian', adaJadwalUjian);
</script>
<script src="<?= base_url() ?>/assets/app/js/jquery.rowspanizer.js"></script>
<script src="<?= base_url() ?>/assets/app/js/dashboard.js"></script>
<script src="<?= base_url() ?>/assets/app/js/admin-first-exam-guide.js"></script>
