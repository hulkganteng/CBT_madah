<?php
/**
 * Backup & pemulihan database (PRD FR-27).
 * Restore menuntut pengetikan ulang nama berkas supaya tidak salah klik.
 */
?>
<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-8">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-4 text-right">
                    <button type="button" class="btn btn-primary" id="btn-backup">
                        <i class="fa fa-download"></i> Backup Sekarang
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-warning">
                <b>Wajib sebelum dan sesudah hari ujian.</b>
                Restore menimpa seluruh isi database dengan isi berkas backup.
                Sistem otomatis membuat backup baru sebelum restore dijalankan,
                tetapi tetap periksa nama berkas dengan teliti.
            </div>

            <div class="card my-shadow">
                <div class="card-header">
                    <h6 class="card-title mb-0">Berkas Backup</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered w-100">
                            <thead class="bg-light">
                            <tr>
                                <th>No.</th>
                                <th>Berkas</th>
                                <th class="text-center">Ukuran</th>
                                <th class="text-center">Waktu</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$berkas) : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada berkas backup.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($berkas as $i => $row) : ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><code><?= html_escape($row['nama']) ?></code></td>
                                    <td class="text-center"><?= number_format($row['ukuran'] / 1048576, 2) ?> MB</td>
                                    <td class="text-center"><?= date('d/m/Y H:i', $row['waktu']) ?></td>
                                    <td class="text-center text-nowrap">
                                        <a class="btn btn-xs btn-default"
                                           href="<?= base_url('dbpemulihan/unduh/' . rawurlencode($row['nama'])) ?>">
                                            <i class="fa fa-file-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-danger btn-pulihkan"
                                                data-berkas="<?= html_escape($row['nama']) ?>">
                                            <i class="fa fa-undo"></i> Pulihkan
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-pulihkan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">Pulihkan Database</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Seluruh data ujian, jawaban, dan nilai akan diganti dengan isi berkas:</p>
                <p><code id="pulih-berkas">-</code></p>
                <div class="form-group">
                    <label>Ketik ulang nama berkas untuk konfirmasi*</label>
                    <input type="text" id="pulih-konfirmasi" class="form-control" autocomplete="off">
                    <small class="text-muted">Harus sama persis, termasuk akhiran .sql.zip</small>
                </div>
                <div class="alert alert-info mb-0">
                    Backup otomatis dibuat lebih dulu, jadi restore ini masih bisa dibatalkan
                    dengan memulihkan berkas backup yang baru muncul.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="konfirm-pulihkan" disabled>Pulihkan Sekarang</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function ($) {
        var berkas = '';

        function kirim(url, data, sukses) {
            $.post(base_url + url, data, function (res) {
                if (res.status) {
                    sukses(res);
                } else {
                    window.alert(res.message || 'Aksi gagal.');
                }
            }, 'json').fail(function (xhr) {
                var res = xhr.responseJSON || {};
                window.alert(res.message || 'Aksi gagal.');
            });
        }

        $('#btn-backup').on('click', function () {
            var btn = $(this).prop('disabled', true);
            kirim('dbpemulihan/backup', {}, function (res) {
                window.alert(res.message);
                window.location.reload();
            });
            window.setTimeout(function () { btn.prop('disabled', false); }, 15000);
        });

        $(document).on('click', '.btn-pulihkan', function () {
            berkas = $(this).data('berkas');
            $('#pulih-berkas').text(berkas);
            $('#pulih-konfirmasi').val('');
            $('#konfirm-pulihkan').prop('disabled', true);
            $('#modal-pulihkan').modal('show');
        });

        $('#pulih-konfirmasi').on('input', function () {
            $('#konfirm-pulihkan').prop('disabled', $(this).val() !== berkas);
        });

        $('#konfirm-pulihkan').on('click', function () {
            if (!window.confirm('Restore akan menimpa database. Lanjutkan?')) return;
            var btn = $(this).prop('disabled', true);
            kirim('dbpemulihan/pulihkan', {berkas: berkas, konfirmasi: $('#pulih-konfirmasi').val()}, function (res) {
                $('#modal-pulihkan').modal('hide');
                window.alert(res.message);
                window.location.reload();
            });
            btn.prop('disabled', false);
        });
    })(window.jQuery);
</script>
