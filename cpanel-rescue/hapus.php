<?php
/**
 * hapus.php - Pengganti fungsi Delete cPanel File Manager.
 *
 * Menghapus file/folder secara LANGSUNG dan PERMANEN, tanpa melewati folder
 * .trash - jadi tetap bekerja walaupun kuota disk penuh atau .trash rusak,
 * yang merupakan penyebab paling umum tombol Delete di File Manager mogok.
 *
 * Kompatibel PHP 5.6 ke atas. Semua operasi dibatasi di dalam direktori home.
 *
 * CARA PAKAI
 *   1. Ganti nilai $PASSWORD di bawah ini dengan kata sandi bebas milik Anda.
 *   2. Upload lewat cPanel > File Manager > Upload ke folder mana pun di public_html.
 *   3. Buka https://domain-anda/hapus.php lalu masukkan kata sandi tadi.
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
    $u = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($n >= 1024 && $i < count($u) - 1) {
        $n /= 1024;
        $i++;
    }
    return round($n, 1) . ' ' . $u[$i];
}

function perms_of($path) {
    $p = @fileperms($path);
    return $p === false ? '----' : substr(sprintf('%o', $p), -4);
}

function owner_name($path) {
    $uid = @fileowner($path);
    if ($uid === false) {
        return '-';
    }
    if (function_exists('posix_getpwuid')) {
        $info = @posix_getpwuid($uid);
        if (is_array($info) && isset($info['name'])) {
            return $info['name'];
        }
    }
    return (string) $uid;
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
        $doc = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $pos = strpos($doc, '/public_html');
        if ($pos !== false) {
            $candidates[] = substr($doc, 0, $pos);
        }
    }
    $candidates[] = dirname(dirname(dirname(__FILE__)));
    foreach ($candidates as $c) {
        if ($c && is_dir($c)) {
            $r = realpath($c);
            if ($r !== false) {
                return rtrim($r, '/');
            }
        }
    }
    return '';
}

/**
 * Benar-benar berada di dalam home? realpath() dipakai supaya "../" dan
 * symlink yang menunjuk keluar home ikut tertolak.
 */
function within_home($path, $home) {
    if ($home === '') {
        return false;
    }
    $rp = @realpath($path);
    if ($rp === false) {
        return false;
    }
    return $rp === $home || strpos($rp, $home . '/') === 0;
}

/** Symlink dihapus apa adanya - yang dicek adalah folder induknya. */
function link_within_home($path, $home) {
    return within_home(dirname($path), $home);
}

/**
 * Hapus rekursif. Kalau unlink/rmdir gagal karena bit tulis induknya hilang,
 * permission-nya dilonggarkan sekali lalu dicoba ulang.
 */
function hapus_rekursif($path, &$stat, $depth = 0) {
    if ($depth > 64) {
        $stat['gagal'][] = array($path, 'kedalaman folder melebihi batas aman');
        return false;
    }

    if (is_link($path)) {
        if (@unlink($path)) {
            $stat['file']++;
            return true;
        }
        $stat['gagal'][] = array($path, 'symlink tidak bisa dihapus');
        return false;
    }

    if (is_file($path)) {
        $size = @filesize($path);
        if (@unlink($path)) {
            $stat['file']++;
            $stat['bytes'] += ($size !== false ? $size : 0);
            return true;
        }
        @chmod(dirname($path), 0755);
        @chmod($path, 0644);
        if (@unlink($path)) {
            $stat['file']++;
            $stat['bytes'] += ($size !== false ? $size : 0);
            return true;
        }
        $stat['gagal'][] = array($path, 'file tidak bisa dihapus (pemilik ' . owner_name($path) . ', permission ' . perms_of($path) . ')');
        return false;
    }

    if (is_dir($path)) {
        $items = @scandir($path);
        if ($items === false) {
            @chmod($path, 0755);
            $items = @scandir($path);
        }
        if ($items === false) {
            $stat['gagal'][] = array($path, 'isi folder tidak bisa dibaca');
            return false;
        }
        $bersih = true;
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') {
                continue;
            }
            if (!hapus_rekursif($path . '/' . $it, $stat, $depth + 1)) {
                $bersih = false;
            }
        }
        if (!$bersih) {
            return false;
        }
        if (@rmdir($path)) {
            $stat['folder']++;
            return true;
        }
        @chmod($path, 0755);
        if (@rmdir($path)) {
            $stat['folder']++;
            return true;
        }
        $stat['gagal'][] = array($path, 'folder tidak bisa dihapus (pemilik ' . owner_name($path) . ', permission ' . perms_of($path) . ')');
        return false;
    }

    $stat['gagal'][] = array($path, 'bukan file maupun folder, atau sudah tidak ada');
    return false;
}

function ukuran_folder($path, $depth = 0) {
    if ($depth > 12) {
        return 0;
    }
    $total = 0;
    $items = @scandir($path);
    if (!is_array($items)) {
        return 0;
    }
    foreach ($items as $it) {
        if ($it === '.' || $it === '..') {
            continue;
        }
        $p = $path . '/' . $it;
        if (is_link($p)) {
            continue;
        }
        if (is_dir($p)) {
            $total += ukuran_folder($p, $depth + 1);
        } else {
            $s = @filesize($p);
            $total += ($s !== false ? $s : 0);
        }
    }
    return $total;
}

/* ---------------------------- tampilan dasar --------------------------- */

function page_head($title) {
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="robots" content="noindex,nofollow">';
    echo '<title>' . esc($title) . '</title><style>';
    echo 'body{font:14px/1.6 -apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f4f6f8;color:#1b2733}';
    echo '.wrap{max-width:1000px;margin:0 auto;padding:24px 16px 64px}';
    echo 'h1{font-size:21px;margin:0 0 4px}h2{font-size:16px;margin:26px 0 8px;padding-bottom:6px;border-bottom:1px solid #dde3e9}';
    echo '.sub{color:#5b6b7c;margin:0 0 18px}';
    echo 'table{border-collapse:collapse;width:100%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.06);border-radius:6px;overflow:hidden}';
    echo 'th,td{text-align:left;padding:7px 10px;border-bottom:1px solid #eef1f4;vertical-align:middle}';
    echo 'thead th{background:#fafbfc;font-weight:600;color:#40515f;font-size:12.5px;text-transform:uppercase;letter-spacing:.03em}';
    echo '.box{background:#fff;border-radius:6px;padding:14px 16px;box-shadow:0 1px 2px rgba(0,0,0,.06);margin-bottom:12px}';
    echo '.ok{color:#186a3b;font-weight:600}.bad{color:#a4262c;font-weight:600}.warn{color:#8a6100;font-weight:600}';
    echo '.v{border-left:4px solid #ccc;padding:12px 16px;margin:0 0 12px;background:#fff;border-radius:0 6px 6px 0}';
    echo '.v.bad{border-left-color:#a4262c}.v.ok{border-left-color:#186a3b}.v.warn{border-left-color:#d79f00}';
    echo 'code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:12.5px}';
    echo 'input[type=password]{padding:9px 10px;border:1px solid #c6ced6;border-radius:5px;font-size:14px;min-width:240px}';
    echo 'button{padding:8px 14px;border:0;border-radius:5px;background:#2b6cb0;color:#fff;font-size:13.5px;cursor:pointer}';
    echo 'button.danger{background:#a4262c}button.link{background:none;color:#2b6cb0;padding:0;font-size:14px;text-align:left;text-decoration:underline}';
    echo '.muted{color:#7a8894;font-size:12.5px}.bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:10px 0}';
    echo '</style></head><body><div class="wrap">';
}

function page_foot() {
    echo '</div></body></html>';
}

/* ---------------------------- otentikasi --------------------------- */

$setupNeeded = ($PASSWORD === 'GANTI' . '_KATA_SANDI_INI' || $PASSWORD === '');
$given  = isset($_POST['k']) ? (string) $_POST['k'] : '';
$authed = (!$setupNeeded && $given !== '' && safe_equals($PASSWORD, $given));

if (!$authed) {
    page_head('Alat Hapus File');
    echo '<h1>Alat Hapus File</h1>';
    if ($setupNeeded) {
        echo '<div class="v bad"><b>Skrip belum disiapkan</b>';
        echo '<p>Buka file <code>hapus.php</code> di cPanel &gt; File Manager, klik kanan &gt; <b>Edit</b>, '
           . 'lalu ganti <code>GANTI_KATA_SANDI_INI</code> dengan kata sandi bebas milik Anda. Simpan, lalu muat ulang halaman ini.</p></div>';
    } else {
        if ($given !== '') {
            echo '<div class="v bad"><b>Kata sandi salah.</b></div>';
        }
        echo '<div class="box"><form method="post">';
        echo '<p>Masukkan kata sandi yang Anda tulis di dalam file <code>hapus.php</code>.</p>';
        echo '<input type="password" name="k" autofocus> <button type="submit">Masuk</button>';
        echo '</form></div>';
    }
    page_foot();
    exit;
}

$home = home_dir();
if ($home === '') {
    page_head('Alat Hapus File');
    echo '<div class="v bad"><b>Direktori home tidak terdeteksi.</b><p>Skrip berhenti demi keamanan.</p></div>';
    page_foot();
    exit;
}

/* --------------------------- aksi --------------------------- */

$act    = isset($_POST['act']) ? (string) $_POST['act'] : '';
$pesan  = array();
$selfDeleted = false;

if ($act === 'selfdestruct') {
    if (@unlink(__FILE__)) {
        $selfDeleted = true;
        $pesan[] = array('ok', 'Skrip <code>hapus.php</code> sudah dihapus dari server. Tutup tab ini.');
    } else {
        $pesan[] = array('bad', 'Gagal menghapus diri sendiri. Hapus <code>hapus.php</code> lewat File Manager.');
    }
}

if ($act === 'del') {
    $targets = isset($_POST['target']) && is_array($_POST['target']) ? $_POST['target'] : array();
    if (!count($targets)) {
        $pesan[] = array('warn', 'Tidak ada item yang dicentang.');
    } else {
        $stat = array('file' => 0, 'folder' => 0, 'bytes' => 0, 'gagal' => array());
        foreach ($targets as $t) {
            $t = (string) $t;
            if (is_link($t) ? !link_within_home($t, $home) : !within_home($t, $home)) {
                $stat['gagal'][] = array($t, 'ditolak: berada di luar direktori home');
                continue;
            }
            if (realpath($t) === $home) {
                $stat['gagal'][] = array($t, 'ditolak: direktori home tidak boleh dihapus');
                continue;
            }
            hapus_rekursif($t, $stat);
        }
        $ringkas = $stat['file'] . ' file dan ' . $stat['folder'] . ' folder terhapus ('
                 . human_bytes($stat['bytes']) . ' dibebaskan).';
        if (count($stat['gagal'])) {
            $d = '<p>' . esc($ringkas) . '</p><p>Yang gagal:</p><ul>';
            foreach (array_slice($stat['gagal'], 0, 40) as $g) {
                $d .= '<li><code>' . esc($g[0]) . '</code> - ' . esc($g[1]) . '</li>';
            }
            if (count($stat['gagal']) > 40) {
                $d .= '<li>... dan ' . (count($stat['gagal']) - 40) . ' lainnya</li>';
            }
            $d .= '</ul>';
            $pesan[] = array('warn', '<b>Sebagian berhasil dihapus</b>' . $d);
        } else {
            $pesan[] = array('ok', '<b>Berhasil.</b> ' . esc($ringkas));
        }
    }
}

if ($act === 'emptytrash') {
    $trash = $home . '/.trash';
    if (!is_dir($trash)) {
        $pesan[] = array('warn', 'Folder <code>.trash</code> tidak ada, jadi tidak ada yang dikosongkan.');
    } else {
        $stat = array('file' => 0, 'folder' => 0, 'bytes' => 0, 'gagal' => array());
        $items = @scandir($trash);
        if (is_array($items)) {
            foreach ($items as $it) {
                if ($it === '.' || $it === '..') {
                    continue;
                }
                hapus_rekursif($trash . '/' . $it, $stat);
            }
        }
        @chmod($trash, 0755);
        $pesan[] = array(count($stat['gagal']) ? 'warn' : 'ok',
            '<b>Folder .trash dikosongkan.</b> ' . esc($stat['file'] . ' file, ' . $stat['folder']
            . ' folder, ' . human_bytes($stat['bytes']) . ' dibebaskan.')
            . (count($stat['gagal']) ? ' ' . count($stat['gagal']) . ' item gagal dihapus.' : ''));
    }
}

if ($act === 'chmod') {
    $t    = isset($_POST['path']) ? (string) $_POST['path'] : '';
    $mode = isset($_POST['mode']) ? (string) $_POST['mode'] : '';
    if (!within_home($t, $home)) {
        $pesan[] = array('bad', 'Ditolak: target berada di luar direktori home.');
    } elseif (!preg_match('/^[0-7]{3,4}$/', $mode)) {
        $pesan[] = array('bad', 'Mode permission tidak sah.');
    } elseif (@chmod($t, intval($mode, 8))) {
        $pesan[] = array('ok', 'Permission <code>' . esc($t) . '</code> diubah menjadi ' . esc($mode) . '.');
    } else {
        $pesan[] = array('bad', 'Gagal mengubah permission <code>' . esc($t) . '</code>.');
    }
}

/* --------------------------- folder aktif --------------------------- */

$dir = isset($_POST['dir']) ? (string) $_POST['dir'] : $home . '/public_html';
if (!is_dir($dir) || !within_home($dir, $home)) {
    $dir = is_dir($home . '/public_html') ? $home . '/public_html' : $home;
}
$dir = rtrim(realpath($dir), '/');
if ($dir === '') {
    $dir = $home;
}

$entries = array();
$items = @scandir($dir);
if (is_array($items)) {
    foreach ($items as $it) {
        if ($it === '.' || $it === '..') {
            continue;
        }
        $p = $dir . '/' . $it;
        $isDir = (!is_link($p) && is_dir($p));
        $entries[] = array(
            'nama'   => $it,
            'path'   => $p,
            'dir'    => $isDir,
            'link'   => is_link($p),
            'size'   => $isDir ? null : @filesize($p),
            'mtime'  => @filemtime($p),
            'perms'  => perms_of($p),
            'owner'  => owner_name($p),
            'writable' => @is_writable($p),
        );
    }
}
usort($entries, function ($a, $b) {
    if ($a['dir'] !== $b['dir']) {
        return $a['dir'] ? -1 : 1;
    }
    return strcasecmp($a['nama'], $b['nama']);
});

/* ------------------------------ render ------------------------------ */

page_head('Alat Hapus File');
echo '<h1>Alat Hapus File</h1>';
echo '<p class="sub">Menghapus permanen tanpa melewati <code>.trash</code>, jadi tetap jalan walau kuota disk penuh. '
   . 'Semua aksi dibatasi di dalam <code>' . esc($home) . '</code>.</p>';

foreach ($pesan as $m) {
    echo '<div class="v ' . esc($m[0]) . '">' . $m[1] . '</div>';
}

if ($selfDeleted) {
    page_foot();
    exit;
}

$trashDir = $home . '/.trash';
$trashSize = is_dir($trashDir) ? ukuran_folder($trashDir) : null;
echo '<div class="box"><b>Folder .trash</b> - ';
if ($trashSize === null) {
    echo 'belum ada (normal).';
} else {
    echo 'berisi kira-kira <b>' . esc(human_bytes($trashSize)) . '</b>, permission ' . esc(perms_of($trashDir))
       . '. Mengosongkannya sering langsung memulihkan tombol Delete di File Manager.';
    echo '<div class="bar"><form method="post" onsubmit="return confirm(\'Kosongkan seluruh isi .trash secara permanen?\')">';
    echo '<input type="hidden" name="k" value="' . esc($given) . '">';
    echo '<input type="hidden" name="dir" value="' . esc($dir) . '">';
    echo '<input type="hidden" name="act" value="emptytrash">';
    echo '<button class="danger" type="submit">Kosongkan .trash sekarang</button></form></div>';
}
echo '</div>';

// jalur navigasi
echo '<h2>Lokasi</h2><div class="box"><div class="bar">';
$rel = ($dir === $home) ? '' : substr($dir, strlen($home) + 1);
$parts = $rel === '' ? array() : explode('/', $rel);
$acc = $home;
echo '<form method="post" style="display:inline"><input type="hidden" name="k" value="' . esc($given) . '">'
   . '<input type="hidden" name="dir" value="' . esc($home) . '"><button class="link" type="submit">home</button></form>';
foreach ($parts as $part) {
    $acc .= '/' . $part;
    echo '<span class="muted">/</span>';
    echo '<form method="post" style="display:inline"><input type="hidden" name="k" value="' . esc($given) . '">'
       . '<input type="hidden" name="dir" value="' . esc($acc) . '"><button class="link" type="submit">' . esc($part) . '</button></form>';
}
if ($dir !== $home) {
    echo '<span class="muted">&nbsp;|&nbsp;</span>';
    echo '<form method="post" style="display:inline"><input type="hidden" name="k" value="' . esc($given) . '">'
       . '<input type="hidden" name="dir" value="' . esc(dirname($dir)) . '"><button class="link" type="submit">naik satu level</button></form>';
}
echo '</div></div>';

echo '<h2>Isi folder (' . count($entries) . ' item)</h2>';
echo '<form method="post" onsubmit="return confirm(\'Hapus PERMANEN semua item yang dicentang? Tindakan ini tidak bisa dibatalkan.\')">';
echo '<input type="hidden" name="k" value="' . esc($given) . '">';
echo '<input type="hidden" name="dir" value="' . esc($dir) . '">';
echo '<input type="hidden" name="act" value="del">';
echo '<table><thead><tr><th style="width:34px"><input type="checkbox" onclick="var b=this.form.querySelectorAll(\'input[name^=target]\');for(var i=0;i<b.length;i++)b[i].checked=this.checked"></th>';
echo '<th>Nama</th><th style="width:90px">Ukuran</th><th style="width:150px">Diubah</th><th style="width:150px">Perm / Pemilik</th></tr></thead><tbody>';

if (!count($entries)) {
    echo '<tr><td colspan="5" class="muted">Folder kosong.</td></tr>';
}
foreach ($entries as $e) {
    echo '<tr><td><input type="checkbox" name="target[]" value="' . esc($e['path']) . '"></td><td>';
    if ($e['dir']) {
        echo '<form method="post" style="display:inline"><input type="hidden" name="k" value="' . esc($given) . '">';
        echo '<input type="hidden" name="dir" value="' . esc($e['path']) . '">';
        echo '<button class="link" type="submit">' . esc($e['nama']) . '/</button></form>';
    } else {
        echo esc($e['nama']);
        if ($e['link']) {
            echo ' <span class="muted">(symlink)</span>';
        }
    }
    if (!$e['writable']) {
        echo ' <span class="warn" title="tidak writable">&#9888;</span>';
    }
    echo '</td><td class="muted">' . ($e['dir'] ? '-' : esc(human_bytes($e['size']))) . '</td>';
    echo '<td class="muted">' . esc($e['mtime'] ? date('d M Y H:i', $e['mtime']) : '-') . '</td>';
    echo '<td class="muted">' . esc($e['perms']) . ' / ' . esc($e['owner']) . '</td></tr>';
}
echo '</tbody></table>';
echo '<div class="bar"><button class="danger" type="submit">Hapus permanen yang dicentang</button>';
echo '<span class="muted">Folder dihapus beserta seluruh isinya.</span></div></form>';

echo '<h2>Perbaiki permission</h2><div class="box">';
echo '<p class="muted">Pakai ini kalau ada item bertanda &#9888; (tidak writable) sehingga tidak bisa dihapus. '
   . 'Nilai lazim: <code>755</code> untuk folder, <code>644</code> untuk file.</p>';
echo '<form method="post"><input type="hidden" name="k" value="' . esc($given) . '">';
echo '<input type="hidden" name="dir" value="' . esc($dir) . '">';
echo '<input type="hidden" name="act" value="chmod">';
echo '<div class="bar"><input type="text" name="path" placeholder="path lengkap, contoh: ' . esc($dir) . '/folder" style="flex:1;min-width:280px;padding:9px 10px;border:1px solid #c6ced6;border-radius:5px">';
echo '<input type="text" name="mode" value="755" size="5" style="padding:9px 10px;border:1px solid #c6ced6;border-radius:5px">';
echo '<button type="submit">Ubah permission</button></div></form></div>';

echo '<h2>Selesai</h2><div class="box">';
echo '<p><b>Jangan tinggalkan skrip ini di server.</b> Siapa pun yang menebak kata sandinya bisa menghapus file Anda.</p>';
echo '<form method="post" onsubmit="return confirm(\'Hapus hapus.php dari server?\')">';
echo '<input type="hidden" name="k" value="' . esc($given) . '">';
echo '<input type="hidden" name="act" value="selfdestruct">';
echo '<button class="danger" type="submit">Hapus skrip ini dari server</button></form></div>';

page_foot();
