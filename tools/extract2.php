<?php
// Usage: php extract2.php <file> <funcName>
// Prints function source by token offsets (works for single-line files).
$src = file_get_contents($argv[1]);
$want = $argv[2];
$tokens = token_get_all($src);
$n = count($tokens);
$offsets = array();
$off = 0;
foreach ($tokens as $idx => $tok) {
    $offsets[$idx] = $off;
    $off += strlen(is_array($tok) ? $tok[1] : $tok);
}
for ($i = 0; $i < $n; $i++) {
    if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) continue;
    $j = $i + 1;
    while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
    if ($j >= $n || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) continue;
    if (strcasecmp($tokens[$j][1], $want) !== 0) continue;
    $depth = 0; $started = false;
    for ($k = $j; $k < $n; $k++) {
        $s = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
        if ($s === '{') { $started = true; $depth++; }
        elseif ($s === '}') { $depth--; if ($started && $depth === 0) break; }
    }
    $start = $offsets[$i];
    $end = $offsets[$k] + 1;
    echo substr($src, $start, $end - $start) . "\n";
    exit(0);
}
fwrite(STDERR, "Function not found: $want\n");
exit(1);