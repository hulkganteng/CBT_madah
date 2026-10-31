<?php
/**
 * Halaman daftar bank soal untuk simulasi/ujicoba siswa
 */
?>
<div class="content-wrapper" style="margin-top: -1px;">
    <div class="sticky"></div>
    <section class="content overlap p-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <?php $this->load->view('members/siswa/templates/top'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="text-center"><b>SIMULASI / UJICOBA</b></div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-default-info">
                                Pilih bank soal untuk berlatih. Hasil latihan tidak masuk ke penilaian resmi.
                            </div>

                            <?php if (empty($banks)) : ?>
                                <div class="alert alert-default-warning text-center">
                                    Belum ada bank soal yang tersedia untuk kelas Anda.
                                </div>
                            <?php else : ?>
                                <div class="row" id="daftar-bank">
                                    <?php foreach ($banks as $bank) : ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        <b><?= $bank->nama_mapel ?? $bank->bank_nama ?></b>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <ul class="list-group list-group-unbordered">
                                                        <li class="list-group-item p-1">
                                                            Kode
                                                            <span class="float-right"><b><?= $bank->bank_kode ?></b></span>
                                                        </li>
                                                        <li class="list-group-item p-1">
                                                            Jenis
                                                            <span class="float-right"><b><?= $bank->nama_jenis ?></b></span>
                                                        </li>
                                                        <li class="list-group-item p-1">
                                                            Jumlah Soal
                                                            <span class="float-right"><b><?= $bank->jml_soal ?></b></span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="card-footer p-1 text-center">
                                                    <a href="<?= base_url('ujicoba/kerjakan/' . $bank->id_bank) ?>"
                                                       class="btn btn-primary btn-block">
                                                        <i class="fas fa-play mr-1"></i> Kerjakan
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
