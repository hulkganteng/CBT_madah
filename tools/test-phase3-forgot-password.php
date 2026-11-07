<?php
/* Uji alur lupa password seperti browser: ambil halaman, baca token CSRF,
   POST, lalu ikuti redirect. Hanya membaca; tidak mengubah data.
   Harapan: tanpa SMTP, sistem TIDAK menampilkan sukses palsu. */
function phase3_fetch($url, $jar, $post = NULL, $headers = FALSE)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HEADER => $headers,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_TIMEOUT => 20,
    ));
    if ($post !== NULL) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string) curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array($code, $body);
}

$base = 'http://localhost/garudacbt/auth/forgot_password';
$jar = tempnam(sys_get_temp_dir(), 'ck');
list($code, $html) = phase3_fetch($base, $jar);
if (!preg_match('/name="(csrf_token)"\s+value="([^"]*)"/i', $html, $token)) {
    echo "FAIL: token CSRF tidak ditemukan pada halaman lupa password\n";
    @unlink($jar);
    exit(1);
}
list($code) = phase3_fetch($base, $jar, array('identity' => 'admin', $token[1] => $token[2]), TRUE);
list($code, $html) = phase3_fetch($base, $jar);
@unlink($jar);

if ($code !== 200) {
    echo "FAIL: halaman lupa password mengembalikan HTTP $code\n";
    exit(1);
}
$success = preg_match('#<p class="text-green[^\"]*">(.*?)</p>#s', $html, $mm)
    ? trim(preg_replace('/\s+/', ' ', strip_tags($mm[1])))
    : '';
$info = preg_match('#<div id="infoMessage"[^>]*>(.*?)</div>#s', $html, $mm)
    ? trim(preg_replace('/\s+/', ' ', strip_tags($mm[1])))
    : '';

if ($success !== '') {
    echo "FAIL: sukses palsu ditampilkan: $success\n";
    exit(1);
}
if ($info === '') {
    echo "FAIL: tidak ada pesan kesalahan yang jelas\n";
    exit(1);
}
echo "PASS: lupa password tidak menampilkan sukses palsu\n";
echo "pesan: $info\n";
