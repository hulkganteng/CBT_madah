<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1><?= html_escape($judul) ?></h1>
            <a href="<?= base_url('cbtjadwal') ?>" class="btn btn-sm btn-danger"><i class="fas fa-arrow-circle-left mr-1"></i>Kembali</a>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-info">Gunakan kode bank soal dan kode jenis ujian. Seluruh baris harus valid sebelum dapat disimpan.
                Untuk jadwal per sesi, isi tanggal sesi, kode sesi, jenis sasaran (<code>rombel</code>, <code>peminatan</code>, atau <code>program_mak</code>), kode sasaran, urutan mapel, dan jam buka WIB (misalnya <code>07:45</code>).
                Gunakan lembar <b>Kode Sesi dan Sasaran</b> di template. Isi <code>1</code> pada <code>wajib_mapel_pertama</code> untuk mensyaratkan urutan 1, atau pada <code>ubah_sesi</code> untuk memindahkan sasaran pada tanggal tersebut.
                Ulangi bank dan jenis ujian untuk menambahkan slot lain dengan pengaturan ujian yang sama. Kosongkan kolom sesi untuk jadwal biasa.</div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card my-shadow">
                        <div class="card-header"><b>File Jadwal</b><a href="<?= base_url('cbtjadwalimport/template') ?>" class="btn btn-success btn-sm float-right"><i class="fas fa-download mr-1"></i>Template</a></div>
                        <div class="card-body">
                            <?= form_open_multipart('cbtjadwalimport/preview', array('id' => 'preview-form')) ?>
                            <label for="upload-file">Pilih Excel atau CSV</label>
                            <input id="upload-file" class="form-control-file" type="file" name="upload_file" accept=".xlsx,.xls,.csv" required>
                            <button class="btn btn-primary btn-block mt-3" type="submit"><i class="fas fa-search mr-1"></i>Pratinjau</button>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card my-shadow">
                        <div class="card-header"><b>Pratinjau</b></div>
                        <div class="card-body" id="preview" aria-live="polite">Belum ada file yang diperiksa.</div>
                        <div class="card-footer text-right">
                            <?= form_open('cbtjadwalimport/commit', array('id' => 'commit-form')) ?>
                            <input type="hidden" name="rows" id="rows">
                            <button class="btn btn-success" type="submit" disabled><i class="fas fa-file-import mr-1"></i>Impor Jadwal</button>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
$(function () {
    const preview = $('#preview');
    const commit = $('#commit-form');
    $('#preview-form').on('submit', function (event) {
        event.preventDefault();
        preview.text('Memeriksa file...');
        commit.find('button').prop('disabled', true);
        $.ajax({url: this.action, method: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json'})
            .done(function (result) {
                if (!result.status) {
                    preview.html('<div class="alert alert-danger mb-0"><ul class="mb-0"></ul></div>');
                    $.each(result.errors || ['File tidak valid.'], function (_, error) { preview.find('ul').append($('<li>').text(error)); });
                    return;
                }
                const table = $('<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Baris</th><th>Bank</th><th>Jenis</th><th>Mulai</th><th>Selesai</th><th>Durasi</th><th>Tanggal sesi</th><th>Sesi</th><th>Sasaran</th><th>Urutan</th><th>Buka WIB</th><th>Wajib mapel 1</th><th>Ubah sesi</th></tr></thead><tbody></tbody></table></div>');
                $.each(result.rows, function (_, row) {
                    const tr = $('<tr>');
                    $.each([row._row, row.kode_bank, row.kode_jenis, row.tanggal_mulai, row.tanggal_selesai, row.durasi, row.tanggal_sesi, row.kode_sesi,
                        [row.jenis_sasaran, row.kode_sasaran].filter(Boolean).join(' / '), row.urutan_mapel, row.jam_buka_wib,
                        row.tanggal_sesi ? (row.wajib_mapel_pertama === '1' ? 'Ya' : 'Tidak') : '',
                        row.tanggal_sesi ? (row.ubah_sesi === '1' ? 'Ya' : 'Tidak') : ''], function (_, value) { tr.append($('<td>').text(value)); });
                    table.find('tbody').append(tr);
                });
                preview.empty().append(table);
                $('#rows').val(JSON.stringify(result.rows));
                commit.find('button').prop('disabled', false);
            }).fail(function () { preview.html('<div class="alert alert-danger mb-0">Permintaan gagal. Silakan coba lagi.</div>'); });
    });
    commit.on('submit', function (event) {
        event.preventDefault();
        const button = commit.find('button').prop('disabled', true);
        $.post(this.action, commit.serialize(), null, 'json').done(function (result) {
            if (result.status) {
                swal.fire('Berhasil', result.message, 'success').then(function () { window.location.href = '<?= base_url('cbtjadwal') ?>'; });
            } else {
                swal.fire('Gagal', result.message || 'Jadwal gagal diimpor.', 'error');
                button.prop('disabled', false);
            }
        }).fail(function () { swal.fire('Gagal', 'Permintaan gagal. Silakan coba lagi.', 'error'); button.prop('disabled', false); });
    });
});
</script>
