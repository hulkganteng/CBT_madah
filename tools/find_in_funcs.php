<?php
// Find which functions in a file contain a given substring.
$file = $argv[1];
$src = file_get_contents($file);
$needle = $argv[2];
$tok = token_get_all($src);
$n = count($tok);
$off = 0;
$map = array();
foreach ($tok as $i => $t) { $map[$i] = $off; $off += strlen(is_array($t) ? $t[1] : $t); }
$funcs = array();
for ($i = 0; $i < $n; $i++) {
    if (!is_array($tok[$i]) || $tok[$i][0] !== T_FUNCTION) continue;
    $j = $i + 1;
    while ($j < $n && is_array($tok[$j]) && $tok[$j][0] === T_WHITESPACE) $j++;
    if ($j >= $n || !is_array($tok[$j]) || $tok[$j][0] !== T_STRING) continue;
    $name = $tok[$j][1];
    $depth = 0; $started = false;
    for ($k = $j; $k < $n; $k++) {
        $s = is_array($tok[$k]) ? $tok[$k][1] : $tok[$k];
        if ($s === '{') { $started = true; $depth++; }
        elseif ($s === '}') { $depth--; if ($started && $depth === 0) break; }
    }
    $body = substr($src, $map[$i], $map[$k] + 1 - $map[$i]);
    if (strpos($body, $needle) !== false) {
        echo $name . "\n";
    }
}