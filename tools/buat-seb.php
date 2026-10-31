<?php
/**
 * Pembuat berkas .seb + Config Key untuk Garuda CBT (PRD FR-19).
 *
 * Pakai: php tools/buat-seb.php [preset] [keluaran.seb]
 *        php tools/buat-seb.php madah SEB-Simulasi-CBT.seb
 *
 * Berkas dibuat dengan prefiks "plnd" (tidak dienkripsi, gzip ganda) sesuai
 * BinaryParser SEB (gzip('plnd' + gzip(xml))).
 *
 * Config Key dihitung persis seperti DataProcessor.cs + Json.cs SEB:
 *   - data konfigurasi diserialkan ke JSON tanpa spasi
 *   - kunci diurutkan (StringComparer.InvariantCulture ~ strcasecmp untuk ASCII)
 *   - kunci originatorVersion dibuang
 *   - digest SHA-256 heksadesimal huruf kecil
 * Sumber: SafeExamBrowser/seb-win-refactoring
 * (SafeExamBrowser.Configuration/ConfigurationData/DataProcessor.cs + Json.cs).
 */

if (!class_exists('SebData')) {
    /** Penanda nilai plist bertipe <data> (sudah berupa base64). */
    class SebData
    {
        public $base64;

        public function __construct($base64)
        {
            $this->base64 = $base64;
        }
    }
}

if (!function_exists('seb_plist')) {
    /** Serialisasi array asosiatif PHP ke plist XML. Kunci selalu diurutkan. */
    function seb_plist($value)
    {
        if ($value instanceof SebData) {
            return '<data>' . htmlspecialchars($value->base64, ENT_XML1, 'UTF-8') . '</data>';
        }
        if (is_array($value)) {
            if (array_keys($value) === range(0, count($value) - 1)) {
                $out = '<array>';
                foreach ($value as $item) {
                    $out .= seb_plist($item);
                }
                return $out . '</array>';
            }
            $keys = array_keys($value);
            usort($keys, 'strcasecmp');
            $out = '<dict>';
            foreach ($keys as $key) {
                $out .= '<key>' . htmlspecialchars($key, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</key>';
                $out .= seb_plist($value[$key]);
            }
            return $out . '</dict>';
        }
        if (is_bool($value)) {
            return $value ? '<true/>' : '<false/>';
        }
        if (is_int($value)) {
            return '<integer>' . $value . '</integer>';
        }
        return '<string>' . htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</string>';
    }
}

if (!function_exists('seb_json')) {
    /** SEB-JSON: tanpa spasi, kunci terurut, originatorVersion dibuang. */
    function seb_json($value)
    {
        if ($value instanceof SebData) {
            return json_encode($value->base64);
        }
        if (is_array($value)) {
            if ($value && array_keys($value) === range(0, count($value) - 1)) {
                $parts = array();
                foreach ($value as $item) {
                    $parts[] = seb_json($item);
                }
                return '[' . implode(',', $parts) . ']';
            }
            $keys = array_keys($value);
            usort($keys, 'strcasecmp');
            $parts = array();
            foreach ($keys as $key) {
                $parts[] = json_encode((string) $key) . ':' . seb_json($value[$key]);
            }
            return '{' . implode(',', $parts) . '}';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return json_encode((string) $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('buat_seb_config')) {
    /**
     * Tulis berkas .seb dan hitung Config Key-nya.
     *
     * @param array $preset    startURL, host, file
     * @param string $out      path berkas keluaran
     * @param array $overrides penimpa setelan (mis. matikan URL filter)
     * @return array           file, configKey, startURL, bytes
     */
    function buat_seb_config(array $preset, $out, $adminPass = null, $quitPass = null, array $overrides = array())
    {
        $host = $preset['host'];
        $filterOn = array_key_exists('filter', $preset) ? (bool) $preset['filter'] : true;

        $settings = array(
            'allowQuit' => true,
            'allowSwitchToApplications' => false,
            'allowUserSwitching' => false,
            'browserViewMode' => 0,
            'browserWindowAllowReload' => true,
            'browserWindowShowURL' => 0,
            'enableAltEsc' => false,
            'enableAltF4' => false,
            'enableAltTab' => false,
            'enableAppSwitcherCheck' => true,
            'enableBrowserWindowToolbar' => false,
            'enableCtrlEsc' => false,
            'enableEsc' => false,
            'enableF1' => false,
            'enableF10' => false,
            'enableF11' => false,
            'enableF12' => false,
            'enableF2' => false,
            'enableF3' => false,
            'enableF4' => false,
            'enableF5' => true,
            'enableF6' => false,
            'enableF7' => false,
            'enableF8' => false,
            'enableF9' => false,
            'enableJavaScript' => true,
            'enableLogging' => true,
            'enablePrintScreen' => false,
            'enablePrivateClipboard' => true,
            'enableRightMouse' => false,
            'enableStartMenu' => false,
            'enableZoomPage' => true,
            'enableZoomText' => true,
            'examKeySalt' => new SebData(base64_encode(hash('sha256', 'garuda-cbt:' . $host, true))),
            'hashedAdminPassword' => $adminPass ? hash('sha256', $adminPass) : '',
            'hashedQuitPassword' => $quitPass ? hash('sha256', $quitPass) : '',
            'ignoreExitKeys' => !$quitPass,
            'insideSebEnableEaseOfAccess' => false,
            'insideSebEnableNetworkConnectionSelector' => false,
            'insideSebEnableShutDown' => false,
            'insideSebEnableStartTaskManager' => false,
            'insideSebEnableSwitchUser' => false,
            'killExplorerShell' => false,
            'logLevel' => 1,
            'mainBrowserWindowHeight' => '100%',
            'mainBrowserWindowPositioning' => 1,
            'mainBrowserWindowWidth' => '100%',
            'monitorProcesses' => true,
            'originatorVersion' => 'SEB_Windows_3.10.1',
            'quitURL' => '',
            'quitURLConfirm' => true,
            'sebConfigPurpose' => 1,
            'sebMode' => 0,
            'sebServicePolicy' => 1,
            'sendBrowserExamKey' => true,
            'showReloadButton' => true,
            'showTaskBar' => false,
            'showTime' => true,
            'startURL' => $preset['startURL'],
            'taskBarHeight' => 40,
            'touchOptimized' => false,
            'URLFilterEnable' => $filterOn,
            'URLFilterEnableContentFilter' => false,
            'URLFilterMessage' => 1,
            'urlFilterRegex' => true,
        );

        if ($filterOn) {
            $rules = array(
                array('action' => 1, 'active' => true, 'expression' => $host, 'regex' => false),
            );
            foreach (array('assets', 'uploads', 'index.php', 'auth', 'siswa', 'cbt', 'dbpemulihan') as $path) {
                $rules[] = array('action' => 1, 'active' => true, 'expression' => $host . '/' . $path, 'regex' => false);
            }
            $rules[] = array('action' => 1, 'active' => true, 'expression' => $host . '/assets/*', 'regex' => false);
            $rules[] = array('action' => 1, 'active' => true, 'expression' => $host . '/uploads/*', 'regex' => false);
            $settings['URLFilterRules'] = $rules;
        }

        foreach ($overrides as $key => $value) {
            $settings[$key] = $value;
        }

        $hashable = $settings;
        unset($hashable['originatorVersion']);
        $configKey = hash('sha256', seb_json($hashable));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">' . "\n"
            . '<plist version="1.0">' . seb_plist($settings) . '</plist>' . "\n";

        $payload = gzencode($xml, 9);
        if ($payload === false) {
            throw new RuntimeException('gzip XML gagal');
        }
        $file = gzencode('plnd' . $payload, 9);
        if ($file === false) {
            throw new RuntimeException('gzip berkas gagal');
        }
        if (file_put_contents($out, $file) === false) {
            throw new RuntimeException('Gagal menulis ' . $out);
        }

        return array(
            'file' => $out,
            'bytes' => strlen($file),
            'startURL' => $preset['startURL'],
            'configKey' => $configKey,
        );
    }
}

$seb_presets = array(
    'online' => array(
        'startURL' => 'https://cbt.bhydev.my.id/login-siswa',
        'host' => 'cbt.bhydev.my.id',
        'file' => 'SEB-Online.seb',
    ),
    'madah' => array(
        'startURL' => 'https://cbtmadah.bhydev.my.id/login-siswa',
        'host' => 'cbtmadah.bhydev.my.id',
        'file' => 'SEB-Simulasi-CBT.seb',
    ),
    'lokal' => array(
        'startURL' => 'http://192.168.1.10/garudacbt/login-siswa',
        'host' => '192.168.1.10',
        'file' => 'SEB-Lokal.seb',
    ),
);

/* Hanya jalankan CLI bila berkas ini dieksekusi langsung, bukan di-require
   oleh alat lain (mis. tools/siapkan-simulasi.php). */
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $which = isset($argv[1]) ? strtolower($argv[1]) : 'online';

    if (!isset($seb_presets[$which])) {
        fwrite(STDERR, 'Pilihan jalur: ' . implode(' | ', array_keys($seb_presets)) . "\n");
        exit(2);
    }
    $preset = $seb_presets[$which];
    $out = isset($argv[2]) ? $argv[2] : $preset['file'];

    $result = buat_seb_config(
        $preset,
        $out,
        getenv('SEB_ADMIN_PASSWORD') ?: null,
        getenv('SEB_QUIT_PASSWORD') ?: null
    );

    echo 'berkas   : ', $result['file'], ' (', $result['bytes'], " byte)\n";
    echo 'startURL : ', $result['startURL'], "\n";
    echo 'configKey: ', $result['configKey'], "\n";
}
