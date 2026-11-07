<?php
declare(strict_types=1);

require dirname(__DIR__) . '/application/libraries/Environment_database_installer.php';

function expect($condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$installer = new Environment_database_installer();
$base = 'codex_installer_' . strtolower(bin2hex(random_bytes(4)));
$targets = $installer->targetNames($base);
$config = array('hostname' => 'localhost', 'username' => 'root', 'password' => '', 'database' => $base);
$server = new mysqli('localhost', 'root', '', '');

try {
    expect($targets['development'] === $base . '_development', 'Nama development salah');
    expect($targets['production'] === $base . '_production', 'Nama production salah');
    $rendered = $installer->renderConfiguration(array(
        'hostname' => 'local\'host',
        'username' => 'root',
        'password' => 'pa\'ss\\word',
        'database' => $base,
    ));
    define('BASEPATH', dirname(__DIR__) . '/system/');
    define('ENVIRONMENT', 'development');
    eval('?>' . $rendered);
    expect($db['default']['hostname'] === 'local\'host', 'Hostname tidak dirender aman');
    expect($db['default']['password'] === 'pa\'ss\\word', 'Password tidak dirender aman');
    unset($db);
    foreach (array('nama-db', 'nama development', 'nama_production') as $invalid) {
        try {
            $installer->targetNames($invalid);
            throw new RuntimeException('Nama tidak valid diterima: ' . $invalid);
        } catch (InvalidArgumentException $e) {
        }
    }

    $installer->install($config);
    foreach ($targets as $target) {
        $tables = (int) $server->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$target}' AND TABLE_TYPE='BASE TABLE'")->fetch_row()[0];
        $foreignKeys = (int) $server->query("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='{$target}'")->fetch_row()[0];
        expect($tables === 79, "Jumlah tabel {$target} bukan 79");
        expect($foreignKeys === 10, "Foreign key {$target} bukan 10");
    }

    $dev = new mysqli('localhost', 'root', '', $targets['development']);
    $hash = password_hash('Admin#2026', PASSWORD_BCRYPT);
    $stmt = $dev->prepare("INSERT INTO users (ip_address,username,password,email,created_on,active,first_name,last_name) VALUES ('127.0.0.1','admin',?,'admin@example.test',?,1,'Admin','Test')");
    $created = time();
    $stmt->bind_param('si', $hash, $created);
    expect($stmt->execute(), 'Admin development gagal dibuat');
    $adminId = (int) $dev->insert_id;
    expect($dev->query("INSERT INTO users_groups (user_id,group_id) VALUES ({$adminId},1)"), 'Grup admin gagal dibuat');
    expect($dev->query("INSERT INTO setting (id_setting,sekolah,nama_aplikasi) VALUES (1,'Sekolah Test','CBT Test')"), 'Profil sekolah gagal dibuat');

    $devConfig = array('hostname' => 'localhost', 'username' => 'root', 'password' => '', 'database' => $targets['development']);
    $prodConfig = array('hostname' => 'localhost', 'username' => 'root', 'password' => '', 'database' => $targets['production']);
    expect($server->query("INSERT INTO `{$targets['production']}`.setting (id_setting,sekolah) VALUES (1,'Penghalang')"), 'Gagal membuat kondisi finalisasi gagal');
    expect(!$installer->finalize($devConfig, $prodConfig, $adminId), 'Finalisasi menerima production yang sudah berisi data');
    expect((int) $server->query("SELECT COUNT(*) FROM `{$targets['development']}`.users")->fetch_row()[0] === 0, 'Admin development tidak dibersihkan setelah finalisasi gagal');
    expect($server->query("DELETE FROM `{$targets['production']}`.setting"), 'Gagal membersihkan kondisi uji');

    $stmt->execute();
    $adminId = (int) $dev->insert_id;
    expect($dev->query("INSERT INTO users_groups (user_id,group_id) VALUES ({$adminId},1)"), 'Grup admin retry gagal dibuat');
    expect($dev->query("INSERT INTO setting (id_setting,sekolah,nama_aplikasi) VALUES (1,'Sekolah Test','CBT Test')"), 'Profil sekolah retry gagal dibuat');
    expect($installer->finalize($devConfig, $prodConfig, $adminId), 'Finalisasi dua database gagal');

    $checks = array(
        "SELECT COUNT(*) FROM `{$targets['development']}`.users" => 37,
        "SELECT COUNT(*) FROM `{$targets['development']}`.master_guru" => 6,
        "SELECT COUNT(*) FROM `{$targets['development']}`.master_siswa" => 30,
        "SELECT COUNT(*) FROM `{$targets['development']}`.master_kelas" => 6,
        "SELECT COUNT(*) FROM `{$targets['development']}`.cbt_bank_soal" => 2,
        "SELECT COUNT(*) FROM `{$targets['production']}`.users" => 1,
        "SELECT COUNT(*) FROM `{$targets['production']}`.setting" => 1,
        "SELECT COUNT(*) FROM `{$targets['production']}`.master_guru" => 0,
        "SELECT COUNT(*) FROM `{$targets['production']}`.master_siswa" => 0,
        "SELECT COUNT(*) FROM `{$targets['production']}`.cbt_bank_soal" => 0,
        "SELECT COUNT(*) FROM `{$targets['development']}`.migrations WHERE version=20261103000100" => 1,
        "SELECT COUNT(*) FROM `{$targets['production']}`.migrations WHERE version=20261103000100" => 1,
    );
    foreach ($checks as $sql => $expected) {
        expect((int) $server->query($sql)->fetch_row()[0] === $expected, 'Hitungan tidak sesuai: ' . $sql);
    }

    try {
        $installer->install($config);
        throw new RuntimeException('Installer menimpa target yang sudah ada');
    } catch (RuntimeException $e) {
        expect(strpos($e->getMessage(), 'sudah ada') !== false, 'Penolakan target existing tidak jelas');
    }
    echo "PASS: installer dua database aman dan terisolasi\n";
} finally {
    foreach ($targets as $target) {
        if (strpos($target, 'codex_installer_') === 0) {
            $server->query("DROP DATABASE IF EXISTS `{$target}`");
        }
    }
    $server->close();
}
