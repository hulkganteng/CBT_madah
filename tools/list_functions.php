<?php
$src = file_get_contents($argv[1]);
$tokens = token_get_all($src);
$n = count($tokens);
for ($i = 0; $i < $n; $i++) {
    if (is_array($tokens[$i]) && $tokens[$i][0] === T_FUNCTION) {
        $j = $i + 1;
        while ($j < $n && is_array($tokens[$j]) && in_array($tokens[$j][0], array(T_WHITESPACE), true)) $j++;
        if ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
            echo $tokens[$j][1] . " @line " . $tokens[$j][2] . "\n";
        }
    }
}