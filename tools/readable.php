<?php
// Print readable source of a function, decoding double-quoted PHP escapes.
$src = file_get_contents($argv[1]);
$want = $argv[2];
$tokens = token_get_all($src);
$n = count($tokens);
$startIdx = null; $endIdx = null;
for ($i = 0; $i < $n; $i++) {
    if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) continue;
    $j = $i + 1;
    while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
    if ($j >= $n || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) continue;
    if (strcasecmp($tokens[$j][1], $want) !== 0) continue;
    $startIdx = $i;
    $depth = 0; $started = false;
    for ($k = $j; $k < $n; $k++) {
        $s = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
        if ($s === '{') { $started = true; $depth++; }
        elseif ($s === '}') { $depth--; if ($started && $depth === 0) break; }
    }
    $endIdx = $k; break;
}
if ($startIdx === null) { fwrite(STDERR, "not found\n"); exit(1); }
$out = '';
for ($i = $startIdx; $i <= $endIdx; $i++) {
    $tok = $tokens[$i];
    if (is_array($tok) && $tok[0] === T_CONSTANT_ENCAPSED_STRING) {
        $s = $tok[1];
        if ($s[0] === '"') {
            $decoded = stripcslashes(substr($s, 1, -1));
            $decoded = addcslashes($decoded, "\"\\");
            $out .= '"' . $decoded . '"';
        } else {
            $out .= $s;
        }
        continue;
    }
    if (is_array($tok) && $tok[0] === T_WHITESPACE) { $out .= ' '; continue; }
    $out .= is_array($tok) ? $tok[1] : $tok;
}
// Light pretty print: newline after '{' '}' ';'
$out = preg_replace('/\s+/', ' ', $out);
$out = str_replace(array('{ ', '} ', '; '), array("{\n", "}\n", ";\n"), $out);
echo $out . "\n";