<?php
$mathAsset = static function ($path) {
    return html_escape(parse_url(base_url($path), PHP_URL_PATH) . '?v=' . filemtime(FCPATH . $path));
};
?>
<link rel="stylesheet" href="<?= $mathAsset('assets/plugins/katex/katex.min.css') ?>">
<script src="<?= $mathAsset('assets/plugins/katex/katex.min.js') ?>"></script>
<script src="<?= $mathAsset('assets/plugins/katex/contrib/auto-render.min.js') ?>"></script>
<script src="<?= $mathAsset('assets/app/js/cbt/question-math.js') ?>"></script>
