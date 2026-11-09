<?php
// Extract named function source from a PHP file, ignoring goto/obfuscation.
$file = $argv[1];
$src = file_get_contents($file);
$tokens = token_get_all($src);
$want = isset($argv[2]) ? $argv[2] : null;
$n = count($tokens);
function tokstr($t) { return is_array($t) ? $t[1] : $t; }
for ($i = 0; $i < $n; $i++) {
    if (is_array($tokens[$i]) && $tokens[$i][0] === T_FUNCTION) {
        $j = $i + 1;
        while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
        if ($j >= $n || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) continue;
        $name = $tokens[$j][1];
        if ($want !== null && strcasecmp($want, $name) !== 0) continue;
        $k = $j;
        $depth = 0;
        $started = false;
        $startPos = null;
        for (; $k < $n; $k++) {
            $s = tokstr($tokens[$k]);
            if ($s === '{') {
                if (!$started) { $started = true; $startPos = is_array($tokens[$k]) ? $tokens[$k][2] : null; }
                $depth++;
            } elseif ($s === '}') {
                $depth--;
                if ($started && $depth === 0) break;
            }
        }
        if ($startPos === null) continue;
        $endPos = $tokens[$k][2] ?? null;
        // endPos is start line of '}' token; approximate using offsets instead.
        echo "=== $name ===\n";
        // Reconstruct from line numbers: use token line of '{' and '}'.
        $lines = explode("\n", $src);
        $startLine = $tokens[0][2] ?? 1;
        // find line index of startPos
        $offset = 0; $startLineNo = 1; $endLineNo = 1;
        $pos = 0;
        foreach ($lines as $ln => $text) {
            if ($pos + strlen($text) + 1 > $startPos && $startLineNo === 1) { $startLineNo = $ln; }
            if ($pos >= ($tokens[$k][2] ?? 0) && $endLineNo === 1 && $ln > $startLineNo) { $endLineNo = $ln; }
            $pos += strlen($text) + 1;
        }
        // fallback: brute force printing tokens raw
        $out = '';
        for ($t = $i; $t <= $k; $t++) $out .= tokstr($tokens[$t]);
        echo $out . "\n";
    }
}