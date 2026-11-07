<?php
error_reporting(0);
$db_config_path = '../application/config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST) {

    require_once('includes/taskCoreClass.php');
    require_once('includes/databaseLibrary.php');

    $core = new Core();
    $database = new Database();

    if ($core->checkEmpty($_POST) == true) {
        try {
            $database->install($_POST);
        } catch (Throwable $e) {
            $message = $core->show_message('error',
                "ERROR#001<br>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
        if (!isset($message) && $core->checkFile() == false) {
            $message = $core->show_message('error',
                "ERROR#003<br>File application/config/database.php tidak ditemukan");
        }
    } else {
        $message = $core->show_message('error',
            "ERROR#005<br>Gagal membuat database, pastikan semua parameter diisi dengan benar.");
    }

    echo $message;
}
?>
