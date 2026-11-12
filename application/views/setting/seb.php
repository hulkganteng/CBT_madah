<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Generator Safe Exam Browser</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('seb_error')): ?>
                <div class="alert alert-danger" role="alert"><?= html_escape($this->session->flashdata('seb_error')) ?></div>
            <?php endif; ?>

            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Buat File Konfigurasi</h3></div>
                <?= form_open('sebconfig/download') ?>
                <div class="card-body">
                    <div class="form-group">
                        <label for="preset">Preset</label>
                        <select class="form-control" id="preset" name="preset" required>
                            <?php foreach ($presets as $key => $preset): ?>
                                <option value="<?= html_escape($key) ?>" data-url="<?= html_escape($preset['startURL']) ?>" data-file="<?= html_escape($preset['file']) ?>">
                                    <?= html_escape(ucfirst($key)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_url">Start URL</label>
                        <input class="form-control" id="start_url" name="start_url" type="url" required>
                        <small class="form-text text-muted">Alamat halaman login peserta yang dibuka saat SEB dijalankan.</small>
                    </div>

                    <div class="form-group">
                        <label for="filename">Nama file</label>
                        <input class="form-control" id="filename" name="filename" maxlength="100" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="admin_password">Password admin SEB</label>
                            <input class="form-control" id="admin_password" name="admin_password" type="password" minlength="8" autocomplete="new-password" required>
                            <small class="form-text text-muted">Dipakai untuk mengubah konfigurasi SEB.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="quit_password">Password keluar SEB</label>
                            <input class="form-control" id="quit_password" name="quit_password" type="password" minlength="8" autocomplete="new-password" required>
                            <small class="form-text text-muted">Simpan password ini; peserta membutuhkannya untuk keluar.</small>
                        </div>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" id="url_filter" name="url_filter" type="checkbox" value="1" checked>
                        <label class="custom-control-label" for="url_filter">Batasi akses ke domain CBT</label>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-download mr-1"></i> Buat dan Download .seb</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var preset = document.getElementById('preset');
    var startUrl = document.getElementById('start_url');
    var filename = document.getElementById('filename');
    function applyPreset() {
        var option = preset.options[preset.selectedIndex];
        startUrl.value = option.dataset.url;
        filename.value = option.dataset.file;
    }
    preset.addEventListener('change', applyPreset);
    applyPreset();
});
</script>
