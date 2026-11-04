<div class="card my-shadow mb-4" id="excel-import-card" data-bank-id="<?= (int) $bank->id_bank ?>">
    <div class="card-header">
        <h6 class="card-title mb-0"><b>Import dari Excel</b></h6>
        <a href="<?= base_url('excelsoal/template?bank_id=' . (int) $bank->id_bank) ?>" class="btn btn-sm btn-success card-tools">
            <i class="fas fa-download mr-1"></i>Download Template Excel
        </a>
    </div>
    <div class="card-body">
        <?= form_open_multipart('excelsoal/create', array('id' => 'excel-question-form')) ?>
        <input type="hidden" name="bank_id" value="<?= (int) $bank->id_bank ?>">
        <div class="row align-items-end">
            <div class="col-md-9 mb-3">
                <label for="excel-question-file">Pilih file Excel</label>
                <div class="custom-file">
                    <input type="file" name="excel_file" id="excel-question-file" class="custom-file-input" accept=".xlsx,.xls" required>
                    <label class="custom-file-label" for="excel-question-file">Belum ada file dipilih</label>
                </div>
                <small class="form-text text-muted">Format .xlsx atau .xls, maksimal 5 MB.</small>
            </div>
            <div class="col-md-3 mb-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search mr-1"></i>Upload &amp; Periksa</button>
            </div>
        </div>
        <?= form_close() ?>

        <div id="import-draft" class="d-none">
            <hr>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1" id="import-filename">Draft Import</h5>
                    <span class="badge badge-light p-2 mr-1"><span id="count-total">0</span> Soal Ditemukan</span>
                    <span class="badge badge-success p-2 mr-1"><span id="count-valid">0</span> Siap</span>
                    <span class="badge badge-warning p-2"><span id="count-invalid">0</span> Perlu Diperbaiki</span>
                </div>
                <div class="btn-group mt-2 mt-md-0" id="import-filters">
                    <button type="button" class="btn btn-sm btn-outline-secondary active" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-success" data-filter="ready">Siap</button>
                    <button type="button" class="btn btn-sm btn-outline-warning" data-filter="problems">Bermasalah</button>
                </div>
            </div>
            <div id="import-alert"></div>
            <div id="import-items"></div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button type="button" id="page-prev" class="btn btn-sm btn-outline-secondary">Sebelumnya</button>
                <span id="page-info" class="text-muted"></span>
                <button type="button" id="page-next" class="btn btn-sm btn-outline-secondary">Berikutnya</button>
            </div>
            <div class="d-flex justify-content-between border-top pt-3">
                <button type="button" id="cancel-import" class="btn btn-outline-danger">Batalkan Import</button>
                <button type="button" id="commit-import" class="btn btn-success"><i class="fas fa-file-import mr-1"></i>Import Soal Siap</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-import-item" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?= form_open('excelsoal/updateItem', array('id' => 'edit-import-form')) ?>
            <div class="modal-header">
                <h5 class="modal-title">Perbaiki Soal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="import_id">
                <input type="hidden" name="item_id">
                <div class="form-group">
                    <label>Jenis soal</label>
                    <select name="type" class="form-control" required>
                        <option value="pg">Pilihan Ganda</option>
                        <option value="pg_complex">Pilihan Ganda Kompleks</option>
                        <option value="true_false">Benar/Salah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="question" class="form-control" rows="3" required></textarea>
                </div>
                <div id="edit-options">
                    <?php foreach (range('A', 'E') as $letter): ?>
                        <div class="form-group row align-items-center">
                            <label class="col-1 mb-0"><?= $letter ?></label>
                            <div class="col-11"><input type="text" name="option_<?= strtolower($letter) ?>" class="form-control"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label>Jawaban benar</label>
                    <div id="edit-answers"></div>
                </div>
                <div class="form-group mb-0">
                    <label>Pembahasan <span class="text-muted">(opsional)</span></label>
                    <textarea name="explanation" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perbaikan</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/app/js/cbt/banksoal/excel-import-draft.js') ?>"></script>
