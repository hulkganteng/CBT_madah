<div class="content-wrapper bg-white pt-4">
<section class="content-header"><div class="container-fluid"><h1>Peminatan Siswa</h1></div></section>
<section class="content"><div class="container-fluid"><div class="card my-shadow"><div class="card-body">
<?php if ($message = $this->session->flashdata('success')): ?><div class="alert alert-success" role="status"><?= html_escape($message) ?></div><?php endif; ?>
<?php if ($message = $this->session->flashdata('error')): ?><div class="alert alert-danger" role="alert"><?= html_escape($message) ?></div><?php endif; ?>
<?php if (!$periodAvailable): ?><div class="alert alert-warning">Tahun pelajaran atau semester aktif belum ditentukan; filter rombel menggunakan periode aktif.</div><?php endif; ?>
<form method="get" action="<?= site_url('peminatansiswa') ?>" class="mb-3">
<div class="form-row">
<div class="form-group col-md-3"><label for="search">Pencarian</label><input id="search" name="q" class="form-control" value="<?= html_escape($filters['search']) ?>" placeholder="Nama, NIS, atau NISN"></div>
<div class="form-group col-md-2"><label for="filter-level">Tingkat</label><select id="filter-level" name="level" class="form-control"><option value="">Semua tingkat</option><?php foreach (array('10', '11', '12') as $level): ?><option value="<?= $level ?>" <?= $filters['level'] === $level ? 'selected' : '' ?>><?= $level ?></option><?php endforeach; ?></select></div>
<div class="form-group col-md-2"><label for="filter-class">Rombel aktif</label><select id="filter-class" name="class" class="form-control"><option value="">Semua rombel</option><option value="none" <?= $filters['class'] === 'none' ? 'selected' : '' ?>>Tanpa rombel</option><?php foreach ($classes as $class): ?><option value="<?= (int) $class->id_kelas ?>" <?= $filters['class'] === (string) $class->id_kelas ? 'selected' : '' ?>><?= html_escape($class->nama_kelas) ?></option><?php endforeach; ?></select></div>
<div class="form-group col-md-2"><label for="filter-major">Peminatan</label><select id="filter-major" name="major" class="form-control"><option value="">Semua peminatan</option><option value="none" <?= $filters['major'] === 'none' ? 'selected' : '' ?>>Belum diisi</option><?php foreach ($majors as $major): ?><option value="<?= (int) $major->id_jurusan ?>" <?= $filters['major'] === (string) $major->id_jurusan ? 'selected' : '' ?>><?= html_escape($major->nama_jurusan) ?><?= !$major->status ? ' (nonaktif)' : '' ?></option><?php endforeach; ?></select></div>
<div class="form-group col-md-2"><label for="filter-program">Program MAK</label><select id="filter-program" name="program" class="form-control"><option value="">Semua program</option><?php foreach (array('TAHFIDZ', 'KITAB') as $program): ?><option value="<?= $program ?>" <?= $filters['program'] === $program ? 'selected' : '' ?>><?= ucfirst(strtolower($program)) ?></option><?php endforeach; ?></select></div>
<div class="form-group col-md-1"><label for="per-page">Per halaman</label><select id="per-page" name="per_page" class="form-control"><?php foreach (array(10, 25, 50, 100) as $size): ?><option value="<?= $size ?>" <?= $filters['perPage'] === $size ? 'selected' : '' ?>><?= $size ?></option><?php endforeach; ?></select></div>
</div><button class="btn btn-primary" type="submit">Terapkan filter</button> <a class="btn btn-outline-secondary" href="<?= site_url('peminatansiswa') ?>">Reset</a>
</form>
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Nama</th><th>NIS / NISN</th><th>Rombel</th><th>Peminatan</th><th>Program MAK</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($students as $student): ?>
<tr><td><?= html_escape($student->nama) ?></td><td><?= html_escape($student->nis) ?> / <?= html_escape($student->nisn) ?></td><td><?= html_escape($student->nama_kelas ?: ($periodAvailable ? 'Tanpa rombel' : 'Periode belum aktif')) ?></td><td><?= html_escape($student->nama_jurusan ?: 'Belum diisi') ?></td><td><?= html_escape($student->program_mak ?: '-') ?></td><td class="text-nowrap">
<button type="button" class="btn btn-sm btn-primary edit-major" data-toggle="modal" data-target="#major-modal" data-id="<?= (int) $student->id_siswa ?>" data-name="<?= html_escape($student->nama) ?>" data-major="<?= (int) $student->peminatan_id ?>" data-program="<?= html_escape($student->program_mak ?: '') ?>"><?= $student->peminatan_id ? 'Ubah' : 'Tetapkan' ?></button>
<?php if ($student->peminatan_id): ?>
<?= form_open('peminatansiswa/remove?' . http_build_query($query + array('page' => $page)), array('class' => 'd-inline', 'onsubmit' => "return confirm('Hapus peminatan siswa ini? Data siswa dan rombel tetap ada.')")) ?>
<input type="hidden" name="id_siswa" value="<?= (int) $student->id_siswa ?>"><button type="submit" class="btn btn-sm btn-danger">Hapus</button><?= form_close() ?>
<?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if (!$students): ?><tr><td colspan="6" class="text-center">Tidak ada siswa ditemukan.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="d-flex flex-wrap justify-content-between align-items-center">
<span>Menampilkan <?= $total ? (($page - 1) * $filters['perPage'] + 1) : 0 ?>–<?= min($page * $filters['perPage'], $total) ?> dari <?= (int) $total ?> siswa</span>
<nav aria-label="Halaman siswa"><ul class="pagination mb-0">
<?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="<?= html_escape(site_url('peminatansiswa') . '?' . http_build_query($query + array('page' => $page - 1))) ?>">Sebelumnya</a></li><?php endif; ?>
<?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?><li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" <?= $i === $page ? 'aria-current="page"' : '' ?> href="<?= html_escape(site_url('peminatansiswa') . '?' . http_build_query($query + array('page' => $i))) ?>"><?= $i ?></a></li><?php endfor; ?>
<?php if ($page < $pages): ?><li class="page-item"><a class="page-link" href="<?= html_escape(site_url('peminatansiswa') . '?' . http_build_query($query + array('page' => $page + 1))) ?>">Berikutnya</a></li><?php endif; ?>
</ul></nav></div></div></div></div></section></div>
<div class="modal fade" id="major-modal" tabindex="-1" role="dialog" aria-labelledby="major-title" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content">
<?= form_open('peminatansiswa/save?' . http_build_query($query + array('page' => $page))) ?>
<div class="modal-header"><h5 class="modal-title" id="major-title">Peminatan Siswa</h5><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
<div class="modal-body"><p id="student-name"></p><input type="hidden" name="id_siswa" id="student-id">
<div class="form-group"><label for="major">Peminatan</label><select id="major" name="peminatan_id" class="form-control" required><option value="">Pilih peminatan</option><?php foreach ($majors as $major): if (!$major->status) continue; ?><option value="<?= (int) $major->id_jurusan ?>" data-code="<?= html_escape(strtoupper($major->kode_jurusan)) ?>"><?= html_escape($major->nama_jurusan) ?></option><?php endforeach; ?></select></div>
<div class="form-group" id="program-group"><label for="program">Program MAK</label><select id="program" name="program_mak" class="form-control"><option value="">Pilih program MAK</option><option value="TAHFIDZ">Tahfidz</option><option value="KITAB">Kitab</option></select></div>
<small class="text-muted">Siswa kelas X mengikuti peminatan Umum sesuai aturan aplikasi.</small></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div><?= form_close() ?>
</div></div></div>
<script>
$(function () {
    function syncProgram() {
        var mak = $('#major option:selected').attr('data-code') === 'MAK';
        $('#program-group').toggle(mak);
        $('#program').prop('required', mak);
        if (!mak) $('#program').val('');
    }
    $('#major').on('change', syncProgram);
    $('.edit-major').on('click', function () {
        var button = $(this);
        $('#student-id').val(button.attr('data-id'));
        $('#student-name').text(button.attr('data-name'));
        $('#major').val(button.attr('data-major') || '');
        $('#program').val(button.attr('data-program') || '');
        syncProgram();
    });
});
</script>
