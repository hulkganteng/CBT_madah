<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-teal my-shadow">
    <!-- Brand Logo -->
    <a href="<?= base_url(); ?>" class="brand-link bg-white">
        <?php $logo_app = $setting->logo_kiri == null ? base_url() . 'assets/img/favicon.png' : base_url() . $setting->logo_kiri; ?>
        <img src="<?= $logo_app ?>" alt="App Logo" class="brand-image"
             style="opacity: .8">
        <span class="brand-text text-sm"><?= $setting->nama_aplikasi ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <?php
        /* Administrator tidak punya baris master_guru, jadi controller mengirim
           $guru = null. Sidebar tetap harus dirender tanpa warning. */
        $sidebar_guru = isset($guru) && $guru ? $guru : null;
        $sidebar_foto = $sidebar_guru && $sidebar_guru->foto != null
            ? base_url() . $sidebar_guru->foto : base_url('assets/img/user.jpg');
        $sidebar_nama = $sidebar_guru ? $sidebar_guru->nama_guru : 'Administrator';
        $sidebar_jabatan = $sidebar_guru && $sidebar_guru->id_jabatan != null ? $sidebar_guru->id_jabatan : '';
        ?>
        <div class="user-panel mt-3 d-flex">
            <div class="image">
                <img src="<?= $sidebar_foto ?>"
                     class="img-circle elevation-2" alt="User Image" style="height: 40px; width: 40px">
            </div>
            <div class="info">
                <a href="#" class="d-block text-wrap">
                    <p><?= $sidebar_nama ?></p>
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2 mb-5">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
                id="tree-menus" data-accordion="false">
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<script>
    const page = '<?= $this->uri->segment(1)?>';
    const jabatan = '<?=$sidebar_jabatan?>';
    const pageact = '<?= $this->uri->segment(2); ?>';
    const isTu = <?= $this->ion_auth->in_group('tu') ? 'true' : 'false' ?>;
    const isAdmin = <?= $this->ion_auth->is_admin() ? 'true' : 'false' ?>;
    const menus = [
        {
            'header': 'HOME', 'cbt': '1',
            'menu': [
                {'name': 'Beranda', 'link': 'dashboard', 'icon': 'fas fa-desktop', 'cbt': '1'},
                {'name': 'Profile', 'link': 'guruview', 'icon': 'fas fa-user', 'cbt': '1'},
                {'name': 'Pengumuman', 'link': 'pengumuman', 'icon': 'fas fa-bullhorn', 'cbt': '1'},
                {
                    'name': 'Wali Kelas', 'icon': 'fas fa-chart-pie', 'cbt': '1', 'wali': true,
                    'submenu': [
                        {'name': 'Siswa', 'link':"walisiswa", 'icon': 'fas fa-users'},
                        {'name': 'Struktur', 'link':"walistruktur", 'icon': 'far fa-circle'}
                    ]
                },
                {
                    'name': 'Tata Usaha (TU)', 'icon': 'fas fa-users-cog', 'cbt': '1', 'tu': true,
                    'submenu': [
                        {'name': 'Kelola Siswa', 'link':"datasiswa", 'icon': 'fa fa-users'},
                        {'name': 'Rombel', 'link':"datakelas", 'icon': 'fas fa-school', 'admin': true},
                        {'name': 'Penempatan Siswa', 'link':"penempatansiswa", 'icon': 'fas fa-random', 'admin': true},
                        {'name': 'Akun Siswa', 'link':"usersiswa", 'icon': 'fas fa-user-lock'}
                    ]
                },
                {
                    'name': 'Ulangan / Ujian', 'icon': 'fa fa-user-graduate', 'cbt': '1',
                    'submenu': [
                        {'name':"Bank Soal", 'link':"cbtbanksoal", 'icon': 'far fa-folder-open'},
                        {'name':"Jadwal", 'link':"cbtjadwal", 'icon': 'far fa-calendar-alt'},
                        {'name': 'Cetak', 'link':"cbtcetak", 'icon': 'fa fa-print'},
                        {'name': 'Status Siswa', 'link':"cbtstatus", 'icon': 'fa fa-user-clock'},
                        {'name': 'Hasil Ujian', 'link':"cbtnilai", 'icon': 'fa fa-file-alt'},
                        {'name': 'Analisis Soal', 'link':"cbtanalisis", 'icon': 'fa fa-chart-line'},
                        {'name': 'Rekap Nilai', 'link':"cbtrekap", 'icon': 'fas fa-trophy'},
                    ]
                },
            ]
        },
        {'name': 'LOGOUT', 'link': '', 'icon': 'fas fa-sign-out-alt', 'cbt': '1'},
    ];

    const isLogin = localStorage.getItem('garudaCBT.login')
    const isCbtMode = isLogin ? isLogin === '1' : false
    let htmlMenu = '';
    menus.forEach(function (header) {
        console.log(header)
        if (isCbtMode && header.cbt === '0') {
            return
        }
        if (jabatan !== '4' && header.wali) {
            return
        }
        if (header.header) {
            htmlMenu += `<li class="nav-header">${header.header}</li>`;
            header.menu.forEach(function (menu) {
                if (isCbtMode && menu.cbt === '0') {
                    return
                }
                if (jabatan !== '4' && menu.wali) {
                    return
                }
                if (!isTu && !isAdmin && menu.tu) {
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
                        if (sub.admin && !isAdmin) return;
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
