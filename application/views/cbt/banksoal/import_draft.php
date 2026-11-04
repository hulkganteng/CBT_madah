<div class="content-wrapper bg-white">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1>Import Soal</h1>
            <a href="<?= base_url('cbtbanksoal/detail/' . (int) $bank->id_bank) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <?php $this->load->view('cbt/banksoal/_excel_import_draft'); ?>
            <div class="card my-shadow">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Tempel dari Word</strong>
                        <div class="text-muted">Parser Word baru sedang disiapkan agar tidak mengganti soal lama.</div>
                    </div>
                    <span class="badge badge-secondary p-2">Dalam pengembangan</span>
                </div>
            </div>
        </div>
    </section>
</div>

