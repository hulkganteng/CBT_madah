<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-sm-flex justify-content-between mb-2">
                <h1><?= $subjudul ?></h1>
                <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-right">
                    <i class="fas fa-arrow-circle-left"></i><span
                            class="d-none d-sm-inline-block ml-1">Kembali</span>
                </button>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php $this->load->view('cbt/banksoal/_excel_import'); ?>
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
