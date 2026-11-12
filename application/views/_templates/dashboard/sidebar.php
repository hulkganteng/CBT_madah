<!-- Main Sidebar Container -->
<?php
/* Beberapa halaman (mis. Pemulihan Database) tidak mengirim $setting.
   Sidebar jangan gagal hanya karena branding tidak lengkap. */
$settingSidebar = isset($setting) ? $setting : null;
$namaAplikasi = $settingSidebar == null || $settingSidebar->nama_aplikasi == null ? 'Garuda CBT' : $settingSidebar->nama_aplikasi;
?>
<aside class="main-sidebar sidebar-light-teal my-shadow">
    <!-- Brand Logo -->
    <a href="<?= base_url(); ?>" class="brand-link bg-white" aria-label="Beranda <?= html_escape($namaAplikasi) ?>">
        <?php $logo_app = $settingSidebar == null || $settingSidebar->logo_kiri == null ? base_url() . 'assets/img/favicon.png' : base_url() . $settingSidebar->logo_kiri; ?>
        <img src="<?= $logo_app ?>" alt="" class="brand-image"
             style="opacity: .8">
        <span class="brand-text font-weight-light"><?= $namaAplikasi ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <?php
        /* Profil user boleh belum diisi (mis. instalasi baru). Sidebar tetap harus
           tampil, jadi nilai null diganti default. */
        $profile = isset($profile) ? $profile : null;
        $isTuOnly = !$this->ion_auth->is_admin() && $this->ion_auth->in_group('tu');
        $avatar = ($profile == null || $profile->foto == null) ? base_url() . 'assets/adminlte/dist/img/avatar5.png' : base_url() . $profile->foto;
        $namaSidebar = ($profile == null || $profile->nama_lengkap == null) ? ($isTuOnly ? 'Tata Usaha' : 'NoName') : $profile->nama_lengkap;
        $jabatanSidebar = ($profile == null || $profile->jabatan == null) ? ($isTuOnly ? 'Tata Usaha' : 'Admin') : $profile->jabatan;
        ?>
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel border-0 d-flex mt-3 mb-3">
            <div class="image">
                <img src="<?= $avatar ?>" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block" style="line-height: 1">
                    <?= $namaSidebar ?>
                    <br>
                    <small class="text-muted">
                        <?= $jabatanSidebar ?>
                    </small>
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2 mb-5">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent"
                id="tree-menus" data-widget="treeview" role="menu" data-accordion="false">
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<script>
    const page = '<?= $this->uri->segment(1)?>';
    const pageact = '<?= $this->uri->segment(2); ?>';
    const isTuOnly = <?= $isTuOnly ? 'true' : 'false' ?>;
    const menus = isTuOnly ? [
        {
            'header': 'TATA USAHA', 'cbt': '1',
            'menu': [
                {'name': 'Beranda', 'link': 'dashboard', 'icon': 'fas fa-desktop', 'cbt': '1'},
                {
                    'name': 'Data Siswa', 'icon': 'fas fa-users', 'cbt': '1',
                    'submenu': [
                        {'name': 'Kelola Siswa', 'link': 'datasiswa', 'icon': 'fa fa-users'},
                        {'name': 'Akun Siswa', 'link': 'usersiswa', 'icon': 'fas fa-user-lock'}
                    ]
                }
            ]
        },
        {'name': 'LOGOUT', 'link': 'auth/logout', 'icon': 'fas fa-sign-out-alt', 'cbt': '1'}
    ] : [
        {
            'header': 'HOME', 'cbt': '1',
            'menu': [
                {'name': 'Dashboard', 'link': 'dashboard', 'icon': 'fas fa-desktop', 'cbt': '1'},
                {
                    'name': 'Data Umum', 'icon': 'fas fa-server', 'cbt': '1',
                    'submenu': [
                        {'name':"Tahun Pelajaran", 'link':"datatahun", 'icon': 'far fa-calendar-check'},
                        {'name':"Mata Pelajaran", 'link':"datamapel", 'icon': 'fa fa-book'},
                        {'name':"Jurusan", 'link':"datajurusan", 'icon': 'fa fa-flask'},
                        {'name':"Siswa", 'link':"datasiswa", 'icon': 'fa fa-users'},
                        {'name':"Kelas / Rombel", 'link':"datakelas", 'icon': 'fa fa-school'},
                        {'name':"Ekstrakurikuler", 'link':"dataekstra", 'icon': 'fa fa-chess'},
                        {'name':"Guru", 'link':"dataguru", 'icon': 'fa fa-chalkboard-teacher'}
                    ]
                },
                {
                    'name': 'Data Ujian', 'icon': 'fa fa-user-graduate', 'cbt': '1',
                    'submenu': [
                        {'name':"Jenis Ujian", 'link':"cbtjenis", 'icon': 'fa fa-project-diagram'},
                        {'name':"Sesi", 'link':"cbtsesi", 'icon': 'far fa-clock'},
                        {'name':"Ruang", 'link':"cbtruang", 'icon': 'fa fa-door-open'},
                        {'name':"Atur Ruang/Sesi", 'link':"cbtsesisiswa", 'icon': 'fa fa-user-clock'},
                        {'name':"Nomor Peserta", 'link':"cbtnomorpeserta", 'icon': 'far fa-id-card'},
                        {'name':"Bank Soal", 'link':"cbtbanksoal", 'icon': 'far fa-folder-open'},
                        {'name':"Jadwal", 'link':"cbtjadwal", 'icon': 'far fa-calendar-alt'},
                        {'name':"Alokasi Waktu", 'link':"cbtalokasi", 'icon': 'fa fa-clock-o'},
                        {'name':"Pengawas", 'link':"cbtpengawas", 'icon': 'fa fa-briefcase'},
                        {'name':"Token", 'link':"cbttoken", 'icon': 'fa fa-key'}
                    ]
                },
                {
                    'name': 'Pengumuman', 'link': 'pengumuman', 'icon': 'fas fa-bullhorn', 'cbt': '1'
                },
            ]
        },
        {
            'header': 'PELAKSANAAN', 'cbt': '1',
            'menu': [
                {
                    'name': 'Pelaksanaan Ujian', 'icon': 'fas fa-graduation-cap', 'cbt': '1',
                    'submenu': [
                        {'name': 'Cetak', 'link':"cbtcetak", 'icon': 'fa fa-print'},
                        {'name': 'Status Siswa', 'link':"cbtstatus", 'icon': 'fa fa-user-clock'},
                        {'name': 'Aktivitas Siswa', 'link':"examactivity", 'icon': 'fa fa-user-shield'},
                        {'name': 'Hasil Ujian', 'link':"cbtnilai", 'icon': 'fa fa-file-alt'},
                        {'name': 'Analisis Soal', 'link':"cbtanalisis", 'icon': 'fa fa-chart-line'},
                        {'name': 'Rekap Nilai', 'link':"cbtrekap", 'icon': 'fas fa-trophy'},
                    ]
                },
            ]
        },
        {
            'header': 'PENGATURAN',  'cbt': '1',
            'menu': [
                {'name': 'Profile Sekolah', 'link': 'settings', 'icon': 'fas fa-university', 'cbt': '1',},
                {'name': 'Generator SEB', 'link': 'sebconfig', 'icon': 'fas fa-shield-alt', 'cbt': '1'},
                {
                    'name': 'User Management', 'icon': 'fa fa-users-cog', 'cbt': '1',
                    'submenu': [
                        {'name': 'Administrator', 'link':"useradmin", 'icon': 'fas fa-cog'},
                        {'name': 'Tata Usaha', 'link':"usertu", 'icon': 'fas fa-user-shield'},
                        {'name': 'Guru', 'link':"userguru", 'icon': 'fas fa-user-tie'},
                        {'name': 'Siswa', 'link':"usersiswa", 'icon': 'fas fa-users'}
                    ]
                },
                {
                    'name': 'Database', 'icon': 'fa fa-users-cog', 'cbt': '1',
                    'submenu': [
                        {'name': 'Backup', 'link':"dbmanager", 'icon': 'fas fa-database'},
                        {'name': 'Pemulihan Database', 'link':"dbpemulihan", 'icon': 'fas fa-undo-alt'},
                        // Data Manager (dbclear) dihapus: drop seluruh tabel via GET.
                        //{'name': 'Update', 'link':"update", 'icon': ''}
                    ]
                },
            ]
        },
        {'name': 'LOGOUT', 'link': 'pengumuman', 'icon': 'fas fa-sign-out-alt',  'cbt': '1'},
    ];

    const isLogin = localStorage.getItem('garudaCBT.login')
    const isCbtMode = isLogin ? isLogin === '1' : false
    let htmlMenu = '';
    menus.forEach(function (header) {
        //console.log(header)
        if (isCbtMode && header.cbt === '0') {
            return
        }
        if (header.header) {
            htmlMenu += `<li class="nav-header">${header.header}</li>`;
            header.menu.forEach(function (menu) {
                if (isCbtMode && menu.cbt === '0') {
                    return
                }
                if (menu.submenu) {
                    var subs = menu.submenu.map(function(item) {
                        if (item['link'].includes('/')) {
                            return item['link'].split('/')[1]
                        } else return item['link'];
                    });
                    htmlMenu += `<li class="nav-item has-treeview ${subs.includes(pageact) || subs.includes(page) ? "menu-open" : ""}">
                    <a href="#" class="nav-link ${subs.includes(pageact) || subs.includes(page) ? "active" : ""}">
                        <i class="nav-icon ${menu.icon}"></i>
                        <p>${menu.name}<i class="fas fa-angle-left right"></i></p>
                    </a><ul class="nav nav-treeview">`;
                    menu.submenu.forEach(function (sub) {
                        htmlMenu += `<li class="nav-item">
                            <a href="${base_url + sub.link}"
                               class="nav-link ${page+'/'+pageact === sub.link || page === sub.link ? "active" : ""}">
                                <i class="${sub.icon} nav-icon"></i>
                                <p>${sub.name}</p>
                            </a>
                        </li>`;
                    })
                    htmlMenu += `</ul></li>`;
                } else {
                    htmlMenu += `<li class="nav-item"><a href="${base_url + menu.link}"
                       class="nav-link ${page === menu.link ? "active" : ""}">
                        <i class="nav-icon ${menu.icon}"></i>
                        <p>${menu.name}</p>
                    </a></li>`
                }
            })
        } else {
            htmlMenu += `<hr /><li class="nav-item">
                    <a href="#" onclick="logout()" class="nav-link">
                        <i class="${header.icon} nav-icon"></i>
                        <p>${header.name}</p>
                    </a>
                </li>`;
        }
    })
    $('#tree-menus').html(htmlMenu)
</script>
