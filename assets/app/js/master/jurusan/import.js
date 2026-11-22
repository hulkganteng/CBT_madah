$(document).ready(function () {
    ajaxcsrf();
    initMasterImport('datajurusan', 'preview', [
        ['nama', 'Nama Jurusan'],
        ['kode', 'Kode Jurusan']
    ]);
});
