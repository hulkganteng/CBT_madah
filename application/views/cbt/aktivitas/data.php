<?php
/**
 * Pemantauan aktivitas siswa (PRD FR-19 / FR-22 + fitur tambahan).
 * Semua angka berasal dari server: client hanya menampilkan.
 */
?>
<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6 text-right">
                    <label class="mr-2 mb-0">Jadwal Ujian</label>
                    <select id="pilih-jadwal" class="form-control form-control-sm d-inline-block w-auto">
                        <option value="">-- pilih jadwal --</option>
                        <?php foreach ($jadwal as $id => $kode): ?>
                            <option value="<?= (int) $id ?>"><?= html_escape($kode) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-6 col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4 id="stat-total">0</h4>
                            <p>Peserta</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4 id="stat-online">0</h4>
                            <p>Online</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h4 id="stat-pelanggaran">0</h4>
                            <p>Pelanggaran</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h4 id="stat-selesai">0</h4>
                            <p>Selesai</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <b>Aktivitas Peserta</b>
                        <small class="text-muted ml-2" id="info-refresh">menunggu jadwal</small>
                    </div>
                    <div class="card-tools">
                        <button type="button" id="toggle-auto" class="btn btn-sm btn-success">
                            <i class="fa fa-pause"></i> <span>Auto refresh</span>
                        </button>
                        <button type="button" id="refresh-now" class="btn btn-sm btn-default">
                            <i class="fa fa-sync"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="peringatan-batas-jadwal" class="alert alert-warning d-none"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover w-100" id="tabel-aktivitas">
                            <thead class="bg-light">
                            <tr>
                                <th>No.</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Sisa Waktu</th>
                                <th class="text-center">Koneksi</th>
                                <th class="text-center">Pindah Tab</th>
                                <th class="text-center">Keluar FS</th>
                                <th class="text-center">Tambahan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                            </thead>
                            <tbody id="isi-aktivitas">
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada peserta.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-waktu" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Waktu Peserta</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Peserta: <b id="waktu-nama">-</b></p>
                <div class="form-group">
                    <label>Tambahan (menit)*</label>
                    <input type="number" id="waktu-menit" class="form-control" min="1" max="120" value="5">
                </div>
                <div class="form-group">
                    <label>Alasan*</label>
                    <textarea id="waktu-alasan" class="form-control" rows="3"
                              placeholder="Contoh: PC mati 8 menit, sudah dipindah ke PC cadangan"></textarea>
                    <small class="text-muted">Wajib diisi, tercatat sebagai bukti berita acara.</small>
                </div>
                <input type="hidden" id="waktu-siswa">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="simpan-waktu">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/app/js/exam-activity.js') ?>"></script>
