<?php
// Structured recovery from goto-flattened PHP function.
// Usage: php degoto.php <file> <funcName> [maxdepth]
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
    $depth = 0; $started = false;
    for ($k = $j; $k < $n; $k++) {
        $s = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
        if ($s === '{') { $started = true; $depth++; }
        elseif ($s === '}') { $depth--; if ($started && $depth === 0) break; }
    }
    $startIdx = $j; $endIdx = $k; break;
}
if ($startIdx === null) { fwrite(STDERR, "not found\n"); exit(1); }

$out = '';
$i = $startIdx;
for (; $i <= $endIdx; $i++) { $s = is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i]; if ($s === '{') break; }
$sig = 'function ' . $want . '()';
$out = '';
for ($i = $i + 1; $i < $endIdx; $i++) {
    $tok = $tokens[$i]; $s = is_array($tok) ? $tok[1] : $tok;
    if (is_array($tok) && $tok[0] === T_CONSTANT_ENCAPSED_STRING && $s[0] === '"') {
        $s = '"' . addcslashes(stripcslashes(substr($s, 1, -1)), "\"\\") . '"';
    }
    $out .= $s;
}
$out = trim(preg_replace('/\s+/', ' ', $out));

// Split into statements by top-level semicolon; track leading labels.
$pos = 0; $len = strlen($out); $d = 0; $stmts = array(); $pendingLabels = array();
while ($pos < $len) {
    while ($pos < $len && $out[$pos] === ' ') $pos++;
    if ($pos >= $len) break;
    if (preg_match('/\G([A-Za-z_][A-Za-z0-9_]*):(?!:)/', $out, $m, 0, $pos)) {
        $pendingLabels[] = $m[1]; $pos += strlen($m[0]); continue;
    }
    $start = $pos; $d = 0;
    for (; $pos < $len; $pos++) {
        $c = $out[$pos];
        if ($c === '{' || $c === '(' || $c === '[') $d++;
        elseif ($c === '}' || $c === ')' || $c === ']') $d--;
        elseif ($c === ';' && $d === 0) { $pos++; break; }
    }
    $code = trim(substr($out, $start, $pos - $start));
    $stmts[] = array($pendingLabels, rtrim($code, '; '));
    $pendingLabels = array();
}

// label => statement index
$labels = array();
foreach ($stmts as $idx => $st) { foreach ($st[0] as $lbl) if (!isset($labels[$lbl])) $labels[$lbl] = $idx; }

$visited = array(); // label index => true
$emittedJoin = array();
function indentOf($lvl) { return str_repeat('    ', $lvl); }
function emitBlock($fromIdx, $lvl) {
    global $stmts, $labels, $visited, $emittedJoin;
    $outStr = '';
    $i = $fromIdx;
    $safety = 0;
    while ($i < count($stmts) && $safety++ < 400) {
        $st = $stmts[$i];
        // if this statement starts a new labeled region and we're not at entry, stop if it's a join we should return from
        if ($i !== $fromIdx && !empty($st[0])) {
            // reached another block start without goto: fall through naturally
        }
        $code = trim($st[1]);
        if ($code === '' ) { $i++; continue; }
        // pure goto
        if (preg_match('/^goto\s+(\w+)$/', $code, $mm)) {
            $tgt = $mm[1];
            if (!isset($labels[$tgt])) { $outStr .= indentOf($lvl) . "/* missing label $tgt */\n"; return $outStr; }
            $ti = $labels[$tgt];
            if (isset($visited[$ti])) {
                $outStr .= indentOf($lvl) . "/* goto $tgt (already emitted) */\n";
                return $outStr;
            }
            $visited[$ti] = true;
            $outStr .= emitBlock($ti, $lvl);
            return $outStr;
        }
        // if (cond) { goto A; } [goto B]
        if (preg_match('/^if\s*\((.+)\)\s*\{\s*goto\s+(\w+);?\s*\}(?:\s*(goto\s+(\w+);?))?$/s', $code, $mm)) {
            $cond = $mm[1];
            $a = $mm[2];
            $hasElse = isset($mm[4]) && $mm[4] !== '';
            $b = $hasElse ? $mm[4] : null;
            $outStr .= indentOf($lvl) . "if ($cond) {\n";
            foreach (array('then' => $a, 'else' => $b) as $branch => $lbl) {
                if ($branch === 'else' && !$hasElse) continue;
                $outStr .= indentOf($lvl + 1) . "// $branch -> $lbl\n";
                if (!$lbl) { continue; }
                if (!isset($labels[$lbl])) { $outStr .= indentOf($lvl+1)."/* missing */\n"; continue; }
                $ti = $labels[$lbl];
                if (isset($visited[$ti])) { $outStr .= indentOf($lvl+1) . "/* join/loop $lbl */\n"; continue; }
                $visited[$ti] = true;
                $body = emitBlock($ti, $lvl + 1);
                $outStr .= $body;
                if (!preg_match('/\/\*\s*(goto|join|loop)/', $body) && $body !== '') { /* fine */ }
                $outStr .= indentOf($lvl+1) . ($branch === 'then' ? "return;\n" : "return;\n");
            }
            $outStr .= indentOf($lvl) . "}\n";
            // after both branches emitted, we then continue with physical fallthrough if any
            $i++;
            continue;
        }
        // plain if without goto inside: emit whole statement
        $outStr .= indentOf($lvl) . $code . ";\n";
        if (preg_match('/^return\b/', $code)) return $outStr;
        $i++;
    }
    return $outStr;
}

$body = emitBlock(0, 1);
echo $sig . "\n{\n" . $body . "}\n";