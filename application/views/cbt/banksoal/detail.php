<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 17:20
 */
?>

<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-sm-flex justify-content-between mb-2">
                <h1><?= $subjudul ?></h1>
                <a href="<?= base_url('cbtbanksoal') ?>" type="button" class="btn btn-sm btn-danger float-right">
                    <i class="fas fa-arrow-circle-left"></i><span
                            class="d-none d-sm-inline-block ml-1">Kembali</span>
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools">
                        <button type="button" onclick="javascript:window.location.reload()"
                                class="btn btn-sm btn-default">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button id="convert" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> <span
                                    class="d-none d-sm-inline-block ml-1">Download Soal</span>
                        </button>
                        <a href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank) ?>"
                           type="button" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> <span
                                    class="d-none d-sm-inline-block ml-1">Tambah/Edit Soal</span>
                        </a>
                    </div>
                </div>
                <div id="tabel-konten" class="card-body">
                    <div id="alert-download" class="alert alert-success align-content-center d-none" role="alert">
                        <div id="download-area"></div>
                    </div>

                    <?php
                    $soals_pg = [];
                    $soals_pg2 = [];

                    $soals_bs = [];
                    foreach ($soals as $soal) {
                        if ($soal->jenis == "1") {
                            $soals_pg[] = $soal;
                        } elseif ($soal->jenis == "2") {
                            $soals_pg2[] = $soal;

                        } elseif ($soal->jenis == "6") {
                            $soals_bs[] = $soal;
                        }
                    }

                    $badge_success = '<span class="text-lg"><i class="bi-check-circle"></i></span>';
                    $badge_danger = '<span class="text-lg text-danger"><i class="bi-exclamation-circle"></i></span>';

                    $total_pg = count($soals_pg);
                    $total_pg_tampil = isset(array_count_values(array_column($soals_pg, 'tampilkan'))['1']) ? array_count_values(array_column($soals_pg, 'tampilkan'))['1'] : 0;
                    $badge_pg = $total_pg_tampil < $bank->tampil_pg ? $badge_danger : $badge_success;

                    $total_pg2 = count($soals_pg2);
                    $total_pg2_tampil = isset(array_count_values(array_column($soals_pg2, 'tampilkan'))['1']) ? array_count_values(array_column($soals_pg2, 'tampilkan'))['1'] : 0;
                    $badge_pg2 = $total_pg2_tampil < $bank->tampil_kompleks ? $badge_danger : $badge_success;



                    $total_bs = count($soals_bs);
                    $total_bs_tampil = isset(array_count_values(array_column($soals_bs, 'tampilkan'))['1']) ? array_count_values(array_column($soals_bs, 'tampilkan'))['1'] : 0;
                    $badge_bs = $total_bs_tampil < (int) ($bank->tampil_bs ?? 0) ? $badge_danger : $badge_success;

                    $total_soal_tampil = isset(array_count_values(array_column($soals, 'tampilkan'))['1']) ? array_count_values(array_column($soals, 'tampilkan'))['1'] : 0;
                    /* PRD FR-11: hanya PG, PG Kompleks, Benar/Salah yang dihitung. */
                    $total_soal_seharusnya_tampil = $bank->tampil_pg + $bank->tampil_kompleks + (int) ($bank->tampil_bs ?? 0);

                    //echo '<pre>';
                    //var_dump($total_soal_seharusnya_tampil);
                    //var_dump($total_soal_tampil);
                    //echo '</pre>';
                    //
                    $tampil_kurang = ($total_pg_tampil + $total_pg2_tampil + $total_bs_tampil) < $total_soal_seharusnya_tampil;
                    $soal_kurang = $total_pg_tampil <> $bank->tampil_pg
                        || $total_pg2_tampil <> $bank->tampil_kompleks
                        || $total_bs_tampil <> (int) ($bank->tampil_bs ?? 0);
                    $status_soal = $soal_kurang || $tampil_kurang ? 'Belum Selesai' : 'SELESAI';
                    $ket_soal = count($soals) < $total_soal_seharusnya_tampil ? 'pembuatan soal masih kurang'
                        : ($tampil_kurang ? 'soal yang ditampilkan masih kurang' : ($soal_kurang ? 'soal yang ditampilkan tidak sama dengan seharusnya' : 'soal sudah cukup dan siap digunakan'));
                    $bg_color = $total_soal_tampil < $total_soal_seharusnya_tampil ? 'bg-danger' : 'bg-success';

                    $jk = json_decode(json_encode($bank->bank_kelas));
                    $jumlahKelas = json_decode(json_encode(unserialize($jk ?? '')));

                    $kelasbank = '';
                    $no = 1;
                    foreach ($jumlahKelas as $j) {
                        foreach ($kelas as $k) {
                            if ((isset($j->kelas_id) && isset($k->id_kelas)) && $j->kelas_id === $k->id_kelas) {
                                if ($no > 1) {
                                    $kelasbank .= ', ';
                                }
                                $kelasbank .= $k->nama_kelas;
                                $no++;
                            }
                        }
                    }
                    //var_dump($check_soal);
                    ?>

                    <div class="row">
                        <div class="col-lg-6 col-md-8">
                            <ul class="list-group list-group-unbordered">
                                <li class="list-group-item p-1"> Kode Bank Soal
                                    <span class="float-right"><b><?= $bank->bank_kode ?></b></span>
                                </li>
                                <li class="list-group-item p-1"> Mata Pelajaran
                                    <span class="float-right"><b><?= $bank->nama_mapel ?></b></span>
                                </li>
                                <li class="list-group-item p-1"> Guru
                                    <span class="float-right">
											<b><?= $bank->nama_guru ?></b>
										</span>
                                </li>
                                <li class="list-group-item p-1"> Kelas
                                    <span class="float-right"><b><?= $kelasbank ?></b></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <ul class="list-group list-group-unbordered">
                                <li class="list-group-item p-1"> Total Seharusnya
                                    <span class="float-right"><b><?= $total_soal_seharusnya_tampil ?></b></span>
                                </li>
                                <li class="list-group-item p-1"> Total Soal dibuat
                                    <span class="float-right"><b><?= count($soals) ?></b></span>
                                </li>
                                <li class="list-group-item p-1"> Total ditampilkan
                                    <span class="float-right"><b><?= $total_soal_tampil ?></b></span>
                                </li>
                                <li class="list-group-item p-1"> Ket.
                                    <span class="float-right"><b><?= $status_soal ?></b></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="card">
                                <div class="card-body text-center p-2 <?= $bg_color ?>">
                                    <b>Pembuatan Soal</b>
                                    <br>
                                    <span class="text-lg"><b><?= $status_soal ?></b></span>
                                    <br>
                                    <span><?= $ket_soal ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-1">
                            <div class="alert alert-default-info">
                                Jika keteragan pembuatan soal sudah <b>SELESAI</b> tapi tidak muncul di akun siswa, klik
                                tombol
                                <span class="badge badge-btn badge-primary">
                                <i class="fa fa-save"></i> <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                            </span>
                                di bawah
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-default my-shadow mb-4">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#ganda" data-toggle="tab">Pilihan
                                Ganda <?= $badge_pg ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="#kompleks" data-toggle="tab">Pilihan Ganda
                                Kompleks <?= $badge_pg2 ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="#benarsalah" data-toggle="tab">Benar/Salah <?= $badge_bs ?></a></li>

                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <?php
                        $disable_delete = $bank->digunakan > 0 || $total_siswa > 0;
                        $dis = $disable_delete ? 'disabled' : '';
                        ?>
                        <div class="tab-pane table-responsive active" id="ganda">
                            <table class="table table-bordered">
                                <tr class="alert alert-default-dark text-center align-middle">
                                    <th class="border-dark">Jenis Soal</th>
                                    <th class="border-dark" colspan="2">Jumlah Soal</th>
                                    <th class="border-dark">Bobot Nilai</th>
                                    <th class="border-dark">Point Per-nomor</th>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border-dark">Pilihan Ganda</td>
                                    <td class="border-dark">Seharusnya</td>
                                    <td class="text-center border-dark"><?= $bank->tampil_pg ?></td>
                                    <td rowspan="3" class="text-center align-middle border-dark">
                                        <?= $bank->bobot_pg ?>
                                    </td>
                                    <td rowspan="3" class="text-center align-middle border-dark">
                                        <?= $bank->bobot_pg > 0 ? round(($bank->bobot_pg / max(1, $total_pg_tampil, $bank->tampil_pg)), 2) : 0 ?>
                                    </td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border-dark">Telah dibuat</td>
                                    <td class="text-center border-dark"><?= $total_pg ?></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border-dark">Ditampilkan</td>
                                    <td class="text-center border-dark"><?= $total_pg_tampil ?></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border-dark">
                                        Info :
                                        <ul>
                                            <?php if ($total_pg < $bank->tampil_pg) : ?>
                                                <li>
                                                    Soal PILIHAN GANDA masih kurang, klik tombol
                                                    <b>(<i class="fas fa-plus"></i>
                                                        Tambah/Edit Soal)</b> untuk menambahkan.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_pg > 0 && $bank->tampil_pg == '0') : ?>
                                                <li>
                                                    Ada soal PILIHAN GANDA tapi tidak ada yang ditampilkan.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_pg_tampil <> $bank->tampil_pg) : ?>
                                                <li>
                                                    Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($bank->digunakan > 0) : ?>
                                                <li>
                                                    Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_siswa > 0) : ?>
                                                <li>
                                                    Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            <hr>
                            <?php if ($total_pg > 0) : ?>
                                <?= form_open('', array('id' => 'select-pg')) ?>
                                <input type="hidden" name="id_bank" value="<?= $bank->id_bank ?>">
                                <input type="hidden" name="jenis" value="1">
                                <div class="d-sm-flex justify-content-between mb-3">
                                    <div>
                                        <input style="width: 24px; height: 24px" class="check-pg-all m-1" id="all-pg"
                                               type="checkbox" <?=$dis?>>
                                        <label for="all-pg" class="align-middle">Pilih Semua PG</label>
                                    </div>
                                    <div>
                                        <span><b>Jumlah PG terpilih: </b></span>
                                        <span id="total-pg" class="text-lg"></span>

                                        <button type="button" class="btn btn-sm btn-primary ml-3" id="save-pg">
                                            <i class="fa fa-save"></i> <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="rTable" id="table-pg">
                                    <div class="rTableBody">
                                        <?php
                                        foreach ($soals_pg as $s) :
                                            $checked = $s->tampilkan == 1 ? 'checked' : ''; ?>
                                            <div class="rTableRow">
                                                <div class="rTableCell align-top text-left" style="width: 40px;">
                                                    <input style="width: 24px; height: 24px" class="check-pg mt-2"
                                                           id="<?= $s->id_soal ?>" type="checkbox" name="soal[]"
                                                           value="<?= $s->id_soal ?>" <?= $checked ?> <?=$dis?>>
                                                </div>
                                                <div class="rTableCell align-top text-center" style="width: 40px;">
                                                    <p class="mt-2">
                                                        <?= $s->nomor_soal ?>.
                                                    </p>
                                                </div>
                                                <div class="rTableCell align-top">
                                                    <div class="mt-2">
                                                        <?= $s->soal ?>
                                                    </div>
                                                    <br>
                                                    <ul class="list-group list-group-unbordered pl-3"
                                                        style="list-style-type: upper-alpha">
                                                        <li><?= str_replace(['<p>', '</p>'], '', $s->opsi_a) ?></li>
                                                        <li><?= str_replace(['<p>', '</p>'], '', $s->opsi_b) ?></li>
                                                        <li><?= str_replace(['<p>', '</p>'], '', $s->opsi_c) ?></li>
                                                        <?php if ($bank->opsi == '4') : ?>
                                                        <li><?= str_replace(['<p>', '</p>'], '', $s->opsi_d) ?></li>
                                                        <?php endif; ?>
                                                        <?php if ($bank->opsi == '5') : ?>
                                                            <li><?= str_replace(['<p>', '</p>'], '', $s->opsi_d) ?></li>
                                                            <li><?= str_replace(['<p>', '</p>'], '', $s->opsi_e) ?></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <div class="mb-2 mt-2">Jawaban:
                                                        <b><?= strtoupper($s->jawaban) ?></b>
                                                    </div>
                                                </div>
                                                <div class="rTableCell text-right pt-2" style="width: 60px">
                                                    <a href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?no=' . $s->nomor_soal . '&jns=1') ?>"
                                                       type="button" class="btn btn-sm btn-default mb-2">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-default"
                                                            type="button"
                                                            data-idsoal="<?= $s->id_soal ?>"
                                                            data-nomor="<?= $s->nomor_soal ?>"
                                                            data-jenis="1"
                                                            onclick="hapusSoal(this)" <?= $dis ?>>
                                                        <i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?= form_close() ?>
                            <?php else: ?>
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Pilihan Ganda
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane table-responsive" id="kompleks">
                            <table class="table table-bordered">
                                <tr class="alert alert-default-dark text-center align-middle">
                                    <th class="border-dark">Jenis Soal</th>
                                    <th class="border-dark" colspan="2">Jumlah Soal</th>
                                    <th class="border-dark">Bobot Nilai</th>
                                    <th class="border-dark">Point Per-nomor</th>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border-dark">Pil. Ganda Kompleks
                                    </td>
                                    <td class="border-dark">Seharusnya</td>
                                    <td class="text-center border-dark"><?= $bank->tampil_kompleks ?></td>
                                    <td rowspan="3" class="text-center align-middle border-dark">
                                        <?= $bank->bobot_kompleks ?>
                                    </td>
                                    <td rowspan="3" class="text-center align-middle border-dark">
                                        <?= $bank->bobot_kompleks > 0 ? round(($bank->bobot_kompleks / max(1, $total_pg2_tampil, $bank->tampil_kompleks)), 2) : 0 ?>
                                    </td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border-dark">Telah dibuat</td>
                                    <td class="text-center border-dark"><?= $total_pg2 ?></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border-dark">Ditampilkan</td>
                                    <td class="text-center border-dark"><?= $total_pg2_tampil ?></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border-dark">
                                        Info :
                                        <ul>
                                            <?php if ($total_pg2 < $bank->tampil_kompleks) : ?>
                                                <li>
                                                    Soal PILIHAN GANDA KOMPLEKS masih kurang, klik tombol <b>(<i
                                                                class="fas fa-plus"></i> Tambah/Edit
                                                        Soal)</b> untuk menambahkan.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_pg2 > 0 && $bank->tampil_kompleks == '0') : ?>
                                                <li>
                                                    Ada soal PILIHAN GANDA KOMPLEKS tapi tidak ada yang ditampilkan.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_pg2_tampil <> $bank->tampil_kompleks) : ?>
                                                <li>
                                                    Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($bank->digunakan > 0) : ?>
                                                <li>
                                                    Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_siswa > 0) : ?>
                                                <li>
                                                    Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.
                                                </li>
                                            <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            <hr>
                            <?php if ($total_pg2 > 0) : ?>
                                <?= form_open('', array('id' => 'select-pg2')) ?>
                                <input type="hidden" name="id_bank" value="<?= $bank->id_bank ?>">
                                <input type="hidden" name="jenis" value="2">
                                <div class="d-sm-flex justify-content-between mb-3">
                                    <div>
                                        <input style="width: 24px; height: 24px" class="check-pg2-all m-1" id="all-pg2"
                                               type="checkbox" <?=$dis?>>
                                        <label for="all-pg2" class="align-middle">Pilih Semua</label>
                                    </div>
                                    <div>
                                        <span><b>Jumlah Soal terpilih: </b></span>
                                        <span id="total-pg2" class="text-lg"></span>

                                        <button type="button" class="btn btn-sm btn-primary ml-3" id="save-pg2">
                                            <i class="fa fa-save"></i> <span class="d-none d-sm-inline-block ml-1">Simpan Soal Terpilih</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="rTable" id="table-pg2">
                                    <div class="rTableBody">
                                        <?php
                                        foreach ($soals_pg2 as $s) :
                                            $checked = $s->tampilkan == 1 ? 'checked' : '' ?>
                                            <div class="rTableRow">
                                                <div class="rTableCell align-top text-left" style="width: 40px;">
                                                    <input style="width: 24px; height: 24px" class="check-pg2 mt-2"
                                                           id="<?= $s->id_soal ?>" type="checkbox" name="soal[]"
                                                           value="<?= $s->id_soal ?>" <?= $checked ?> <?=$dis?>>
                                                </div>
                                                <div class="rTableCell align-top text-center" style="width: 40px;">
                                                    <p class="mt-2">
                                                        <?= $s->nomor_soal ?>.
                                                    </p>
                                                </div>
                                                <div class="rTableCell align-top">
                                                    <div class="mt-2">
                                                        <?= $s->soal ?>
                                                        <br>
                                                    </div>
                                                    <br>
                                                    <?php
                                                    $opsis = unserialize($s->opsi_a ?? '');
                                                    $jawabs = unserialize($s->jawaban ?? '');
                                                    $jwb_pg2 = '';
                                                    if ($jawabs) {
                                                        $jwb_pg2 = implode(', ', array_filter($jawabs));
                                                    }
                                                    if ($opsis) : ?>
                                                        <ul class="list-group list-group-unbordered pl-3"
                                                            style="list-style-type: upper-alpha">
                                                            <?php for ($i = 97; $i < (97 + count($opsis)); $i++) :
                                                                $abjad = chr($i); ?>
                                                                <li><?=isset($opsis[$abjad]) ? str_replace(['<p>', '</p>'], '', $opsis[$abjad]) : 'null' ?></li>
                                                            <?php endfor; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                    <div class="mb-2 mt-2">Jawaban: <b><?= strtoupper($jwb_pg2) ?></b>
                                                    </div>
                                                </div>
                                                <div class="rTableCell text-right pt-2" style="width: 60px">
                                                    <a href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?no=' . $s->nomor_soal . '&jns=2') ?>"
                                                       type="button" class="btn btn-sm btn-default mb-2">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-default"
                                                            type="button"
                                                            data-idsoal="<?= $s->id_soal ?>"
                                                            data-nomor="<?= $s->nomor_soal ?>"
                                                            data-jenis="2"
                                                            onclick="hapusSoal(this)" <?= $dis ?>>
                                                        <i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?= form_close() ?>
                            <?php else: ?>
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Pilihan Ganda Kompleks
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane table-responsive" id="benarsalah">
                            <table class="table table-bordered">
                                <tr class="alert alert-default-dark text-center align-middle">
                                    <th class="border-dark">Jenis Soal</th>
                                    <th class="border-dark" colspan="2">Jumlah Soal</th>
                                    <th class="border-dark">Bobot Nilai</th>
                                    <th class="border-dark">Point Per-nomor</th>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border-dark">Benar / Salah</td>
                                    <td class="border-dark">Seharusnya</td>
                                    <td class="text-center border-dark"><?= (int) ($bank->tampil_bs ?? 0) ?></td>
                                    <td rowspan="3" class="text-center align-middle border-dark">
                                        <?= (int) ($bank->bobot_bs ?? 0) ?>
                                    </td>
                                    <td rowspan="3" class="text-center align-middle border-dark">
                                        <?= $bank->bobot_bs > 0 ? round(($bank->bobot_bs / max(1, $total_bs_tampil, (int) $bank->tampil_bs)), 2) : 0 ?>
                                    </td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border-dark">Telah dibuat</td>
                                    <td class="text-center border-dark"><?= $total_bs ?></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border-dark">Ditampilkan</td>
                                    <td class="text-center border-dark"><?= $total_bs_tampil ?></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border-dark">
                                        Info :
                                        <ul>
                                            <?php if ($total_bs < (int) ($bank->tampil_bs ?? 0)) : ?>
                                                <li>
                                                    Soal BENAR/SALAH masih kurang, klik tombol <b>(<i
                                                                class="fas fa-plus"></i> Tambah/Edit
                                                        Soal)</b> untuk menambahkan.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_bs > 0 && (int) ($bank->tampil_bs ?? 0) == 0) : ?>
                                                <li>
                                                    Ada soal BENAR/SALAH tapi tidak ada yang ditampilkan.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_bs_tampil <> (int) ($bank->tampil_bs ?? 0)) : ?>
                                                <li>
                                                    Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($bank->digunakan > 0) : ?>
                                                <li>
                                                    Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($total_siswa > 0) : ?>
                                                <li>
                                                    Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            <hr>
                            <?php if ($total_bs > 0) : ?>
                                <?= form_open('', array('id' => 'select-bs')) ?>
                                <input type="hidden" name="id_bank" value="<?= $bank->id_bank ?>">
                                <input type="hidden" name="jenis" value="6">
                                <div class="d-sm-flex justify-content-between mb-3">
                                    <div>
                                        <input style="width: 24px; height: 24px" class="check-bs-all m-1" id="all-bs"
                                               type="checkbox" <?=$dis?>>
                                        <label for="all-bs" class="align-middle">Pilih Semua Benar/Salah</label>
                                    </div>
                                    <div>
                                        <span><b>Jumlah Benar/Salah terpilih: </b></span>
                                        <span id="total-bs" class="text-lg"></span>

                                        <button type="button" class="btn btn-sm btn-primary ml-3" id="save-bs">
                                            <i class="fa fa-save"></i> <span class="d-none d-sm-inline-block ml-1">Simpan Soal Terpilih</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="rTable" id="table-bs">
                                    <div class="rTableBody">
                                        <?php
                                        foreach ($soals_bs as $s) :
                                            $checked = $s->tampilkan == 1 ? 'checked' : ''; ?>
                                            <div class="rTableRow">
                                                <div class="rTableCell align-top text-left" style="width: 40px;">
                                                    <input style="width: 24px; height: 24px" class="check-bs mt-2"
                                                           id="<?= $s->id_soal ?>" type="checkbox" name="soal[]"
                                                           value="<?= $s->id_soal ?>" <?= $checked ?> <?=$dis?>>
                                                </div>
                                                <div class="rTableCell align-top text-center" style="width: 40px;">
                                                    <p class="mt-2">
                                                        <?= $s->nomor_soal ?>.
                                                    </p>
                                                </div>
                                                <div class="rTableCell align-top">
                                                    <div class="mt-2">
                                                        <?= $s->soal ?>
                                                        <br>
                                                    </div>
                                                    <br>
                                                    <div class="mb-2 mt-2">Jawaban:
                                                        <b><?= strtoupper($s->jawaban) === 'B' ? 'BENAR' : 'SALAH' ?></b>
                                                    </div>
                                                </div>
                                                <div class="rTableCell text-right pt-2" style="width: 60px">
                                                    <a href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?no=' . $s->nomor_soal . '&jns=6') ?>"
                                                       type="button" class="btn btn-sm btn-default mb-2">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-default"
                                                            type="button"
                                                            data-idsoal="<?= $s->id_soal ?>"
                                                            data-nomor="<?= $s->nomor_soal ?>"
                                                            data-jenis="6"
                                                            onclick="hapusSoal(this)" <?= $dis ?>>
                                                        <i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <?= form_close() ?>
                            <?php else: ?>
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Benar/Salah
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="temp-list"></div>
            <div id="for-export" class="d-none">
                <p><b>I. Soal Pilihan Ganda</b></p>
                <table class="table-soal"
                       style="width:100%; font-size: 11pt; border: 1px solid black; border-collapse: collapse; border-spacing: 0; page-break-after: always">
                    <thead>
                    <tr style="background-color: lightgrey">
                        <th style="width:60px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">
                            NO
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;">
                            SOAL
                        </th>
                        <th style="width:100px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold">
                            JENIS
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold">
                            OPSI
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold">
                            JAWABAN
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse;  text-align: center; font-weight: bold; padding: 12px">
                            KUNCI
                        </th>
                    </tr>
                    </thead>
                    <?php
                    $rows1 = count($soals_pg) > 50 ? count($soals_pg) : 50;
                    for ($i = 0; $i < $rows1; $i++) :
                        $s = isset($soals_pg[$i]) ? (array)$soals_pg[$i] : [
                            'nomor_soal' => $i + 1,
                            'soal' => '',
                            'opsi_a' => '',
                            'opsi_b' => '',
                            'opsi_c' => '',
                            'opsi_d' => '',
                            'opsi_e' => '',
                            'jawaban' => ''
                        ] ?>
                        <tr>
                            <td rowspan="5" style="border: 1px solid black;vertical-align: top;text-align: center;">
                                <?= $s['nomor_soal'] ?>
                            </td>
                            <td rowspan="5" style="border: 1px solid black; vertical-align: top; padding-left: 6px">
                                <?= $s['soal'] ?>
                            </td>
                            <td rowspan="5" style="border: 1px solid black;vertical-align: top;text-align: center;">
                                1
                            </td>
                            <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">A</td>
                            <td style="border: 1px solid black; vertical-align: top; padding-left: 6px"><?= $s['opsi_a'] ?></td>
                            <td style="border: 1px solid black; vertical-align: top; padding-left: 6px"><?= strtolower($s['jawaban']) == 'a' ? 'v' : '' ?></td>
                        </tr>
                        <?php
                        $post_char = 98;
                        for ($a = 0; $a < 4; $a++) :
                            $abjad = chr($post_char);
                            ?>
                            <tr>
                                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">
                                    <?= strtoupper($abjad) ?>
                                </td>
                                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">
                                    <?= $s['opsi_' . $abjad] ?>
                                </td>
                                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px"><?= strtolower($s['jawaban']) == $abjad ? 'v' : '' ?></td>
                            </tr>
                            <?php $post_char++; endfor; endfor; ?>
                </table>
                <p>&nbsp;</p>
                <p><b>II. Soal Pilihan Ganda Kompleks</b></p>
                <table class="table-soal"
                       style="width:100%; font-size: 11pt; border: 3px solid black; border-collapse: collapse; border-spacing: 0; page-break-after: always">
                    <thead>
                    <tr style="border-bottom: 3px solid black; background-color: lightgrey">
                        <th style="width:40px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">
                            NO
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;">
                            SOAL
                        </th>
                        <th style="width:60px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold; padding: 12px">
                            JENIS
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold; padding: 12px">
                            OPSI
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold; padding: 12px">
                            JAWABAN
                        </th>
                        <th style="border: 1px solid black;border-collapse: collapse;  text-align: center; font-weight: bold; padding: 12px">
                            KUNCI
                        </th>
                    </tr>
                    </thead>
                    <?php
                    $rows2 = count($soals_pg2) > 10 ? count($soals_pg2) : 10;
                    for ($sp = 0; $sp < $rows2; $sp++) :
                        $s = isset($soals_pg2[$sp]) ? $soals_pg2[$sp] : json_decode(json_encode([
                            'jawaban' => serialize([]),
                            'nomor_soal' => $sp + 1,
                            'soal' => '',
                            'opsi_a' => serialize(['a' => '', 'b' => '', 'c' => ''])
                        ]));
                        $opsis = unserialize($s->opsi_a ?? '');
                        $opsis = $opsis ? $opsis : ['a'=>'','b'=>'','c'=>''];
                        $rows = $opsis ? count($opsis) : 3;
                        $jawabs = $opsis ? unserialize($s->jawaban ?? '') : [];
                        $bg = $s->nomor_soal % 2 == 0 ? '#FFFFFF' : '#F2F2F2';
                        //if ($opsis) :
                        ?>
                        <tr style="border-top: 3px solid black;background-color: <?= $bg ?>">
                            <td rowspan="<?= $rows ?>"
                                style="border: 1px solid black;vertical-align: top;text-align: center;">
                                <?= $s->nomor_soal ?>
                            </td>
                            <td rowspan="<?= $rows ?>"
                                style="border: 1px solid black; vertical-align: top; padding-left: 6px">
                                <?= $s->soal ?>
                            </td>
                            <td rowspan="<?= $rows ?>"
                                style="border: 1px solid black;vertical-align: top;text-align: center;">
                                2
                            </td>
                            <td style="border: 1px solid black;vertical-align: top;text-align: center; width: 50px">
                                A
                            </td>
                            <td style="border: 1px solid black;vertical-align: top;padding-left: 6px;">
                                <?= $opsis ? $opsis['a'] : '' ?>
                            </td>
                            <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                                <?= $jawabs && in_array('a', $jawabs) ? 'v' : '' ?>
                            </td>
                        </tr>
                        <?php

                        $post_char = 98;
                        for ($i = 1; $i < count($opsis); $i++) :
                            $abjad = chr($post_char); ?>
                            <tr style="background-color: <?= $bg ?>">
                                <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                                    <?= strtoupper($abjad) ?>
                                </td>
                                <td style="border: 1px solid black;vertical-align: top;padding-left: 6px">
                                    <?= $opsis[$abjad] ?>
                                </td>
                                <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                                    <?= $jawabs && in_array($abjad, $jawabs) ? 'v' : '' ?>
                                </td>
                            </tr>
                            <?php $post_char++; endfor;
                        //endif;
                    endfor; ?>
                </table>
                <p>&nbsp;</p>
            </div>
        </div>
    </section>
</div>

<?= form_open('', array('id' => 'up')) ?>
<?= form_close() ?>

<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
<script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>

<script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>

<script>
    var jmlPgTampil = '<?=$bank->tampil_pg?>';
    var jmlPg2Tampil = '<?=$bank->tampil_kompleks?>';

    var jmlBsTampil = '<?= (int) ($bank->tampil_bs ?? 0) ?>';
    var arrJenis = ['', 'Pilihan Ganda', 'PG Kompleks', '', '', '', 'Benar/Salah'];

    $(document).ready(function () {
        var pathUpload = 'uploads';
        var $imgs = $('img');
        $.each($imgs, function () {
            var curSrc = $(this).attr('src');
            if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
            } else {
                var forReplace = curSrc.split(pathUpload);
                $(this).attr('src', base_url + pathUpload + forReplace[1]);
            }
        });

        $.each($(`video`), function () {
            var curSrc = $(this).attr('src');
            if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
            } else {
                var forReplace = curSrc.split(pathUpload);
                $(this).attr('src', base_url + pathUpload + forReplace[1]);
            }
        });

        $.each($(`audio`), function () {
            var curSrc = $(this).attr('src');
            if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
            } else {
                var forReplace = curSrc.split(pathUpload);
                $(this).attr('src', base_url + pathUpload + forReplace[1]);
            }
        });

        var pgCount = $('#table-pg .check-pg').length;
        var pg2Count = $('#table-pg2 .check-pg2').length;



        var pgUnchecked = [];
        var pg2Unchecked = [];
        var bsUnchecked = [];


        function findUnchecked() {
            pgUnchecked = [];
            pg2Unchecked = [];
            bsUnchecked = [];


            $("#table-pg .check-pg:not(:checked)").each(function () {
                pgUnchecked.push($(this).val());
            });

            $("#table-pg2 .check-pg2:not(:checked)").each(function () {
                pg2Unchecked.push($(this).val());
            });

            $("#table-bs .check-bs:not(:checked)").each(function () {
                bsUnchecked.push($(this).val());
            });


            var pgchecked = $("#table-pg .check-pg:checked").length;
            $('#total-pg').html(`<b>${pgchecked}</b>`);
            $("#all-pg").prop("checked", pgchecked == pgCount);

            var pg2checked = $("#table-pg2 .check-pg2:checked").length;
            $('#total-pg2').html(`<b>${pg2checked}</b>`);
            $("#all-pg2").prop("checked", pg2checked == pg2Count);

            var bsCount = $('#table-bs .check-bs').length;
            var bsChecked = $("#table-bs .check-bs:checked").length;
            $('#total-bs').html(`<b>${bsChecked}</b>`);
            $("#all-bs").prop("checked", bsChecked == bsCount);

        }

        findUnchecked();

        $("#table-pg").on("change", ".check-pg", function () {
            findUnchecked();
        });

        $("#table-pg2").on("change", ".check-pg2", function () {
            findUnchecked();
        });

        $("#table-bs").on("change", ".check-bs", function () {
            findUnchecked();
        });


        $("#all-pg").on("click", function () {
            if (this.checked) {
                $(".check-pg").each(function () {
                    this.checked = true;
                    $("#all-pg").prop("checked", true);
                });
            } else {
                $(".check-pg").each(function () {
                    this.checked = false;
                    $("#all-pg").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $("#all-pg2").on("click", function () {
            if (this.checked) {
                $(".check-pg2").each(function () {
                    this.checked = true;
                    $("#all-pg2").prop("checked", true);
                });
            } else {
                $(".check-pg2").each(function () {
                    this.checked = false;
                    $("#all-pg2").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $("#all-bs").on("click", function () {
            if (this.checked) {
                $(".check-bs").each(function () {
                    this.checked = true;
                    $("#all-bs").prop("checked", true);
                });
            } else {
                $(".check-bs").each(function () {
                    this.checked = false;
                    $("#all-bs").prop("checked", false);
                });
            }
            findUnchecked();

        $('#save-pg').on('click', function (e) {
            var dataPost = $('#select-pg').serialize() + "&uncheck=" + JSON.stringify(pgUnchecked);
            console.log(dataPost);

            var checked = $("#table-pg .check-pg:checked").length;
            if (checked !== parseInt(jmlPgTampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Pilihan Ganda terpilih: ${checked} <br>Seharusnya: ${jmlPgTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        $('#save-pg2').on('click', function (e) {
            var dataPost = $('#select-pg2').serialize() + "&uncheck=" + JSON.stringify(pg2Unchecked);
            console.log(dataPost);

            var checked = $("#table-pg2 .check-pg2:checked").length;
            if (checked !== parseInt(jmlPg2Tampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Ganda Kompleks terpilih: ${checked} <br>Seharusnya: ${jmlPg2Tampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        $('#save-bs').on('click', function (e) {
            var dataPost = $('#select-bs').serialize() + "&uncheck=" + JSON.stringify(bsUnchecked);
            console.log(dataPost);

            var checked = $("#table-bs .check-bs:checked").length;
            if (checked !== parseInt(jmlBsTampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Benar/Salah terpilih: ${checked} <br>Seharusnya: ${jmlBsTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        function saveSoalSelected(dataPost) {
            swal.fire({
                title: "Menyimpan Soal Terpilih",
                text: "Silahkan tunggu....",
                button: false,
                closeOnClickOutside: false,
                closeOnEsc: false,
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            $.ajax({
                url: base_url + "cbtbanksoal/saveSelected",
                type: "POST",
                dataType: "JSON",
                data: dataPost,
                success: function (data) {
                    swal.fire({
                        title: "Sukses",
                        html: data.check+' Soal terpilih berhasil disimpan',
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    }).then(result => {
                        if (result.value) {
                            window.location.reload();
                        }
                    });
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        }

        $("#convert").click(function (e) {
            e.preventDefault();

            var contentDocument = $('#for-export').convertToHtmlFile('detail', '');

            var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
            var converted = htmlDocx.asBlob(content, {
                orientation: 'landscape',
                size: 'A4',
                margins: {top: 700, bottom: 700, left: 1000, right: 1000}
            });
            saveAs(converted, 'Soal <?= $bank->bank_kode ?> <?= $bank->nama_mapel ?> Kls <?= $bank->bank_level ?>.docx');

            var link = document.createElement('a');
            link.href = URL.createObjectURL(converted);
            link.download = 'Soal <?= $bank->nama_mapel ?> Kls <?= $bank->bank_level ?>.docx';
            link.appendChild(
                document.createTextNode('Click here if your download has not started automatically'));
            var downloadArea = document.getElementById('download-area');
            downloadArea.innerHTML = '';
            downloadArea.appendChild(link);

            $('#alert-download').removeClass('d-none');

            $("#alert-download").fadeTo(10000, 500).slideUp(500, function () {
                $("#alert-download").slideUp(500);
            });

        });
    });

    function hapusSoal(btn) {
        const id = $(btn).data('idsoal');
        const no = $(btn).data('nomor');
        const jn = $(btn).data('jenis');

        swal.fire({
            title: "HAPUS ?",
            html: "Soal berikut akan dihapus<br>Nomor: <b>" + no + "</b><br>Jenis: <b>" + arrJenis[jn] + "</b>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    text: "Silahkan tunggu....",
                    button: false,
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });
                $.ajax({
                    url: base_url + "cbtbanksoal/hapussoal",
                    method: "POST",
                    data: $('#up').serialize() + '&soal_id=' + id,
                    success: function (result) {
                        console.log("result", result);
                        var tit = result ? 'BERHASIL' : 'GAGAL';
                        var msg = result ? 'berhasil' : 'gagal';
                        var ic = result ? 'success' : 'error';
                        swal.fire({
                            title: tit,
                            text: "Soal " + msg + " dihapus",
                            icon: ic,
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            }
        })
    }
</script>
