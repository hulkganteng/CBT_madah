<?php
foreach (array('', ' ', '0', '0000-00-00 00:00:00', 'abc', '2026-11-09 10:00:00') as $v) {
    try {
        $d = new DateTime($v);
        echo json_encode($v), ' OK ', $d->format('c'), PHP_EOL;
    } catch (Throwable $e) {
        echo json_encode($v), ' THROW ', get_class($e), ': ', $e->getMessage(), PHP_EOL;
    }
}
echo PHP_VERSION, PHP_EOL;