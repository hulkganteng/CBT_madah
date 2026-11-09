<?php
// Print windows around each occurrence of needle, decoding string escapes.
$file = $argv[1]; $needle = $argv[2]; $ctx = isset($argv[3]) ? (int)$argv[3] : 160;
$src = file_get_contents($file);
// decode all double-quoted strings first for readability
$tok = token_get_all($src);
$decoded = '';
foreach ($tok as $t) {
    if (is_array($t) && $t[0] === T_CONSTANT_ENCAPSED_STRING) {
        $s = $t[1];
        if (strlen($s) > 1 && $s[0] === '"') {
            $s = '"' . addcslashes(stripcslashes(substr($s, 1, -1)), "\"\\") . '"';
        }
        $decoded .= $s;
    } else {
        $decoded .= is_array($t) ? $t[1] : $t;
    }
}
$pos = 0; $count = 0;
while (($pos = strpos($decoded, $needle, $pos)) !== false) {
    $count++;
    $start = max(0, $pos - $ctx);
    echo "--- #$count @ $pos ---\n";
    echo substr($decoded, $start, $ctx * 2) . "\n";
    $pos += strlen($needle);
}
echo "total: $count\n";