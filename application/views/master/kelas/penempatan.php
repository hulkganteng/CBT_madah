<div class="content-wrapper bg-white pt-4"><section class="content-header"><div class="container-fluid"><h1>Rombel / Penempatan Siswa</h1></div></section>
<section class="content"><div class="container-fluid"><div class="card my-shadow"><div class="card-body">
<div class="form-row mb-3"><div class="col-md-3"><label>Rombel tujuan</label><select id="target" class="form-control"><option value="">Pilih rombel</option><?php foreach ($kelas as $row): ?><option value="<?= (int)$row->id_kelas ?>"><?= html_escape($row->nama_kelas) ?></option><?php endforeach ?></select></div>
<div class="col-md-2"><label>Mode</label><select id="mode" class="form-control"><option value="unassigned">Siswa Tanpa Kelas</option><option value="move">Pindahkan Siswa</option></select></div>
<div class="col-md-2"><label>Peminatan</label><select id="major" class="form-control"><option value="">Semua</option><?php foreach ($peminatan as $row): ?><option value="<?= (int)$row->id_jurusan ?>"><?= html_escape($row->nama_jurusan) ?></option><?php endforeach ?></select></div>
<div class="col-md-2"><label>Program</label><select id="program" class="form-control"><option value="">Semua</option><option value="TAHFIDZ">Tahfidz</option><option value="KITAB">Kitab</option></select></div>
<div class="col-md-3"><label>Pencarian</label><input id="search" class="form-control" placeholder="Nama, NIS, atau NISN"></div></div>
<div class="row"><div class="col-lg-7"><h6>Kandidat siswa</h6><form id="placement-form"><div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th></th><th>Nama</th><th>Peminatan</th><th>Rombel saat ini</th></tr></thead><tbody id="candidates"><tr><td colspan="4">Pilih rombel tujuan.</td></tr></tbody></table></div><button class="btn btn-primary" type="submit">Tempatkan siswa terpilih</button></form></div>
<div class="col-lg-5"><h6>Isi rombel tujuan</h6><div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Nama</th><th>Peminatan</th><th></th></tr></thead><tbody id="roster"><tr><td colspan="3">Pilih rombel tujuan.</td></tr></tbody></table></div></div></div>
</div></div></div></section></div>
<script>
$(function(){ ajaxcsrf();
 function esc(v){return $('<div>').text(v||'').html();}
 function load(){var target=$('#target').val(); if(!target)return; $.post(base_url+'penempatansiswa/data',{id_kelas:target,mode:$('#mode').val(),peminatan_id:$('#major').val(),program_mak:$('#program').val(),search:$('#search').val()},function(d){
  var c=d.candidates.map(function(s){return '<tr><td><input type="checkbox" name="siswa[]" value="'+s.id_siswa+'"></td><td>'+esc(s.nama)+'<br><small>'+esc(s.nis)+'</small></td><td>'+esc(s.nama_jurusan||'Belum diisi')+' '+esc(s.program_mak||'')+'</td><td>'+esc(s.nama_kelas||'Tanpa Kelas')+'</td></tr>';}).join(''); $('#candidates').html(c||'<tr><td colspan="4">Tidak ada kandidat.</td></tr>');
  var r=d.roster.map(function(s){return '<tr><td>'+esc(s.nama)+'<br><small>'+esc(s.nis)+'</small></td><td>'+esc(s.nama_jurusan||'Belum diisi')+' '+esc(s.program_mak||'')+'</td><td><button class="btn btn-xs btn-danger remove" data-id="'+s.id_siswa+'">Keluarkan</button></td></tr>';}).join(''); $('#roster').html(r||'<tr><td colspan="3">Rombel masih kosong.</td></tr>');
 },'json');}
 $('#target,#mode,#major,#program').change(load); $('#search').on('keyup',load);
 $('#placement-form').submit(function(e){e.preventDefault();var ids=$(this).serializeArray();if(!ids.length)return swal.fire('Pilih siswa','Pilih minimal satu siswa.','warning');swal.fire({title:$('#mode').val()==='move'?'Pindahkan siswa?':'Tempatkan siswa?',icon:'question',showCancelButton:true}).then(function(x){if(!x.value)return;$.post(base_url+'penempatansiswa/save',$(e.target).serialize()+'&id_kelas='+$('#target').val()+'&mode='+$('#mode').val(),function(d){swal.fire(d.status?'Berhasil':'Gagal',d.message,d.status?'success':'error');if(d.status)load();},'json');});});
 $(document).on('click','.remove',function(){var id=$(this).data('id');swal.fire({title:'Keluarkan siswa dari rombel?',icon:'warning',showCancelButton:true}).then(function(x){if(!x.value)return;$.post(base_url+'penempatansiswa/remove',{id_siswa:id},function(d){swal.fire(d.status?'Berhasil':'Gagal',d.message,d.status?'success':'error');if(d.status)load();},'json');});});
});
</script>
