<details class="small">
    <summary><?= count($summarySlots) ?> slot sesi dan kelompok</summary>
    <ul class="pl-3 mb-0">
        <?php foreach ($summarySlots as $slot) : ?>
            <li>
                <?= html_escape($slot['tanggal']) ?> · <?= html_escape($slot['nama_sesi']) ?> ·
                <?= html_escape(Exam_schedule_slots::GROUPS[$slot['jenis_sasaran']] . ' ' . $slot['target_name']) ?> ·
                urutan <?= (int) $slot['urutan'] ?> · <?= html_escape(substr($slot['jam_buka'], 0, 5)) ?> WIB
                <?php if ($slot['wajib_mapel_pertama']) : ?> · wajib mapel pertama<?php endif; ?>
                <?php if ($slot['ubah_sesi']) : ?> · pindah sesi harian<?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</details>
