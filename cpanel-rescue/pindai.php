<?php
/**
 * pindai.php - Pemindai backdoor/webshell untuk akun cPanel.
 *
 * Mencari berkas PHP berisi pola khas pintu belakang, lalu memberi pilihan
 * mengarantinanya (diganti nama + permission 000) alih-alih langsung dihapus,
 * supaya masih bisa diperiksa dan dipulihkan bila ternyata keliru.
 *
 * Kompatibel PHP 5.6 ke atas. Semua operasi dibatasi di dalam direktori home.
 *
 * CARA PAKAI
 *   1. Ganti nilai $PASSWORD di bawah ini dengan kata sandi bebas milik Anda.
 *   2. Upload lewat cPanel > File Manager > Upload ke folder mana pun di public_html.
 *   3. Buka https://domain-anda/pindai.php lalu masukkan kata sandi tadi.
 *   4. Setelah selesai, klik "Hapus skrip ini dari server".
 */

$PASSWORD = 'GANTI_KATA_SANDI_INI';

/* ------------------------------------------------------------------ */

error_reporting(E_ALL);
@ini_set('display_errors', '1');
@set_time_limit(0);
@ignore_user_abort(true);
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

function human_bytes($n) {
    if (!is_numeric($n) || $n < 0) {
        return '-';
    }
    $u = array('B', 'KB', 'MB', 'GB');
    $i = 0;
    while ($n >= 1024 && $i < count($u) - 1) {
        $n /= 1024;
        $i++;
    }
    return round($n, 1) . ' ' . $u[$i];
}

function home_dir() {
    $c = array();
    if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
        $i = @posix_getpwuid(@posix_getuid());
        if (is_array($i) && isset($i['dir'])) {
            $c[] = $i['dir'];
        }
    }
    if (!empty($_SERVER['HOME'])) {
        $c[] = $_SERVER['HOME'];
    }
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $d = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $p = strpos($d, '/public_html');
        if ($p !== false) {
            $c[] = substr($d, 0, $p);
        }
    }
    foreach ($c as $x) {
        if ($x && is_dir($x)) {
            $r = realpath($x);
            if ($r !== false) {
                return rtrim($r, '/');
            }
        }
    }
    return '';
}

function within_home($path, $home) {
    if ($home === '') {
        return false;
    }
    $r = @realpath($path);
    if ($r === false) {
        return false;
    }
    return $r === $home || strpos($r, $home . '/') === 0;
}

/**
 * Daftar penanda. Tiap entri: kunci, tingkat (tinggi/sedang), keterangan,
 * dan regex yang dicocokkan ke isi berkas.
 */
function indikator() {
    return array(
        array('c2-gitlab', 'tinggi', 'Menghubungi repositori penyerang yang sudah dikenal',
            '#gitlab\.com/knightchaos57#i'),
        array('c2-b64', 'tinggi', 'URL tersembunyi dalam base64 (https://gitlab.com/...)',
            '#aHR0cHM6Ly9naXRsYWIuY29t#'),
        array('eval-loader', 'tinggi', 'eval(\'?>\' . $kode) - menjalankan kode yang diunduh',
            '#eval\s*\(\s*([\'"])\?>\1#i'),
        array('fx-table', 'tinggi', 'Tabel nama fungsi teracak ($GLOBALS[\'__fx\'])',
            '#\$GLOBALS\s*\[\s*[\'"]__fx[\'"]\s*\]#'),
        array('eval-decode', 'tinggi', 'eval() atas kode yang disamarkan (base64/gzinflate/rot13)',
            '#eval\s*\(\s*(base64_decode|gzinflate|gzuncompress|str_rot13|strrev|rawurldecode|convert_uudecode)\s*\(#i'),
        array('assert-var', 'tinggi', 'assert() atas variabel - setara eval()',
            '#assert\s*\(\s*\$#'),
        array('preg-e', 'tinggi', 'preg_replace dengan modifier /e - menjalankan kode',
            '#preg_replace\s*\(\s*([\'"])[^\'"]*\1?[imsxuUS]*e[\'"]#i'),
        array('shell-input', 'tinggi', 'Perintah sistem langsung dari input pengunjung',
            '#(system|shell_exec|passthru|popen|proc_open|exec)\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)#i'),
        array('shell-known', 'tinggi', 'Nama webshell yang sudah dikenal',
            '#(b374k|IndoXploit|wso_version|FilesMan|MadSpot|priv8|Sh3ll|c99sh|r57shell)#i'),
        array('create-function', 'sedang', 'create_function() - cara lama menjalankan kode dinamis',
            '#create_function\s*\(#i'),
        array('upload-eval', 'sedang', 'Penerima unggahan berkas di berkas yang juga menjalankan kode',
            '#move_uploaded_file\s*\(#i'),
        array('b64-blob', 'sedang', 'Blok base64 sangat panjang (payload tersembunyi)',
            '#[\'"][A-Za-z0-9+/]{300,}={0,2}[\'"]#'),
        array('var-func', 'sedang', 'Pemanggilan fungsi lewat variabel dari input pengunjung',
            '#\$_(GET|POST|REQUEST|COOKIE)\s*\[[^\]]+\]\s*\(#'),
        array('hex-func', 'sedang', 'Nama fungsi disamarkan dengan escape heksadesimal',
            '#([\'"])(?:\\\\x[0-9a-f]{2}){6,}#i'),
    );
}

/* ---------------------------- otentikasi --------------------------- */

$setupNeeded = ($PASSWORD === 'GANTI' . '_KATA_SANDI_INI' || $PASSWORD === '');
$given  = isset($_POST['k']) ? (string) $_POST['k'] : '';
$authed = (!$setupNeeded && $given !== '' && safe_equals($PASSWORD, $given));

function page_head($title) {
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="robots" content="noindex,nofollow">';
    echo '<title>' . esc($title) . '</title><style>';
    echo 'body{font:14px/1.6 -apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f4f6f8;color:#1b2733}';
    echo '.wrap{max-width:1100px;margin:0 auto;padding:24px 16px 64px}';
    echo 'h1{font-size:21px;margin:0 0 4px}h2{font-size:16px;margin:26px 0 8px;padding-bottom:6px;border-bottom:1px solid #dde3e9}';
    echo '.sub{color:#5b6b7c;margin:0 0 18px}';
    echo 'table{border-collapse:collapse;width:100%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.06);border-radius:6px;overflow:hidden}';
    echo 'th,td{text-align:left;padding:8px 10px;border-bottom:1px solid #eef1f4;vertical-align:top}';
    echo 'thead th{background:#fafbfc;font-weight:600;color:#40515f;font-size:12.5px;text-transform:uppercase;letter-spacing:.03em}';
    echo '.box{background:#fff;border-radius:6px;padding:14px 16px;box-shadow:0 1px 2px rgba(0,0,0,.06);margin-bottom:12px}';
    echo '.v{border-left:4px solid #ccc;padding:12px 16px;margin:0 0 12px;background:#fff;border-radius:0 6px 6px 0}';
    echo '.v.bad{border-left-color:#a4262c}.v.ok{border-left-color:#186a3b}.v.warn{border-left-color:#d79f00}';
    echo '.tinggi{color:#a4262c;font-weight:700}.sedang{color:#8a6100;font-weight:600}.ok{color:#186a3b;font-weight:600}';
    echo 'code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:12.5px;word-break:break-all}';
    echo 'input[type=password],input[type=text]{padding:9px 10px;border:1px solid #c6ced6;border-radius:5px;font-size:14px}';
    echo 'button{padding:9px 16px;border:0;border-radius:5px;background:#2b6cb0;color:#fff;font-size:14px;cursor:pointer}';
    echo 'button.danger{background:#a4262c}.muted{color:#7a8894;font-size:12.5px}';
    echo '.bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:10px 0}';
    echo '</style></head><body><div class="wrap">';
}

function page_foot() {
    echo '</div></body></html>';
}

if (!$authed) {
    page_head('Pemindai Backdoor');
    echo '<h1>Pemindai Backdoor</h1>';
    if ($setupNeeded) {
        echo '<div class="v bad"><b>Skrip belum disiapkan</b>';
        echo '<p>Buka <code>pindai.php</code> di cPanel &gt; File Manager, klik kanan &gt; <b>Edit</b>, ganti '
           . '<code>GANTI_KATA_SANDI_INI</code> dengan kata sandi bebas milik Anda, simpan, lalu muat ulang halaman ini.</p></div>';
    } else {
        if ($given !== '') {
            echo '<div class="v bad"><b>Kata sandi salah.</b></div>';
        }
        echo '<div class="box"><form method="post"><p>Masukkan kata sandi dari dalam berkas <code>pindai.php</code>.</p>';
        echo '<input type="password" name="k" autofocus> <button type="submit">Masuk</button></form></div>';
    }
    page_foot();
    exit;
}

$home = home_dir();
if ($home === '') {
    page_head('Pemindai Backdoor');
    echo '<div class="v bad"><b>Direktori home tidak terdeteksi.</b><p>Skrip berhenti demi keamanan.</p></div>';
    page_foot();
    exit;
}

/* ------------------------------ aksi ------------------------------ */

$pesan = array();
$act   = isset($_POST['act']) ? (string) $_POST['act'] : '';

if ($act === 'selfdestruct') {
    if (@unlink(__FILE__)) {
        page_head('Pemindai Backdoor');
        echo '<div class="v ok"><b>Skrip sudah dihapus dari server.</b><p>Tutup tab ini.</p></div>';
        page_foot();
        exit;
    }
    $pesan[] = array('bad', 'Gagal menghapus diri sendiri. Hapus <code>pindai.php</code> lewat File Manager.');
}

if ($act === 'karantina') {
    $targets = isset($_POST['target']) && is_array($_POST['target']) ? $_POST['target'] : array();
    $ok = 0;
    $gagal = array();
    foreach ($targets as $t) {
        $t = (string) $t;
        if (!within_home($t, $home) || !is_file($t)) {
            $gagal[] = array($t, 'ditolak: di luar direktori home atau bukan berkas');
            continue;
        }
        $baru = $t . '.KARANTINA-' . date('Ymd-His');
        if (@rename($t, $baru)) {
            @chmod($baru, 0000);
            $ok++;
        } else {
            @chmod(dirname($t), 0755);
            if (@rename($t, $baru)) {
                @chmod($baru, 0000);
                $ok++;
            } else {
                $gagal[] = array($t, 'gagal mengganti nama berkas');
            }
        }
    }
    $teks = '<b>' . $ok . ' berkas dikarantina.</b> Berkas diganti namanya menjadi '
          . '<code>&lt;nama asli&gt;.KARANTINA-&lt;tanggal&gt;</code> dan permission-nya dijadikan 000, '
          . 'sehingga tidak bisa dijalankan lagi tetapi masih bisa diperiksa atau dikembalikan.';
    if (count($gagal)) {
        $teks .= '<p>Yang gagal:</p><ul>';
        foreach (array_slice($gagal, 0, 30) as $g) {
            $teks .= '<li><code>' . esc($g[0]) . '</code> - ' . esc($g[1]) . '</li>';
        }
        $teks .= '</ul>';
    }
    $pesan[] = array(count($gagal) ? 'warn' : 'ok', $teks);
}

/* ---------------------------- pemindaian ---------------------------- */

$otomatis = ($act === 'otomatis');

if ($otomatis) {
    $scanDir = $home;
    $budget  = 420;
} else {
    $scanDir = isset($_POST['dir']) && $_POST['dir'] !== '' ? (string) $_POST['dir'] : $home . '/public_html';
    if (!is_dir($scanDir) || !within_home($scanDir, $home)) {
        $scanDir = is_dir($home . '/public_html') ? $home . '/public_html' : $home;
    }
    $budget = isset($_POST['budget']) ? intval($_POST['budget']) : 90;
    if ($budget < 10) {
        $budget = 10;
    }
    if ($budget > 600) {
        $budget = 600;
    }
}
$scanDir = rtrim(realpath($scanDir), '/');

$doScan = ($act === 'pindai' || $act === 'karantina' || $otomatis);

$temuan   = array();
$kosong   = array();
$diperiksa = 0;
$habisWaktu = false;

if ($doScan) {
    $ind  = indikator();
    $mulai = microtime(true);
    $ext  = array('php', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phps', 'pht', 'phar', 'inc', 'suspected');
    $tumpuk = array($scanDir);

    while (count($tumpuk)) {
        if (microtime(true) - $mulai > $budget) {
            $habisWaktu = true;
            break;
        }
        $d = array_pop($tumpuk);
        $items = @scandir($d);
        if (!is_array($items)) {
            continue;
        }
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') {
                continue;
            }
            $p = $d . '/' . $it;
            if (is_link($p)) {
                continue;
            }
            if (is_dir($p)) {
                $tumpuk[] = $p;
                continue;
            }
            $size = @filesize($p);

            // Berkas index yang kosong membuat halaman tampil blank atau error.
            if ($size === 0 && preg_match('#^index\.(php|html?)$#i', $it)) {
                $kosong[] = array('path' => $p, 'mtime' => @filemtime($p));
            }

            $e = strtolower(pathinfo($it, PATHINFO_EXTENSION));
            if (!in_array($e, $ext, true)) {
                continue;
            }
            // Pemindai ini sendiri memuat pola yang dicarinya, jadi dilewati.
            if (@realpath($p) === @realpath(__FILE__)) {
                continue;
            }
            if ($size === false || $size > 6 * 1024 * 1024) {
                continue;
            }
            $diperiksa++;

            $isi = @file_get_contents($p, false, null, 0, 512 * 1024);
            if ($isi === false) {
                continue;
            }

            $cocok = array();
            $tingkat = '';
            foreach ($ind as $i) {
                if (preg_match($i[3], $isi)) {
                    $cocok[] = $i[2];
                    if ($i[1] === 'tinggi') {
                        $tingkat = 'tinggi';
                    } elseif ($tingkat === '') {
                        $tingkat = 'sedang';
                    }
                }
            }
            // Nama berkas berpola acak seperti contoh yang sudah ditemukan.
            if (preg_match('#^[a-z0-9]{8,}Cfm\.php$#i', $it)) {
                $cocok[] = 'Nama berkas berpola acak diakhiri "Cfm.php"';
                $tingkat = 'tinggi';
            }
            if (!count($cocok)) {
                continue;
            }
            $temuan[] = array(
                'path'    => $p,
                'size'    => $size,
                'mtime'   => @filemtime($p),
                'tingkat' => $tingkat,
                'cocok'   => $cocok,
            );
        }
    }

    usort($temuan, function ($a, $b) {
        if ($a['tingkat'] !== $b['tingkat']) {
            return $a['tingkat'] === 'tinggi' ? -1 : 1;
        }
        return $b['mtime'] - $a['mtime'];
    });
}

/* Mode otomatis: karantina sendiri semua temuan bertingkat TINGGI. */
$otoKarantina = array('ok' => 0, 'gagal' => array());
if ($otomatis) {
    foreach ($temuan as $i => $t) {
        if ($t['tingkat'] !== 'tinggi') {
            continue;
        }
        $baru = $t['path'] . '.KARANTINA-' . date('Ymd-His');
        $sukses = @rename($t['path'], $baru);
        if (!$sukses) {
            @chmod(dirname($t['path']), 0755);
            $sukses = @rename($t['path'], $baru);
        }
        if ($sukses) {
            @chmod($baru, 0000);
            $otoKarantina['ok']++;
            $temuan[$i]['karantina'] = $baru;
        } else {
            $otoKarantina['gagal'][] = $t['path'];
        }
    }
}

/* Jadwal cron sering dipakai memasang ulang pintu belakang. */
function baca_cron() {
    $nonaktif = array_map('trim', explode(',', (string) @ini_get('disable_functions')));
    foreach (array('shell_exec', 'exec') as $fn) {
        if (!function_exists($fn) || in_array($fn, $nonaktif, true)) {
            continue;
        }
        if ($fn === 'shell_exec') {
            $out = @shell_exec('crontab -l 2>&1');
        } else {
            $buf = array();
            @exec('crontab -l 2>&1', $buf);
            $out = implode("\n", $buf);
        }
        if (is_string($out) && trim($out) !== '') {
            return $out;
        }
    }
    return null;
}
$cron = $doScan ? baca_cron() : null;

/* ------------------------------ tampilan ------------------------------ */

page_head('Pemindai Backdoor');
echo '<h1>Pemindai Backdoor</h1>';
echo '<p class="sub">Mencari pola khas pintu belakang di berkas PHP. Semua aksi dibatasi di dalam '
   . '<code>' . esc($home) . '</code>.</p>';

foreach ($pesan as $m) {
    echo '<div class="v ' . esc($m[0]) . '">' . $m[1] . '</div>';
}

echo '<div class="box"><form method="post" onsubmit="return confirm(\'Pindai seluruh akun dan karantina otomatis semua temuan tingkat TINGGI?\\n\\nBerkas diganti nama, bukan dihapus, jadi masih bisa dikembalikan.\')">';
echo '<input type="hidden" name="k" value="' . esc($given) . '">';
echo '<input type="hidden" name="act" value="otomatis">';
echo '<p><b>Cara cepat — satu klik.</b> Memindai seluruh direktori home, lalu langsung '
   . 'mengarantina setiap temuan bertingkat TINGGI tanpa perlu Anda centang satu per satu. '
   . 'Karantina hanya mengganti nama berkas dan mematikan permission-nya, jadi selalu bisa dibatalkan.</p>';
echo '<button class="danger" type="submit">Pindai seluruh akun &amp; bersihkan otomatis</button>';
echo '</form></div>';

echo '<div class="box"><form method="post">';
echo '<input type="hidden" name="k" value="' . esc($given) . '">';
echo '<input type="hidden" name="act" value="pindai">';
echo '<p class="muted"><b>Cara manual</b> — pindai satu folder saja dan pilih sendiri apa yang dikarantina.</p>';
echo '<div class="bar"><label>Folder yang dipindai</label>';
echo '<input type="text" name="dir" value="' . esc($scanDir) . '" style="flex:1;min-width:280px">';
echo '<label>Batas waktu (detik)</label><input type="text" name="budget" value="' . esc($budget) . '" size="4">';
echo '<button type="submit">Mulai pindai</button></div>';
echo '<p class="muted">Memindai seluruh home sekaligus bisa lama. Kalau waktu habis sebelum selesai, '
   . 'pindai per folder domain, atau naikkan batas waktunya.</p>';
echo '</form></div>';

if ($doScan) {
    echo '<h2>Hasil</h2>';
    echo '<p class="sub">' . esc($diperiksa) . ' berkas PHP diperiksa di <code>' . esc($scanDir) . '</code>. '
       . '<b>' . count($temuan) . '</b> berkas mencurigakan.'
       . ($habisWaktu ? ' <span class="sedang">Batas waktu tercapai - pemindaian belum selesai.</span>' : '')
       . '</p>';

    if ($otomatis) {
        $jml = $otoKarantina['ok'];
        echo '<div class="v ' . ($jml ? 'ok' : 'warn') . '"><b>' . esc($jml)
           . ' berkas berbahaya sudah dikarantina otomatis.</b>';
        echo '<p>Berkas diganti nama menjadi <code>&lt;nama asli&gt;.KARANTINA-&lt;tanggal&gt;</code> dengan '
           . 'permission <code>000</code>, sehingga tidak bisa dijalankan lagi tetapi masih bisa diperiksa '
           . 'atau dikembalikan.</p>';
        if (count($otoKarantina['gagal'])) {
            echo '<p>Gagal dikarantina (perlu ditangani manual lewat File Manager):</p><ul>';
            foreach (array_slice($otoKarantina['gagal'], 0, 30) as $g) {
                echo '<li><code>' . esc($g) . '</code></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }

    if (count($kosong)) {
        echo '<div class="v warn"><b>' . count($kosong) . ' berkas <code>index</code> berukuran 0 byte.</b>';
        echo '<p>Berkas utama yang kosong membuat halaman tampil blank atau error. Perlu dipulihkan dari '
           . 'backup atau dari berkas asli aplikasinya - jangan instal ulang lewat Softaculous, karena '
           . 'database bisa tertimpa.</p><ul>';
        foreach (array_slice($kosong, 0, 40) as $z) {
            echo '<li><code>' . esc(str_replace($home . '/', '', $z['path'])) . '</code>'
               . ($z['mtime'] ? ' <span class="muted">diubah ' . esc(date('d M Y H:i', $z['mtime'])) . '</span>' : '')
               . '</li>';
        }
        echo '</ul></div>';
    }

    if ($cron !== null) {
        echo '<div class="v warn"><b>Jadwal cron akun ini</b>';
        echo '<p>Hapus baris yang tidak Anda buat lewat cPanel &gt; <b>Cron Jobs</b>. Penyerang biasa '
           . 'memasang cron untuk memasang ulang pintu belakang setelah dibersihkan.</p>';
        echo '<pre style="background:#0f1720;color:#e6edf3;padding:12px;border-radius:6px;overflow:auto;max-height:260px">'
           . esc($cron) . '</pre></div>';
    }

    if (!count($temuan)) {
        echo '<div class="v ok"><b>Tidak ada yang cocok dengan pola yang dikenali.</b>'
           . '<p>Ini bukan jaminan bersih - pemindai hanya mengenali pola yang sudah diketahui. '
           . 'Tetap ganti semua kata sandi dan periksa Cron Jobs serta akun FTP di cPanel.</p></div>';
    } else {
        echo '<form method="post" onsubmit="return confirm(\'Karantina berkas yang dicentang? Berkas diganti nama, bukan dihapus.\')">';
        echo '<input type="hidden" name="k" value="' . esc($given) . '">';
        echo '<input type="hidden" name="dir" value="' . esc($scanDir) . '">';
        echo '<input type="hidden" name="budget" value="' . esc($budget) . '">';
        echo '<input type="hidden" name="act" value="karantina">';
        echo '<table><thead><tr><th style="width:34px"><input type="checkbox" onclick="var b=this.form.querySelectorAll(\'input[name^=target]\');for(var i=0;i<b.length;i++)b[i].checked=this.checked"></th>';
        echo '<th>Berkas</th><th style="width:90px">Tingkat</th><th style="width:150px">Diubah</th><th>Penanda yang cocok</th></tr></thead><tbody>';
        foreach ($temuan as $t) {
            $sudah = isset($t['karantina']);
            echo '<tr><td>';
            if (!$sudah) {
                echo '<input type="checkbox" name="target[]" value="' . esc($t['path']) . '"'
                   . ($t['tingkat'] === 'tinggi' ? ' checked' : '') . '>';
            }
            echo '</td>';
            echo '<td><code>' . esc(str_replace($home . '/', '', $t['path'])) . '</code><br>'
               . '<span class="muted">' . esc(human_bytes($t['size']))
               . ($sudah ? ' &middot; <span class="ok">sudah dikarantina</span>' : '') . '</span></td>';
            echo '<td class="' . esc($t['tingkat']) . '">' . esc(strtoupper($t['tingkat'])) . '</td>';
            echo '<td class="muted">' . esc($t['mtime'] ? date('d M Y H:i', $t['mtime']) : '-') . '</td>';
            echo '<td class="muted">' . esc(implode('; ', $t['cocok'])) . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<div class="bar"><button class="danger" type="submit">Karantina yang dicentang</button>';
        echo '<span class="muted">Berkas diganti nama menjadi <code>*.KARANTINA-tanggal</code> dan permission 000, bukan dihapus.</span></div>';
        echo '</form>';

        echo '<div class="v warn"><b>Periksa dulu sebelum mengarantina.</b>'
           . '<p>Penanda bertingkat SEDANG kadang cocok dengan berkas sah - misalnya pustaka pihak ketiga '
           . 'yang memang memakai base64 panjang. Buka berkasnya lewat File Manager &gt; View bila ragu. '
           . 'Penanda TINGGI hampir selalu benar-benar berbahaya.</p></div>';
    }
}

echo '<h2>Setelah memindai</h2><div class="box"><ol>';
echo '<li><b>Ganti semua kata sandi</b> - cPanel, seluruh akun FTP, user database MySQL, '
   . 'dan admin setiap aplikasi. Selama kata sandi lama masih berlaku, pembersihan akan sia-sia.</li>';
echo '<li><b>cPanel &gt; Cron Jobs</b> - hapus jadwal yang tidak Anda buat. Penyerang sering memasang cron '
   . 'untuk memasang ulang pintu belakang.</li>';
echo '<li><b>cPanel &gt; FTP Accounts</b> dan <b>Email Accounts</b> - hapus akun yang tidak Anda kenal.</li>';
echo '<li><b>Minta backup ke penyedia hosting</b> dari tanggal sebelum berkas mencurigakan pertama muncul.</li>';
echo '<li><b>Perbarui semua aplikasi</b> - WordPress beserta plugin dan tema, SLiMS, Moodle. '
   . 'Celah yang dipakai masuk biasanya ada di aplikasi yang usang.</li>';
echo '</ol></div>';

echo '<h2>Selesai</h2><div class="box">';
echo '<p><b>Jangan tinggalkan skrip ini di server.</b></p>';
echo '<form method="post" onsubmit="return confirm(\'Hapus pindai.php dari server?\')">';
echo '<input type="hidden" name="k" value="' . esc($given) . '">';
echo '<input type="hidden" name="act" value="selfdestruct">';
echo '<button class="danger" type="submit">Hapus skrip ini dari server</button></form></div>';

page_foot();
