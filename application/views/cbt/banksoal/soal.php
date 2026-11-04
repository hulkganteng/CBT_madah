<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 14/07/20
 * Time: 17:46
 */

$dataFileAttach = [];
$att = @unserialize($soal->file ?? '');
if ($att !== false) {
    $dataFileAttach = unserialize($soal->file ?? '');
}

$pg_belum_komplit = [];
$pg2_belum_komplit = [];
$jodohkan_belum_komplit = [];
$isian_belum_komplit = [];
$essai_belum_komplit = [];
$bs_belum_komplit = [];

foreach ($soal_belum_komplit as $key => $value) {
    if ($value->jenis == '1') {
        array_push($pg_belum_komplit, $value->nomor_soal);
    } elseif ($value->jenis == '2') {
        array_push($pg2_belum_komplit, $value->nomor_soal);
    } elseif ($value->jenis == '3') {
        array_push($jodohkan_belum_komplit, $value->nomor_soal);
    } elseif ($value->jenis == '4') {
        array_push($isian_belum_komplit, $value->nomor_soal);
    } elseif ($value->jenis == '6') {
        array_push($bs_belum_komplit, $value->nomor_soal);
    } else {
        array_push($essai_belum_komplit, $value->nomor_soal);
    }
}

$soal_pg_ada = [];
$soal_pg2_ada = [];
$soal_jodohkan_ada = [];
$soal_isian_ada = [];
$soal_esai_ada = [];
$soal_bs_ada = [];

foreach ($soal_ada as $key => $value) {
    if ($value->jenis == '1') {
        array_push($soal_pg_ada, $value->nomor_soal);
    } elseif ($value->jenis == '2') {
        array_push($soal_pg2_ada, $value->nomor_soal);
    } elseif ($value->jenis == '3') {
        array_push($soal_jodohkan_ada, $value->nomor_soal);
    } elseif ($value->jenis == '4') {
        array_push($soal_isian_ada, $value->nomor_soal);
    } elseif ($value->jenis == '6') {
        array_push($soal_bs_ada, $value->nomor_soal);
    } else {
        array_push($soal_esai_ada, $value->nomor_soal);
    }
}
?>

<div class="content-wrapper bg-white">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-sm-flex justify-content-between mb-2">
                <h1><?= $subjudul ?></h1>
                <a href="<?= base_url('cbtbanksoal/detail/' . $bank->id_bank) ?>" type="button"
                   class="btn btn-sm btn-danger float-right">
                    <i class="fas fa-arrow-circle-left"></i><span
                            class="d-none d-sm-inline-block ml-1">Kembali</span>
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow">
                <div class="card-body">
                    <div class="row">
                        <h6 class="col-md-6"><b>Bank
                                Soal:</b> <?= $bank->bank_kode . ' | PG: ' . $bank->tampil_pg . ', PG Kompleks: ' . $bank->tampil_kompleks . ', Benar/Salah: ' . (int) ($bank->tampil_bs ?? 0) ?>
                        </h6>
                    </div>
                </div>
            </div>
            <?= form_open('create', array('id' => 'create')) ?>
            <div class="card my-shadow">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link <?= $tab_active == '1' ? 'active' : '' ?>"
                                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=1') ?>">Pilihan
                                Ganda</a></li>
                        <li class="nav-item"><a class="nav-link <?= $tab_active == '2' ? 'active' : '' ?>"
                                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=2') ?>">Pilihan
                                Ganda Kompleks</a></li>
                        <li class="nav-item"><a class="nav-link <?= $tab_active == '6' ? 'active' : '' ?>"
                                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=6') ?>">Benar
                                / Salah</a></li>
                        <!-- PRD FR-11: hanya PG, PG Kompleks, Benar/Salah. Tab lain disembunyikan. -->
                    </ul>
                </div>
                <div class="card-body">
                    <input type="hidden" id="jenis-id" name="jenis" value="1" class="form-control">
                    <input type="hidden" id="bank-id" name="bank_id" value="<?= $bank->id_bank ?>" class="form-control">
                    <input type="hidden" id="soal-id" name="soal_id"
                           value="<?= is_null($soal) ? '0' : $soal->id_soal ?>" class="form-control">
                    <input type="hidden" id="method" name="method" value="<?= is_null($soal) ? 'add' : 'edit' ?>"
                           class="form-control">

                    <div class="tab-content">
                        <div class="tab-pane  <?= $tab_active == '1' ? 'active' : '' ?>" id="ganda">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Pilihan Ganda, jumlah soal seharusnya: <b><?= $bank->tampil_pg ?></b>
                            </div>
                            <span><b>PG NOMOR: </b></span><br>
                            <?php
                            $no = 1;
                            $tpg = !isset($jml_pg) || $jml_pg == null || $jml_pg->nomor_soal == null ? 0 : $jml_pg->nomor_soal;
                            for ($i = 0; $i < $tpg; $i++) : ?>
                                <a href="javascript:void(0)"
                                   class="mt-1 btn btn-circle-sm <?= !in_array($no, $pg_belum_komplit) ? 'btn-success' : 'btn-outline-danger' ?> <?= is_null($soal) ? ($no == 1 ? 'active' : '') : ($no == $soal->nomor_soal ? 'active' : '') ?>"
                                   onclick="getSoalById(<?= $bank->id_bank ?>, <?= $no ?>, <?= '1' . $no ?>, '1')"
                                   id="btn-<?= '1' . $no ?>"><?= $no ?>
                                </a>
                                <?php
                                $no++;
                            endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-outline-success"
                               data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                               data-nomor="<?= $no ?>" onclick="tambahSoalPg(this)" id="btn-add-new-pg"><i
                                        class="fa fa-plus"></i> Tambah PG</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '2' ? 'active' : '' ?>" id="kompleks">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Pilihan Ganda Kompleks, jumlah soal seharusnya: <b><?= $bank->tampil_kompleks ?></b>
                            </div>
                            <span><b>SOAL NOMOR: </b></span><br>
                            <?php

                            $tpg2 = !isset($jml_pg2) || $jml_pg2 == null || $jml_pg2->nomor_soal == null ? 0 : $jml_pg2->nomor_soal;
                            $no2 = 1;
                            for ($i = 0; $i < $tpg2; $i++) : ?>
                                <a href="javascript:void(0)"
                                   class="mt-1 btn btn-circle-sm <?= !in_array($no2, $pg2_belum_komplit) ? 'btn-success' : 'btn-outline-danger' ?> <?= is_null($soal) ? ($no2 == 1 ? 'active' : '') : ($no2 == $soal->nomor_soal ? 'active' : '') ?>"
                                   onclick="getSoalById(<?= $bank->id_bank ?>, <?= $no2 ?>, <?= '2' . $no2 ?>, '2')"
                                   id="btn-<?= '2' . $no2 ?>"><?= $no2 ?>
                                </a>
                                <?php
                                $no2++;
                            endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-outline-success"
                               data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                               data-nomor="<?= $no2 ?>" onclick="tambahSoalPg2(this)" id="btn-add-new-pg"><i
                                        class="fa fa-plus"></i> Tambah PG Kompleks</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '6' ? 'active' : '' ?>" id="benarsalah">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Benar/Salah, jumlah soal seharusnya: <b><?= (int) ($bank->tampil_bs ?? 0) ?></b>
                            </div>
                            <span><b>SOAL NOMOR: </b></span><br>
                            <?php
                            $tbs = !isset($jml_bs) || $jml_bs == null || $jml_bs->nomor_soal == null ? 0 : $jml_bs->nomor_soal;
                            $nobs = 1;
                            for ($i = 0; $i < $tbs; $i++) : ?>
                                <a href="javascript:void(0)"
                                   class="mt-1 btn btn-circle-sm <?= !in_array($nobs, $bs_belum_komplit) ? 'btn-success' : 'btn-outline-danger' ?> <?= is_null($soal) ? ($nobs == 1 ? 'active' : '') : ($nobs == $soal->nomor_soal ? 'active' : '') ?>"
                                   onclick="getSoalById(<?= $bank->id_bank ?>, <?= $nobs ?>, <?= '6' . $nobs ?>, '6')"
                                   id="btn-<?= '6' . $nobs ?>"><?= $nobs ?>
                                </a>
                                <?php
                                $nobs++;
                            endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-outline-success"
                               data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                               data-nomor="<?= $nobs ?>" onclick="tambahSoalBs(this)" id="btn-add-new-bs"><i
                                        class="fa fa-plus"></i> Tambah Benar/Salah</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $pg_none = $tab_active == '1' ? '' : 'd-none';
            $pg2_none = $tab_active == '2' ? '' : 'd-none';
            $jodohkan_none = $tab_active == '3' ? '' : 'd-none';
            $isian_none = $tab_active == '4' ? '' : 'd-none';
            $essai_none = $tab_active == '5' ? '' : 'd-none';
            $bs_none = $tab_active == '6' ? '' : 'd-none';
            ?>
            <div class="alert alert-default-info" id="empty-soal">
                <span>Belum ada soal</span>
            </div>
            <div class="card my-shadow d-none" id="not-empty-soal">
                <div class="card-header">
                    <div class="card-title">
                        <h6 id="nomor-soal"><b>Soal Nomor: 1</b></h6>
                    </div>
                    <div class="card-tools">
                        <button type="submit" id="save-question" class="btn btn-primary btn-sm mr-1">
                            <i class="fa fa-save mr-1"></i>Simpan
                        </button>
                        <button type="submit" id="save-add-another" class="btn btn-outline-primary btn-sm">
                            Simpan &amp; Tambah Lagi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="soal-area mt-4">
                        <div class="row">
                            <div class="col-md-6" id="soal-area">
                                <div class="form-group">
                                    <label>Stimulus (opsional: teks bacaan, gambar, tabel)</label>
                                    <textarea class="textstimulus" id="textstimulus"
                                              placeholder="Tulis stimulus / wacana disini (opsional)"
                                              name="deskripsi"
                                              style="width:100%;"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Soal</label>
                                    <textarea class="textsoal" id="textsoal"
                                              data-id="<?= $this->security->get_csrf_hash() ?>"
                                              data-name="<?= $this->security->get_csrf_token_name() ?>" name="soal"
                                              placeholder="Tulis soal disini"
                                              style="width:100%;"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6" id="jawaban-area">
                                <?php
                                $opsis [''] = 'Pilih Jawaban Benar :';
                                $opsis ['a'] = 'A';
                                $opsis ['b'] = 'B';
                                $opsis ['c'] = 'C';

                                if ($bank->opsi == 4) {
                                    $opsis ['d'] = 'D';
                                    $rangeAbjad = range('a', 'd');
                                } elseif ($bank->opsi == 5) {
                                    $opsis ['d'] = 'D';
                                    $opsis ['e'] = 'E';
                                    $rangeAbjad = range('a', 'e');
                                } else {
                                    $rangeAbjad = range('a', 'c');
                                };
                                ?>
                                <div id="root-opsi-pg" class="<?= $pg_none ?>">
                                    <?php
                                    foreach ($rangeAbjad as $abjad) :
                                        ?>
                                        <div class="mb-3 ml-3">
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" class="custom-control-input pg-correct-answer"
                                                       name="jawaban_pg" value="<?= $abjad ?>"
                                                       id="jawaban-benar-<?= $abjad ?>" required>
                                                <label class="custom-control-label" for="jawaban-benar-<?= $abjad ?>">
                                                    Jawaban <?= strtoupper($abjad) ?> benar
                                                </label>
                                            </div>
                                            <textarea class="textjawaban" id="textjawaban_<?= $abjad ?>"
                                                      name="jawaban_<?= $abjad ?>"
                                                      placeholder="Place some text here"
                                                      style="width:100%;"></textarea>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="root-opsi-pg2" class="<?= $pg2_none ?>">
                                    <div id="opsi-pg2"></div>
                                    <div class="mb-3 ml-3">
                                        <button id="tambah-jawaban-pg2" type="button" class="btn btn-success">Tambah
                                            Jawaban
                                        </button>
                                    </div>
                                </div>

                                <div class="<?= $jodohkan_none ?> mt-4" id="root-opsi-jodohkan">
                                    <hr>
                                    <p><b>Jawaban</b></p>
                                    <div id="jawaban-jodohkan">
                                    </div>
                                    <div class="alert alert-default-warning">
                                        Maksimal <b>20 KOLOM</b> dan <b>20 BARIS</b>
                                    </div>
                                </div>

                                <div class="mt-1 ml-3" id="jawaban-benar">
                                    <label>Jawaban Benar</label>
                                    <div id="root-jawaban-pg" class="<?= $pg_none ?>">
                                        <small class="text-muted">Pilih lingkaran pada pilihan jawaban yang benar di atas.</small>
                                    </div>
                                    <div class="<?= $bs_none ?>" id="root-jawaban-bs">
                                        <?php
                                        echo form_dropdown(
                                            'jawaban_bs',
                                            array('' => 'Pilih Jawaban Benar :', 'b' => 'Benar', 's' => 'Salah'),
                                            '',
                                            'id="jawaban-bs" class="form-control"'
                                        ); ?>
                                    </div>
                                    <div class="<?= $isian_none ?>" id="root-jawaban-isian">
                                        <input type="text" id="jawaban-isian" name="jawaban_isian" class="form-control"
                                               value="" disabled="disabled"/>
                                    </div>
                                    <div class="<?= $essai_none ?>" id="root-jawaban-essai">
										<textarea class="textjawaban-essai" id="jawaban-essai" name="jawaban_essai"
                                                  placeholder="Place some text here" style="width:100%;"
                                                  disabled="disabled">
										</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
</div>

<?= form_open('create', array('id' => 'addfile')) ?>
<div class="modal fade" id="pickerModal" tabindex="-1" role="dialog" aria-labelledby="pickerModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h5 id="pickerModalLabel">Pilih File</h5>
                    <br> hanya untuk file audio dan video
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <ul id="media-list" class="clearfix">
                        <li class="myupload">
                                    <span>
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                        <input name="file_uploads" type="file" id="picupload"
                                               class="picupload">
                                    </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h5 id="noteModalLabel">Edit Field</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea id="edit-dialog" style="width:100%;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanValue()">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>/assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/mediastyler.js"></script>

<script src="<?= base_url() ?>/assets/app/js/linker-list.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>

<script type="text/javascript">
    var nomor_soal = '<?=$p_no?>';
    let soal_ada = '<?=is_null($soal) ? 'n' : 'y'?>';
    let idBank = '<?= $bank->id_bank ?>';
    let idSoal = '<?= is_null($soal) ? 0 : $soal->id_soal ?>';
    let jenis = '<?= $tab_active ?>';
    console.log('jns', jenis);

    var dataFiles = [];
    var arrFileAttach = JSON.parse('<?= json_encode($dataFileAttach)?>');
    dataFiles = $.merge(dataFiles, arrFileAttach);

    var adaPg = JSON.parse('<?= json_encode($soal_pg_ada)?>');
    var adaPg2 = JSON.parse('<?= json_encode($soal_pg2_ada)?>');
    var adaJodohkan = JSON.parse('<?= json_encode($soal_jodohkan_ada)?>');
    var adaIsian = JSON.parse('<?= json_encode($soal_isian_ada)?>');
    var adaEssai = JSON.parse('<?= json_encode($soal_esai_ada)?>');
    console.log('adaPg', adaIsian);

    $(function () {
        bsCustomFileInput.init();
    });
</script>
<script src="<?= base_url() ?>/assets/app/js/cbt/banksoal/soal.js"></script>
