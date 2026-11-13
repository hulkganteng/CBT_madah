<?php
$peminatanSiswa = $this->db->where('status', 1)->order_by('nama_jurusan')->get('master_jurusan')->result();
?>
<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-sm-flex justify-content-between mb-2">
                <h1><?= $subjudul ?></h1>
                <a href="<?= base_url('datasiswa') ?>" type="button" class="btn btn-sm btn-danger">
                    <i class="fas fa-arrow-circle-left"></i><span class="d-none d-sm-inline-block ml-1">Kembali</span>
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <br>
            <div class="col-lg-12 p-0">
                <div class="alert alert-default-danger shadow align-content-center" role="alert">
                    <strong>Catatan!</strong> untuk import data dari file excel, silahkan download templatenya terlebih
                    dahulu.
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4 col-lg-5">
                    <div class="card my-shadow mb-4">
                        <?= form_open_multipart('', array('id' => 'formPreviewExcel')); ?>
                        <div class="card-header">
                            <h6 class="card-title">File Excel</h6>
                            <?php
                            $url = $tipe == 'add' ? 'assets/app/template-import/format_siswa.xlsx' : 'datasiswa/downloaddata';
                            ?>
                            <a href="<?= base_url() . $url ?>" class="card-tools btn-success btn btn-sm">
                                <i class="fas fa-download"></i><span class="ml-2">Download Template</span>
                            </a>
                        </div>
                        <div class="card-body excel">
                            <div class="form-group pb-2">
                                <label for="upload_file">Pilih file excel</label>
                                <input type="file" id="input-file-events-excel" name="upload_file" class="dropify"/>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-7">
                    <div class="card my-shadow mb-4">
                        <?= form_open('', array('id' => 'formUpload')); ?>
                        <div class="card-header">
                            <h6 class="card-title">Preview</h6>
                            <button id="submit-excel" name="preview" type="submit"
                                    class="btn btn-sm btn-primary card-tools" disabled="disabled">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>Upload
                            </button>
                        </div>
                        <div class="card-body pb-0">
                            <div class="form-row">
                                <div class="col-md-6"><label>Peminatan default (opsional)</label><select class="form-control" id="peminatan_id" name="peminatan_id"><option value="">Gunakan kolom Excel</option><?php foreach ($peminatanSiswa as $major) : ?><option value="<?= (int) $major->id_jurusan ?>" data-code="<?= html_escape(strtoupper($major->kode_jurusan)) ?>"><?= html_escape($major->nama_jurusan) ?></option><?php endforeach; ?></select><small class="text-muted">Dipakai hanya bila kolom PEMINATAN_ID kosong.</small></div>
                                <div class="col-md-6" id="program-mak-row"><label>Program MAK default</label><select class="form-control" id="program_mak" name="program_mak"><option value="">Pilih program</option><option value="TAHFIDZ">Tahfidz</option><option value="KITAB">Kitab</option></select></div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                        <div class="card-body" id="file-preview">
                            <span class="text-center">Pastikan anda telah mengisi format yang telah disediakan.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    var typeImport = '<?=$tipe?>';
</script>
<script type="text/javascript"
        src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson-cell.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson-row.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson.js"></script>
<script src="<?= base_url() ?>/assets/app/js/jquery.htmlparser.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/master/siswa/import.js?v=<?= filemtime(FCPATH . 'assets/app/js/master/siswa/import.js') ?>"></script>
<script>
$(function () {
    function syncImportSpecialization() {
        var $major = $('#peminatan_id');
        $('#import-major-hidden').remove();
        if ($major.prop('disabled')) $('<input>', {id:'import-major-hidden', type:'hidden', name:'peminatan_id', value:$major.val()}).appendTo('#formUpload');
        var mak = $major.find('option:selected').data('code') === 'MAK';
        $('#program-mak-row').toggle(mak); $('#program_mak').prop('required', mak);
        if (!mak) $('#program_mak').val('');
    }
    $('#peminatan_id').change(syncImportSpecialization); syncImportSpecialization();
});
</script>
