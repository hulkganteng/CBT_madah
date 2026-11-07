<?php

class Environment_database_installer
{
    private $root;

    public function __construct()
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function targetNames($base)
    {
        $base = trim((string) $base);
        if (!preg_match('/^[A-Za-z0-9_]+$/', $base)
            || preg_match('/_(development|production)$/i', $base)) {
            throw new InvalidArgumentException('Nama dasar database hanya boleh berisi huruf, angka, dan garis bawah tanpa akhiran environment.');
        }

        return array(
            'development' => $base . '_development',
            'production' => $base . '_production',
        );
    }

    public function install(array $data)
    {
        foreach (array('hostname', 'username', 'database') as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException('Host, username, dan nama dasar database wajib diisi.');
            }
        }

        $targets = $this->targetNames($data['database']);
        $connection = $this->connect($data, '');
        $created = array();

        try {
            foreach ($targets as $target) {
                $escaped = $connection->real_escape_string($target);
                $result = $connection->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='{$escaped}'");
                if (!$result || $result->num_rows > 0) {
                    throw new RuntimeException("Database {$target} sudah ada. Installer tidak menimpa database lama.");
                }
            }

            foreach ($targets as $target) {
                if (!$connection->query("CREATE DATABASE `{$target}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
                    throw new RuntimeException("Gagal membuat database {$target}: {$connection->error}");
                }
                $created[] = $target;
                $this->importSql($data, $target, $this->root . '/assets/app/db/master.sql');
            }
        } catch (Throwable $e) {
            foreach (array_reverse($created) as $target) {
                $connection->query("DROP DATABASE `{$target}`");
            }
            $connection->close();
            throw $e;
        }

        $connection->close();
        return $targets;
    }

    public function installAndConfigure(array $data)
    {
        $targets = $this->install($data);
        try {
            $this->writeConfiguration($data);
        } catch (Throwable $e) {
            $connection = $this->connect($data, '');
            foreach ($targets as $target) {
                $connection->query("DROP DATABASE `{$target}`");
            }
            $connection->close();
            throw $e;
        }
        return $targets;
    }

    public function writeConfiguration(array $data)
    {
        $rendered = $this->renderConfiguration($data);
        if (file_put_contents($this->root . '/application/config/database.php', $rendered, LOCK_EX) === false
            || file_put_contents($this->root . '/.env', "CI_ENV=development\n", LOCK_EX) === false) {
            throw new RuntimeException('Konfigurasi database atau .env tidak dapat ditulis.');
        }

        return true;
    }

    public function renderConfiguration(array $data)
    {
        $targets = $this->targetNames($data['database']);
        $template = file_get_contents($this->root . '/assets/app/db/database.php');
        if ($template === false) {
            throw new RuntimeException('Template konfigurasi database tidak ditemukan.');
        }

        $rendered = strtr($template, array(
            '%HOSTNAME%' => var_export((string) $data['hostname'], true),
            '%USERNAME%' => var_export((string) $data['username'], true),
            '%PASSWORD%' => var_export(isset($data['password']) ? (string) $data['password'] : '', true),
            '%DATABASE_DEVELOPMENT%' => var_export($targets['development'], true),
            '%DATABASE_PRODUCTION%' => var_export($targets['production'], true),
        ));
        return $rendered;
    }

    public function finalize(array $development, array $production, $adminId)
    {
        $adminId = (int) $adminId;
        if ($adminId <= 0) {
            return false;
        }

        $devName = $this->targetNames(preg_replace('/_development$/', '', $development['database']))['development'];
        $prodName = $this->targetNames(preg_replace('/_production$/', '', $production['database']))['production'];
        $connection = $this->connect($development, $devName);

        try {
            $admin = $connection->query("SELECT u.id FROM `{$devName}`.users u JOIN `{$devName}`.users_groups ug ON ug.user_id=u.id AND ug.group_id=1 WHERE u.id={$adminId}");
            if (!$admin || $admin->num_rows !== 1) {
                throw new RuntimeException('Akun administrator hasil onboarding tidak valid.');
            }
            foreach (array('users', 'setting') as $table) {
                $count = $connection->query("SELECT COUNT(*) FROM `{$prodName}`.`{$table}`")->fetch_row()[0];
                if ((int) $count !== 0) {
                    throw new RuntimeException('Production sudah berisi akun atau profil sekolah.');
                }
            }

            $connection->begin_transaction();
            $queries = array(
                "INSERT INTO `{$prodName}`.users SELECT * FROM `{$devName}`.users WHERE id={$adminId}",
                "INSERT INTO `{$prodName}`.users_groups SELECT * FROM `{$devName}`.users_groups WHERE user_id={$adminId} AND group_id=1",
                "INSERT INTO `{$prodName}`.users_profile SELECT * FROM `{$devName}`.users_profile WHERE id_user={$adminId}",
                "INSERT INTO `{$prodName}`.setting SELECT * FROM `{$devName}`.setting",
            );
            foreach ($queries as $query) {
                if (!$connection->query($query)) {
                    throw new RuntimeException($connection->error);
                }
            }
            $this->executeSql($connection, file_get_contents($this->root . '/assets/app/db/development-seed.sql'));
            $connection->commit();
            $connection->close();
            return true;
        } catch (Throwable $e) {
            $connection->rollback();
            $this->removeIncompleteOnboarding($connection, $devName, $adminId);
            $connection->close();
            if (function_exists('log_message')) {
                log_message('error', 'Finalisasi installer dua database gagal: ' . $e->getMessage());
            }
            return false;
        }
    }

    private function removeIncompleteOnboarding(mysqli $connection, $database, $adminId)
    {
        $connection->begin_transaction();
        $connection->query("DELETE FROM `{$database}`.users_profile WHERE id_user={$adminId}");
        $connection->query("DELETE FROM `{$database}`.users_groups WHERE user_id={$adminId}");
        $connection->query("DELETE FROM `{$database}`.users WHERE id={$adminId}");
        $connection->query("DELETE FROM `{$database}`.setting");
        $connection->commit();
    }

    private function importSql(array $data, $database, $path)
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException("Template SQL tidak ditemukan: {$path}");
        }
        $connection = $this->connect($data, $database);
        try {
            $this->executeSql($connection, $sql);
        } finally {
            $connection->close();
        }
    }

    private function executeSql(mysqli $connection, $sql)
    {
        if (!is_string($sql) || trim($sql) === '' || !$connection->multi_query($sql)) {
            throw new RuntimeException('Impor SQL gagal: ' . $connection->error);
        }
        do {
            if ($result = $connection->store_result()) {
                $result->free();
            }
            if ($connection->errno) {
                throw new RuntimeException('Impor SQL gagal: ' . $connection->error);
            }
        } while ($connection->more_results() && $connection->next_result());
        if ($connection->errno) {
            throw new RuntimeException('Impor SQL gagal: ' . $connection->error);
        }
    }

    private function connect(array $data, $database)
    {
        mysqli_report(MYSQLI_REPORT_OFF);
        $connection = new mysqli(
            $data['hostname'],
            $data['username'],
            isset($data['password']) ? $data['password'] : '',
            $database
        );
        if ($connection->connect_errno) {
            throw new RuntimeException('Koneksi database gagal: ' . $connection->connect_error);
        }
        $connection->set_charset('utf8mb4');
        return $connection;
    }
}
