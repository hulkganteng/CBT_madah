<div class="card my-shadow mb-4" id="excel-import-card">
    <div class="card-header">
        <h6 class="card-title"><b>Import Soal dari Excel</b></h6>
        <a href="<?= base_url('excelsoal/template?bank_id=' . (int) $bank->id_bank) ?>" class="btn btn-sm btn-success card-tools">
            <i class="fas fa-download mr-1"></i>Template dari Admin
        </a>
    </div>
    <div class="card-body">
        <?= form_open_multipart('excelsoal/preview', array('id' => 'excel-question-form')) ?>
        <input type="hidden" name="bank_id" value="<?= html_escape($bank->id_bank) ?>">
        <div class="row align-items-end">
            <div class="col-md-7 mb-3">
                <label for="excel-question-file">File Excel</label>
                <div class="custom-file">
                    <input type="file" name="excel_file" id="excel-question-file" class="custom-file-input" accept=".xlsx,.xls" required>
                    <label class="custom-file-label" for="excel-question-file">Pilih file .xlsx atau .xls</label>
                </div>
                <small class="form-text text-muted">Maksimal 5 MB dan 2.000 soal per file.</small>
            </div>
            <div class="col-md-3 mb-3">
                <label for="excel-import-mode">Mode import</label>
                <select name="mode" id="excel-import-mode" class="form-control">
                    <option value="append">Tambah ke soal yang ada</option>
                    <option value="replace">Ganti seluruh soal</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search mr-1"></i>Preview
                </button>
            </div>
        </div>
        <?= form_close() ?>

        <div id="excel-import-result" class="d-none">
            <div id="excel-import-errors"></div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-3">
                    <thead><tr><th>No</th><th>Jenis</th><th>Soal</th><th>Status</th></tr></thead>
                    <tbody id="excel-import-preview"></tbody>
                </table>
            </div>
            <button type="button" id="excel-import-submit" class="btn btn-success" disabled>
                <i class="fas fa-file-import mr-1"></i>Import Soal
            </button>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/app/js/cbt/banksoal/excel-import.js') ?>"></script>
