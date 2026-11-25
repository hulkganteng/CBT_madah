<?php if (!$this->exam_schedule_slots->ready()) : ?>
    <p class="text-muted">Jalankan migrasi untuk mengatur jadwal per sesi dan kelompok peserta.</p>
    <?php return; ?>
<?php endif; ?>
<?php
$slotOptions = $this->exam_schedule_slots->options($jadwal->id_tp ?? $tp_active->id_tp, $jadwal->id_smt ?? $smt_active->id_smt);
$savedSlots = $this->exam_schedule_slots->forSchedule($jadwal->id_jadwal);
?>
<fieldset class="border rounded p-3 mb-3">
    <legend class="w-auto px-2 h6">Jadwal per Sesi dan Kelompok Peserta</legend>
    <p class="small text-muted mb-2">Tanpa slot, ujian mengikuti jadwal biasa. Tambahkan slot untuk setiap tanggal, sesi, dan sasaran yang memakai bank soal ini. Siswa dapat mulai dari jam buka slot hingga akhir sesi. Isi 07:45 WIB jika mapel kedua harus buka 15 menit sebelum pergantian pukul 08:00.</p>
    <p class="small text-muted">Syarat mapel pertama mencari urutan 1 pada tanggal, sesi, jenis ujian, dan tingkat yang sama, dengan mengutamakan sasaran yang sama. Centang <b>Ubah sesi pada tanggal ini</b> untuk memindahkan sasaran ke sesi tersebut pada hari itu. Ulangi untuk setiap rombel yang dipindah.</p>
    <input type="hidden" name="slots_json" id="slots-json" disabled>
    <div id="jadwal-slots"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="tambah-slot"><i class="fas fa-plus mr-1"></i>Tambah Slot</button>
</fieldset>
<template id="slot-template">
    <div class="border rounded p-2 mb-2 exam-slot">
        <div class="form-row">
            <label class="col-lg-2 col-sm-6">Tanggal
                <input type="date" data-slot="tanggal" class="form-control form-control-sm" required>
            </label>
            <label class="col-lg-2 col-sm-6">Sesi
                <select data-slot="id_sesi" class="form-control form-control-sm" required>
                    <option value="">Pilih sesi</option>
                    <?php foreach ($slotOptions['sesi'] as $option) : ?>
                        <option value="<?= (int) $option['id'] ?>"><?= html_escape($option['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="col-lg-2 col-sm-6">Jenis sasaran
                <select data-slot="jenis_sasaran" class="form-control form-control-sm" required>
                    <?php foreach (Exam_schedule_slots::GROUPS as $value => $label) : ?>
                        <option value="<?= html_escape($value) ?>"><?= html_escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="col-lg-2 col-sm-6">Sasaran peserta
                <select data-slot="sasaran" class="form-control form-control-sm" required></select>
            </label>
            <label class="col-lg-2 col-sm-6">Urutan mapel
                <input type="number" data-slot="urutan" min="1" max="65535" class="form-control form-control-sm" required>
            </label>
            <label class="col-lg-2 col-sm-6">Jam buka WIB
                <input type="time" data-slot="jam_buka" class="form-control form-control-sm" required>
            </label>
        </div>
        <div class="d-flex flex-wrap align-items-center">
            <label class="mb-1 mr-3"><input type="checkbox" data-slot="wajib_mapel_pertama"> Wajib menyelesaikan mapel pertama</label>
            <label class="mb-1 mr-3"><input type="checkbox" data-slot="ubah_sesi"> Ubah sesi pada tanggal ini</label>
            <button type="button" class="btn btn-sm btn-outline-danger ml-auto remove-slot">Hapus slot</button>
        </div>
    </div>
</template>
<script>
(function () {
    const options = <?= json_encode($slotOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const saved = <?= json_encode($savedSlots, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const wrapper = document.getElementById('jadwal-slots');
    const payload = document.getElementById('slots-json');
    const template = document.getElementById('slot-template');
    const startDate = document.getElementById('tgl-mulai');

    function addSlot(values) {
        const row = template.content.firstElementChild.cloneNode(true);
        const group = row.querySelector('[data-slot="jenis_sasaran"]');
        const target = row.querySelector('[data-slot="sasaran"]');
        function showTargets(selected) {
            target.replaceChildren(new Option('Pilih sasaran', ''));
            options[group.value].forEach(option => target.add(new Option(option.nama, option.id)));
            target.value = selected || '';
        }
        Object.keys(values).forEach(key => {
            const input = row.querySelector('[data-slot="' + key + '"]');
            if (input && input.type === 'checkbox') {
                input.checked = Number(values[key]) === 1;
            } else if (input) {
                input.value = key === 'jam_buka' ? values[key].slice(0, 5) : values[key];
            }
        });
        showTargets(values.sasaran);
        group.addEventListener('change', () => showTargets(''));
        row.querySelector('.remove-slot').addEventListener('click', () => row.remove());
        wrapper.appendChild(row);
    }

    saved.forEach(addSlot);
    document.getElementById('tambah-slot').addEventListener('click', () => addSlot({tanggal: startDate.value, urutan: 1}));
    document.getElementById('create').addEventListener('submit', () => {
        payload.value = JSON.stringify(Array.from(wrapper.children, row => {
            const slot = {};
            row.querySelectorAll('[data-slot]').forEach(input => {
                slot[input.dataset.slot] = input.type === 'checkbox' ? Number(input.checked) : input.value;
            });
            return slot;
        }));
        payload.disabled = false;
    }, true);
})();
</script>
