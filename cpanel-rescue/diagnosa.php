<?php
/**
 * diagnosa.php - Alat diagnosa hosting cPanel.
 *
 * Dibuat untuk dua masalah:
 *   1. Website menampilkan pesan "could not find driver".
 *   2. Tombol Delete di cPanel File Manager tidak berfungsi.
 *
 * Kompatibel PHP 5.6 ke atas, tanpa library tambahan.
 *
 * CARA PAKAI
 *   1. Ganti nilai $PASSWORD di bawah ini dengan kata sandi bebas milik Anda.
 *   2. Upload file ini ke folder domain bermasalah lewat cPanel > File Manager > Upload.
 *   3. Buka https://domain-anda/diagnosa.php lalu masukkan kata sandi tadi.
 *   4. Setelah selesai, klik tombol "Hapus skrip ini" di bagian bawah halaman.
 */

$PASSWORD = 'GANTI_KATA_SANDI_INI';

/* ------------------------------------------------------------------ */

error_reporting(E_ALL);
@ini_set('display_errors', '1');
@set_time_limit(180);
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

function esc($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function safe_equals($a, $b) {
    if (function_exists('hash_equals')) {
        return hash_equals((string) $a, (string) $b);
    }
    return (string) $a === (string) $b;
}

function fn_disabled($name) {
    $list = (string) @ini_get('disable_functions');
    if ($list === '') {
        return !function_exists($name);
    }
    $parts = array_map('trim', explode(',', $list));
    return in_array($name, $parts, true) || !function_exists($name);
}

function human_bytes($n) {
    if (!is_numeric($n) || $n < 0) {
        return '-';
    }
    $u = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($n >= 1024 && $i < count($u) - 1) {
        $n /= 1024;
        $i++;
    }
    return round($n, 1) . ' ' . $u[$i];
}

function home_dir() {
    $candidates = array();
    if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
        $info = @posix_getpwuid(@posix_getuid());
        if (is_array($info) && isset($info['dir'])) {
            $candidates[] = $info['dir'];
        }
    }
    if (!empty($_SERVER['HOME'])) {
        $candidates[] = $_SERVER['HOME'];
    }
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        // .../home/akun/public_html/domain -> .../home/akun
        $doc = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $pos = strpos($doc, '/public_html');
        if ($pos !== false) {
            $candidates[] = substr($doc, 0, $pos);
        }
    }
    foreach ($candidates as $c) {
        if ($c && is_dir($c)) {
            return rtrim($c, '/');
        }
    }
    return '';
}

function owner_name($path) {
    $uid = @fileowner($path);
    if ($uid === false) {
        return '-';
    }
    if (function_exists('posix_getpwuid')) {
        $info = @posix_getpwuid($uid);
        if (is_array($info) && isset($info['name'])) {
            return $info['name'] . ' (' . $uid . ')';
        }
    }
    return (string) $uid;
}

function perms_of($path) {
    $p = @fileperms($path);
    if ($p === false) {
        return '-';
    }
    return substr(sprintf('%o', $p), -4);
}

/* ---------------------------- otentikasi --------------------------- */

$setupNeeded = ($PASSWORD === 'GANTI' . '_KATA_SANDI_INI' || $PASSWORD === '');
$given = isset($_POST['k']) ? (string) $_POST['k'] : '';
$authed = (!$setupNeeded && $given !== '' && safe_equals($PASSWORD, $given));

function page_head($title) {
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="robots" content="noindex,nofollow">';
    echo '<title>' . esc($title) . '</title><style>';
    echo 'body{font:14px/1.6 -apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f4f6f8;color:#1b2733}';
    echo '.wrap{max-width:960px;margin:0 auto;padding:24px 16px 64px}';
    echo 'h1{font-size:21px;margin:0 0 4px}h2{font-size:16px;margin:28px 0 8px;padding-bottom:6px;border-bottom:1px solid #dde3e9}';
    echo '.sub{color:#5b6b7c;margin:0 0 20px}';
    echo 'table{border-collapse:collapse;width:100%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.06);border-radius:6px;overflow:hidden}';
    echo 'th,td{text-align:left;padding:8px 12px;border-bottom:1px solid #eef1f4;vertical-align:top;word-break:break-word}';
    echo 'th{width:34%;font-weight:600;color:#40515f;background:#fafbfc}tr:last-child th,tr:last-child td{border-bottom:0}';
    echo '.box{background:#fff;border-radius:6px;padding:14px 16px;box-shadow:0 1px 2px rgba(0,0,0,.06);margin-bottom:12px}';
    echo '.ok{color:#186a3b;font-weight:600}.bad{color:#a4262c;font-weight:600}.warn{color:#8a6100;font-weight:600}';
    echo '.v{border-left:4px solid #ccc;padding:12px 16px;margin:0 0 12px;background:#fff;border-radius:0 6px 6px 0}';
    echo '.v.bad{border-left-color:#a4262c}.v.warn{border-left-color:#d79f00}.v.ok{border-left-color:#186a3b}';
    echo '.v p{margin:6px 0;font-weight:400;color:#1b2733}.v b{display:block;margin-bottom:4px}';
    echo 'code,pre{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:12.5px}';
    echo 'pre{background:#0f1720;color:#e6edf3;padding:12px;border-radius:6px;overflow:auto;max-height:320px}';
    echo 'ol,ul{margin:6px 0 6px 20px;padding:0}li{margin:4px 0}';
    echo 'input[type=password],input[type=text]{padding:9px 10px;border:1px solid #c6ced6;border-radius:5px;font-size:14px;min-width:240px}';
    echo 'button{padding:9px 16px;border:0;border-radius:5px;background:#2b6cb0;color:#fff;font-size:14px;cursor:pointer}';
    echo 'button.danger{background:#a4262c}';
    echo '</style></head><body><div class="wrap">';
}

function page_foot() {
    echo '</div></body></html>';
}

if (!$authed) {
    page_head('Diagnosa Hosting');
    echo '<h1>Diagnosa Hosting cPanel</h1>';
    if ($setupNeeded) {
        echo '<div class="v bad"><b>Skrip belum disiapkan</b>';
        echo '<p>Buka file <code>diagnosa.php</code> di <b>cPanel &gt; File Manager</b>, klik kanan &gt; <b>Edit</b>, lalu ganti baris:</p>';
        echo '<pre>$PASSWORD = \'GANTI_KATA_SANDI_INI\';</pre>';
        echo '<p>menjadi kata sandi bebas milik Anda, misalnya <code>$PASSWORD = \'kunci-saya-2026\';</code>. Simpan, lalu muat ulang halaman ini.</p></div>';
    } else {
        if ($given !== '') {
            echo '<div class="v bad"><b>Kata sandi salah.</b></div>';
        }
        echo '<div class="box"><form method="post">';
        echo '<p>Masukkan kata sandi yang Anda tulis di dalam file <code>diagnosa.php</code>.</p>';
        echo '<input type="password" name="k" autofocus> <button type="submit">Masuk</button>';
        echo '</form></div>';
    }
    page_foot();
    exit;
}

/* --------------------------- pengumpulan data --------------------------- */

$home    = home_dir();
$docroot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : dirname(__FILE__);
$here    = dirname(__FILE__);

$pdoLoaded  = extension_loaded('PDO');
$pdoDrivers = $pdoLoaded ? PDO::getAvailableDrivers() : array();
$hasPdoMysql = in_array('mysql', $pdoDrivers, true);
$hasMysqli   = extension_loaded('mysqli');

$verdicts = array();

if (!$pdoLoaded) {
    $verdicts[] = array('bad', 'Ekstensi PDO tidak aktif sama sekali',
        'Inilah penyebab pesan <code>could not find driver</code>. PHP untuk domain ini dijalankan tanpa PDO.');
} elseif (!$hasPdoMysql) {
    $verdicts[] = array('bad', 'Ekstensi <code>pdo_mysql</code> tidak aktif di PHP ' . PHP_VERSION,
        'Inilah penyebab pesan <code>could not find driver</code>. PDO ada, tetapi driver MySQL-nya tidak dimuat, '
        . 'sehingga aplikasi gagal menghubungi database. Driver yang tersedia sekarang: <code>'
        . (count($pdoDrivers) ? esc(implode(', ', $pdoDrivers)) : 'tidak ada satu pun') . '</code>.');
} else {
    $verdicts[] = array('ok', 'Ekstensi <code>pdo_mysql</code> aktif di PHP ' . PHP_VERSION,
        'Driver database sudah benar di folder ini. Kalau website masih menampilkan '
        . '<code>could not find driver</code>, berarti domain itu memakai versi PHP yang berbeda dari folder tempat '
        . 'skrip ini berada - periksa bagian "Folder domain di public_html" di bawah.');
}

/* --------- uji tulis: home, .trash, docroot (untuk masalah delete) -------- */

function write_test($dir) {
    if ($dir === '' || !is_dir($dir)) {
        return array(false, 'folder tidak ditemukan');
    }
    if (!is_writable($dir)) {
        return array(false, 'folder tidak writable (permission ' . perms_of($dir) . ', pemilik ' . owner_name($dir) . ')');
    }
    $f = rtrim($dir, '/') . '/.uji-tulis-' . getmypid() . '.tmp';
    $prev = error_get_last();
    $ok = @file_put_contents($f, str_repeat('x', 1024));
    if ($ok === false) {
        $err = error_get_last();
        $msg = ($err && $err !== $prev && isset($err['message'])) ? $err['message'] : 'gagal menulis (tanpa pesan)';
        return array(false, $msg);
    }
    @unlink($f);
    return array(true, 'bisa ditulis dan dihapus');
}

$trash = $home !== '' ? $home . '/.trash' : '';
$testHome    = write_test($home);
$testDocroot = write_test($here);
$testTrash   = is_dir($trash) ? write_test($trash) : array(null, 'folder .trash belum ada (normal jika belum pernah menghapus file)');

$freeHome = $home !== '' ? @disk_free_space($home) : false;

if ($testHome[0] === false || (is_dir($trash) && $testTrash[0] === false)) {
    $detail = 'Uji tulis gagal: ' . esc($testHome[0] === false ? $testHome[1] : $testTrash[1]) . '. ';
    $detail .= 'cPanel File Manager menghapus file dengan cara <b>memindahkannya ke folder <code>.trash</code></b> di direktori home. '
        . 'Kalau kuota disk penuh atau <code>.trash</code> tidak bisa ditulis, pemindahan itu gagal dan tombol Delete tampak tidak bereaksi.';
    $verdicts[] = array('bad', 'Penyebab Delete di File Manager gagal ditemukan', $detail);
} elseif (is_dir($trash) && $testTrash[0] === true) {
    $verdicts[] = array('ok', 'Folder <code>.trash</code> normal',
        'Home dan <code>.trash</code> bisa ditulis, jadi kegagalan Delete kemungkinan besar bukan karena kuota. '
        . 'Coba centang <b>"Skip the trash bin and permanently delete the files"</b> saat menghapus, '
        . 'dan cek juga kepemilikan file pada tabel di bawah.');
}

/* ------------------- versi PHP lain yang tersedia di server ------------------- */

$phpVariants = array();
$globs = array(
    '/opt/cpanel/ea-php*/root/etc/php.d',
    '/opt/alt/php*/etc/php.d',
);
foreach ($globs as $g) {
    $dirs = @glob($g, GLOB_ONLYDIR);
    if (!is_array($dirs)) {
        continue;
    }
    foreach ($dirs as $d) {
        $label = $d;
        if (preg_match('#/(ea-php\d+|php\d+)/#', $d, $m)) {
            $label = $m[1];
        }
        $inis = @glob($d . '/*pdo*mysql*.ini');
        $phpVariants[$label] = is_array($inis) && count($inis) > 0;
    }
}
ksort($phpVariants);

/* --------------- file konfigurasi yang bisa mengubah PHP --------------- */

$configFiles = array();
$scanDirs = array_unique(array_filter(array($here, $docroot, $home !== '' ? $home . '/public_html' : '', $home)));
foreach ($scanDirs as $d) {
    foreach (array('.htaccess', 'php.ini', '.user.ini') as $name) {
        $p = rtrim($d, '/') . '/' . $name;
        if (@is_file($p) && !isset($configFiles[$p])) {
            $configFiles[$p] = @file_get_contents($p);
        }
    }
}

$clSelector = ($home !== '') ? $home . '/.cl.selector/defaults.cfg' : '';
$clSelectorData = (@is_file($clSelector)) ? @file_get_contents($clSelector) : null;

/* ------------------------ deteksi aplikasi + DB ------------------------ */

function detect_app($dir) {
    $dir = rtrim($dir, '/');
    if (@is_file($dir . '/wp-config.php')) {
        return array('WordPress', $dir . '/wp-config.php');
    }
    if (@is_file($dir . '/sysconfig.inc.php') || @is_file($dir . '/config/database.php')) {
        return array('SLiMS (perpustakaan)', @is_file($dir . '/config/database.php') ? $dir . '/config/database.php' : $dir . '/sysconfig.inc.php');
    }
    if (@is_file($dir . '/artisan') && @is_file($dir . '/.env')) {
        return array('Laravel', $dir . '/.env');
    }
    if (@is_file($dir . '/config.php') && @is_dir($dir . '/course')) {
        return array('Moodle (LMS)', $dir . '/config.php');
    }
    if (@is_file($dir . '/application/config/database.php')) {
        return array('CodeIgniter', $dir . '/application/config/database.php');
    }
    if (@is_file($dir . '/configuration.php') && @is_dir($dir . '/administrator')) {
        return array('Joomla', $dir . '/configuration.php');
    }
    return array(null, null);
}

function read_db_config($file) {
    $out = array('host' => null, 'name' => null, 'user' => null, 'pass' => null, 'driver' => null);
    $src = @file_get_contents($file);
    if ($src === false) {
        return $out;
    }
    $pairs = array(
        'host'   => array('DB_HOST', 'dbhost', 'db_host', 'hostname', 'DATABASE_HOST'),
        'name'   => array('DB_NAME', 'dbname', 'db_name', 'database', 'DATABASE_NAME'),
        'user'   => array('DB_USER', 'dbuser', 'db_user', 'username', 'DATABASE_USER'),
        'pass'   => array('DB_PASSWORD', 'dbpass', 'db_password', 'password', 'DATABASE_PASSWORD'),
        'driver' => array('DB_CONNECTION', 'dbtype', 'db_driver', 'dbdriver', 'driver'),
    );
    foreach ($pairs as $key => $names) {
        foreach ($names as $n) {
            // define('DB_HOST','x') | $dbhost = 'x' | 'host' => 'x' | DB_HOST=x
            $patterns = array(
                "#define\\s*\\(\\s*['\"]" . preg_quote($n, '#') . "['\"]\\s*,\\s*['\"]([^'\"]*)['\"]#i",
                "#\\\$" . preg_quote($n, '#') . "\\s*=\\s*['\"]([^'\"]*)['\"]#i",
                "#->" . preg_quote($n, '#') . "\\s*=\\s*['\"]([^'\"]*)['\"]#i",
                "#['\"]" . preg_quote($n, '#') . "['\"]\\s*=>\\s*['\"]([^'\"]*)['\"]#i",
                "#^\\s*" . preg_quote($n, '#') . "\\s*=\\s*\"?([^\"\\r\\n]*)\"?\\s*$#im",
            );
            foreach ($patterns as $re) {
                if (preg_match($re, $src, $m)) {
                    $out[$key] = trim($m[1]);
                    break 2;
                }
            }
        }
    }
    return $out;
}

list($appName, $appConfig) = detect_app($here);
if ($appName === null && $here !== $docroot) {
    list($appName, $appConfig) = detect_app($docroot);
}
$db = $appConfig ? read_db_config($appConfig) : null;

$dbTest = null;
if ($db && $db['name'] && $db['user']) {
    $host = $db['host'] ? $db['host'] : 'localhost';
    if ($hasPdoMysql) {
        try {
            $dsn = 'mysql:host=' . $host . ';dbname=' . $db['name'];
            $pdo = new PDO($dsn, $db['user'], (string) $db['pass'], array(PDO::ATTR_TIMEOUT => 5));
            $dbTest = array(true, 'Koneksi PDO ke database "' . $db['name'] . '" BERHASIL.');
        } catch (Exception $e) {
            $dbTest = array(false, 'Koneksi PDO gagal: ' . $e->getMessage());
        }
    } elseif ($hasMysqli) {
        $c = @mysqli_connect($host, $db['user'], (string) $db['pass'], $db['name']);
        if ($c) {
            $dbTest = array(true, 'PDO tidak ada, tetapi koneksi lewat mysqli BERHASIL. '
                . 'Artinya database dan kredensialnya sehat - yang kurang hanya ekstensi pdo_mysql.');
            @mysqli_close($c);
        } else {
            $dbTest = array(false, 'PDO tidak ada dan mysqli juga gagal: ' . mysqli_connect_error());
        }
    } else {
        $dbTest = array(false, 'PDO maupun mysqli tidak tersedia, koneksi database tidak bisa diuji.');
    }
}

/* ---------------------- daftar folder domain ---------------------- */

$domainDirs = array();
$phRoot = $home !== '' ? $home . '/public_html' : '';
if ($phRoot !== '' && is_dir($phRoot)) {
    $items = @scandir($phRoot);
    if (is_array($items)) {
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') {
                continue;
            }
            $p = $phRoot . '/' . $it;
            if (!@is_dir($p)) {
                continue;
            }
            $handler = '';
            $ht = $p . '/.htaccess';
            if (@is_file($ht)) {
                $c = (string) @file_get_contents($ht);
                if (preg_match('#(AddHandler|SetHandler)\s+\S*(ea-php\d+|alt-php\d+|php\d+)\S*#i', $c, $m)) {
                    $handler = $m[2];
                }
            }
            list($an, ) = detect_app($p);
            $domainDirs[] = array(
                'name'    => $it,
                'index'   => (@is_file($p . '/index.php') || @is_file($p . '/index.html')),
                'handler' => $handler,
                'app'     => $an,
                'perms'   => perms_of($p),
                'owner'   => owner_name($p),
            );
        }
    }
}

/* ------------------------------ tampilan ------------------------------ */

page_head('Hasil Diagnosa');
echo '<h1>Hasil Diagnosa Hosting</h1>';
echo '<p class="sub">Dijalankan pada ' . esc(date('d M Y H:i:s')) . ' dari <code>' . esc($here) . '</code></p>';

echo '<h2>Kesimpulan</h2>';
foreach ($verdicts as $v) {
    echo '<div class="v ' . esc($v[0]) . '"><b>' . $v[1] . '</b><p>' . $v[2] . '</p></div>';
}

echo '<h2>PHP untuk folder ini</h2><table>';
$rows = array(
    'Versi PHP'          => PHP_VERSION,
    'Antarmuka (SAPI)'   => PHP_SAPI,
    'Dijalankan sebagai' => (function_exists('posix_getpwuid') && function_exists('posix_geteuid'))
                            ? (($u = @posix_getpwuid(@posix_geteuid())) && isset($u['name']) ? $u['name'] : '-') : '-',
    'php.ini yang dipakai' => (string) php_ini_loaded_file(),
    'File .ini tambahan' => (string) php_ini_scanned_files(),
    'Ekstensi PDO'       => $pdoLoaded ? '<span class="ok">aktif</span>' : '<span class="bad">TIDAK aktif</span>',
    'Driver PDO tersedia'=> $pdoLoaded
        ? (count($pdoDrivers) ? esc(implode(', ', $pdoDrivers)) : '<span class="bad">kosong</span>')
        : '-',
    'pdo_mysql'          => $hasPdoMysql ? '<span class="ok">aktif</span>' : '<span class="bad">TIDAK aktif</span>',
    'mysqli'             => $hasMysqli ? '<span class="ok">aktif</span>' : '<span class="bad">tidak aktif</span>',
    'mysqlnd'            => extension_loaded('mysqlnd') ? 'aktif' : 'tidak aktif',
    'pdo_sqlite'         => in_array('sqlite', $pdoDrivers, true) ? 'aktif' : 'tidak aktif',
    'disable_functions'  => (string) @ini_get('disable_functions'),
);
foreach ($rows as $k => $v) {
    echo '<tr><th>' . esc($k) . '</th><td>' . ($v === '' ? '<i>kosong</i>' : $v) . '</td></tr>';
}
echo '</table>';

if (count($phpVariants)) {
    echo '<h2>Versi PHP lain yang terpasang di server</h2>';
    echo '<p class="sub">Kolom kanan menunjukkan apakah versi itu punya berkas konfigurasi pdo_mysql. '
       . 'Pilih versi bertanda "tersedia" di <b>MultiPHP Manager</b> bila versi sekarang tidak punya driver.</p><table>';
    foreach ($phpVariants as $label => $ok) {
        echo '<tr><th>' . esc($label) . '</th><td>'
           . ($ok ? '<span class="ok">pdo_mysql tersedia</span>' : '<span class="warn">pdo_mysql tidak ditemukan</span>')
           . '</td></tr>';
    }
    echo '</table>';
}

echo '<h2>Penyimpanan dan hak tulis</h2><table>';
echo '<tr><th>Folder home</th><td>' . ($home !== '' ? esc($home) : '<span class="warn">tidak terdeteksi</span>') . '</td></tr>';
echo '<tr><th>Sisa ruang disk (partisi)</th><td>' . ($freeHome !== false ? esc(human_bytes($freeHome)) : '-')
   . ' <i>(ini kapasitas partisi server, bukan kuota akun Anda - lihat kuota di cPanel &gt; Disk Usage)</i></td></tr>';
echo '<tr><th>Uji tulis di home</th><td>' . ($testHome[0] ? '<span class="ok">OK</span> - ' : '<span class="bad">GAGAL</span> - ') . esc($testHome[1]) . '</td></tr>';
echo '<tr><th>Uji tulis di folder ini</th><td>' . ($testDocroot[0] ? '<span class="ok">OK</span> - ' : '<span class="bad">GAGAL</span> - ') . esc($testDocroot[1]) . '</td></tr>';
echo '<tr><th>Folder <code>.trash</code></th><td>';
if ($testTrash[0] === null) {
    echo esc($testTrash[1]);
} else {
    echo ($testTrash[0] ? '<span class="ok">OK</span> - ' : '<span class="bad">GAGAL</span> - ') . esc($testTrash[1]);
    echo '<br>permission ' . esc(perms_of($trash)) . ', pemilik ' . esc(owner_name($trash));
}
echo '</td></tr>';
echo '<tr><th>Pemilik folder ini</th><td>' . esc(owner_name($here)) . ', permission ' . esc(perms_of($here)) . '</td></tr>';
echo '</table>';

if (count($domainDirs)) {
    echo '<h2>Folder domain di public_html</h2><table>';
    echo '<tr><th>Folder</th><td><b>Halaman index</b> &middot; <b>Versi PHP di .htaccess</b> &middot; <b>Aplikasi</b> &middot; <b>Permission</b></td></tr>';
    foreach ($domainDirs as $d) {
        echo '<tr><th>' . esc($d['name']) . '</th><td>'
           . ($d['index'] ? '<span class="ok">ada</span>' : '<span class="bad">TIDAK ADA index.php/html</span>')
           . ' &middot; ' . ($d['handler'] !== '' ? esc($d['handler']) : '<i>ikut default</i>')
           . ' &middot; ' . ($d['app'] !== null ? esc($d['app']) : '<i>tidak dikenali</i>')
           . ' &middot; ' . esc($d['perms']) . ' / ' . esc($d['owner'])
           . '</td></tr>';
    }
    echo '</table>';
}

echo '<h2>Aplikasi dan database di folder ini</h2><table>';
echo '<tr><th>Aplikasi terdeteksi</th><td>' . ($appName !== null ? esc($appName) : '<i>tidak dikenali</i>') . '</td></tr>';
echo '<tr><th>File konfigurasi</th><td>' . ($appConfig !== null ? esc($appConfig) : '-') . '</td></tr>';
if ($db) {
    echo '<tr><th>DB host</th><td>' . esc($db['host'] !== null ? $db['host'] : '-') . '</td></tr>';
    echo '<tr><th>Nama database</th><td>' . esc($db['name'] !== null ? $db['name'] : '-') . '</td></tr>';
    echo '<tr><th>User database</th><td>' . esc($db['user'] !== null ? $db['user'] : '-') . '</td></tr>';
    echo '<tr><th>Password database</th><td>' . ($db['pass'] !== null && $db['pass'] !== '' ? '<i>terisi (disembunyikan)</i>' : '<span class="warn">kosong</span>') . '</td></tr>';
    if ($db['driver'] !== null) {
        echo '<tr><th>Driver yang diminta aplikasi</th><td>' . esc($db['driver']) . '</td></tr>';
    }
}
if ($dbTest !== null) {
    echo '<tr><th>Uji koneksi database</th><td>'
       . ($dbTest[0] ? '<span class="ok">BERHASIL</span> - ' : '<span class="bad">GAGAL</span> - ')
       . esc($dbTest[1]) . '</td></tr>';
}
echo '</table>';

if (count($configFiles)) {
    echo '<h2>File konfigurasi yang ditemukan</h2>';
    echo '<p class="sub">Baris <code>AddHandler</code>/<code>SetHandler</code> menentukan versi PHP domain. '
       . 'Baris <code>php_value</code>, <code>php_flag</code>, atau <code>extension=</code> yang salah juga bisa mematikan driver.</p>';
    foreach ($configFiles as $path => $content) {
        echo '<div class="box"><b>' . esc($path) . '</b>';
        $content = (string) $content;
        if (strlen($content) > 4000) {
            $content = substr($content, 0, 4000) . "\n... (dipotong)";
        }
        echo '<pre>' . esc($content === '' ? '(kosong)' : $content) . '</pre></div>';
    }
}

if ($clSelectorData !== null) {
    echo '<h2>Pilihan ekstensi PHP Selector (CloudLinux)</h2>';
    echo '<div class="box"><b>' . esc($clSelector) . '</b><pre>' . esc($clSelectorData) . '</pre></div>';
}

echo '<h2>Langkah perbaikan</h2><div class="box">';
if (!$hasPdoMysql) {
    echo '<p><b>Untuk pesan "could not find driver":</b></p><ol>';
    echo '<li>Masuk cPanel &gt; <b>Select PHP Version</b> (kalau ada) atau <b>MultiPHP INI Editor</b>.</li>';
    echo '<li>Pastikan domain yang bermasalah dipilih pada kotak domain di bagian atas.</li>';
    echo '<li>Di daftar <b>Extensions</b>, centang <code>pdo_mysql</code>, <code>mysqli</code>, <code>mysqlnd</code>, dan <code>pdo</code>. '
       . 'Kalau tersedia pilihan <code>nd_pdo_mysql</code>, centang salah satu saja - <code>nd_pdo_mysql</code> atau <code>pdo_mysql</code>.</li>';
    echo '<li>Klik <b>Save</b>, tunggu sekitar 30 detik, lalu muat ulang halaman ini untuk memastikan baris pdo_mysql sudah hijau.</li>';
    echo '<li>Kalau versi PHP sekarang tidak menyediakan pdo_mysql sama sekali, buka cPanel &gt; <b>MultiPHP Manager</b>, '
       . 'centang domain tersebut, lalu pindahkan ke versi PHP yang di tabel "Versi PHP lain" di atas bertanda <i>tersedia</i> '
       . '(umumnya PHP 8.1 atau 8.2), klik <b>Apply</b>.</li></ol>';
} else {
    echo '<p><b>Driver sudah benar di folder ini.</b> Kalau domain lain masih error, upload ulang <code>diagnosa.php</code> '
       . 'ke folder domain tersebut dan jalankan dari alamat domain itu, karena tiap domain bisa memakai versi PHP yang berbeda.</p>';
}
echo '<p><b>Untuk Delete di File Manager yang tidak berfungsi:</b></p><ol>';
echo '<li>Saat menghapus, centang <b>"Skip the trash bin and permanently delete the files"</b> pada kotak konfirmasi. '
   . 'Ini melewati folder <code>.trash</code> yang sering jadi biang kegagalan.</li>';
echo '<li>Cek cPanel &gt; <b>Disk Usage</b>. Kalau kuota disk atau jumlah file (inode) sudah penuh, '
   . 'penghapusan lewat trash pasti gagal. Kosongkan folder <code>.trash</code> di direktori home terlebih dahulu.</li>';
echo '<li>Kalau masih gagal, gunakan <code>hapus.php</code> yang disertakan bersama skrip ini - '
   . 'alat itu menghapus file langsung tanpa melewati <code>.trash</code>.</li>';
echo '<li>Coba juga jendela penyamaran / matikan ekstensi pemblokir iklan, karena beberapa ekstensi memblokir permintaan '
   . '<code>fileop</code> milik File Manager sehingga tombol Delete tampak diam saja.</li></ol>';
echo '</div>';

echo '<h2>Selesai</h2><div class="box">';
echo '<p><b>Jangan biarkan skrip ini di server.</b> File ini menampilkan detail konfigurasi hosting Anda.</p>';
echo '<form method="post" onsubmit="return confirm(\'Hapus diagnosa.php dari server?\')">';
echo '<input type="hidden" name="k" value="' . esc($given) . '">';
echo '<input type="hidden" name="selfdestruct" value="1">';
echo '<button class="danger" type="submit">Hapus skrip ini dari server</button></form></div>';

page_foot();

if (isset($_POST['selfdestruct']) && $_POST['selfdestruct'] === '1') {
    @unlink(__FILE__);
}
