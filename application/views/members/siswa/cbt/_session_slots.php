<div class="col-12">
    <div class="card my-shadow">
        <div class="card-header"><h5 class="mb-0">Jadwal per Sesi</h5></div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Tanggal</th><th>Sesi</th><th>Urutan</th><th>Mapel</th><th>Buka (WIB)</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($studentSlots as $date => $dailySlots) : ?>
                    <?php foreach ($dailySlots as $id => $slot) :
                        $duration = $elapsed[$id] ?? NULL;
                        $finished = $duration && (int) $duration->status === 2;
                        $reason = $slot['block_reason'];
                        ?>
                        <tr>
                            <td><?= buat_tanggal(date('D, d M Y', strtotime($date))) ?></td>
                            <td><?= html_escape($slot['nama_sesi'] ?? 'Sesi tidak tersedia') ?></td>
                            <td><?= (int) $slot['urutan'] ?></td>
                            <td><?= html_escape($slot['nama_mapel']) ?><br><small><?= (int) $slot['durasi_ujian'] ?> menit</small></td>
                            <td><?= html_escape(substr($slot['jam_buka'], 0, 5)) ?></td>
                            <td>
                                <?php if ($finished) : ?>
                                    Sudah selesai
                                <?php elseif ($date < $todayWib) : ?>
                                    Sudah berakhir
                                <?php elseif ($date > $todayWib) : ?>
                                    Belum dimulai
                                <?php elseif ($reason !== NULL && !$duration) : ?>
                                    <?= html_escape($reason) ?>
                                    <a href="<?= base_url('siswa/konfirmasi/' . (int) $id) ?>" class="btn btn-outline-secondary btn-sm d-block mt-1">Buka konfirmasi</a>
                                <?php else : ?>
                                    <a href="<?= base_url('siswa/konfirmasi/' . (int) $id) ?>" class="btn btn-primary btn-sm">
                                        <?= $duration ? 'Lanjutkan' : 'Kerjakan' ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
