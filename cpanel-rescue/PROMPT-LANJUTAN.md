Saya perlu bantuan melanjutkan pemulihan akun hosting yang diretas. Sesi sebelumnya
sudah melumpuhkan serangannya; sekarang tinggal memulihkan situs-situsnya.

## Akses dan batasan

- Hosting: **Hostinger, paket cPanel**. Akun: **`thiiori`**, server **`srv175`**,
  home **`/home/thiiori`**, domain utama **`thi.or.id`** (~23 subdomain dalam satu akun).
- **Saya punya akses SSH** lewat cPanel → Terminal. Ini jalur kerja utama —
  beri saya perintah, saya salin hasilnya. cPanel File Manager **tidak bisa dipakai**
  (semua upload/delete gagal "Permission denied"; Hostinger sedang menyelidiki gangguan ini).
- Jangan minta password saya. Berikan perintah atau langkah klik, saya yang menjalankan.
- Aplikasi: WordPress (mayoritas situs), SLiMS 9 v9.8.0 (`perpustakaan.thi.or.id`),
  Moodle (`lms.thi.or.id`), dan aplikasi PHP custom (`tkit.thi.or.id`).

## Yang sudah dikerjakan (JANGAN diulang)

Serangan sudah dilumpuhkan dan terbukti mati:

1. **Persistence dimatikan.** Malware menyuntik `~/.bashrc` dan `~/.bash_profile` dengan
   4 blok `php -r` yang berjalan tiap login. Fungsinya: memeriksa apakah backdoor masih ada,
   kalau dihapus ia memasang ulang dari salinan terenkripsi, lalu `chmod 0444` filenya dan
   `chmod 0555` foldernya, sekaligus menimpa `index.php` situs. Kedua berkas sudah dibersihkan.
2. **16 folder cadangan dihancurkan** di `/var/tmp/.cache_*`, `/var/tmp/.run_*`,
   `/tmp/.cache_*`, `/tmp/.sess_*`, `/dev/shm/.shm_*`.
3. **Terbukti mati**: setelah logout-login, `jobs` kosong dan `ps -ef | grep "php -r"` kosong.
4. **372 file backdoor dikarantina** (rename ke `*.KARANTINA` + `chmod 000`), tersebar di
   23 situs. Verifikasi: `find ~/public_html -name "*Cfm.php" -type f | wc -l` → 0.
5. **Bukti diarsipkan**: `~/bukti-serangan.tar.gz`, `~/bukti-backdoor-file.tar.gz`,
   `~/bukti-wp-blog-header-rusak.php`, `~/daftar-backdoor.txt`.
6. **Semua folder terkunci dibuka** (`chmod u+rwx` rekursif); tidak ada sisa.
7. `smpit-ha.thi.or.id`: `index.php` dan `wp-blog-header.php` (yang disusupi, 2001 byte
   vs normal ~351) sudah dipulihkan ke isi asli WordPress.

Detail serangan: loader mengunduh PHP dari
`https://gitlab.com/knightchaos57/logosaja/-/raw/main/ak.txt` lalu `eval()`-nya, memakai 10
metode fallback. Penanda payload: **`R3NV024`**. Infeksi mulai **16 Agustus 2026**.
`index.php` beberapa situs dikosongkan **15 September 2026**.

## Tugas yang tersisa, berurutan

### 1. Pindah PHP 8.3 → 8.2 (PALING PENTING, dampak terbesar)

**Akar masalah semua situs WordPress mati.** Sudah dibuktikan di server:

```
/opt/alt/php80/usr/bin/php   mysqli OK    | pdo_mysql OK
/opt/alt/php81/usr/bin/php   mysqli OK    | pdo_mysql OK
/opt/alt/php82/usr/bin/php   mysqli OK    | pdo_mysql OK
/opt/alt/php83/usr/bin/php   mysqli TIDAK | pdo_mysql OK   <-- versi yang dipakai akun
/opt/alt/php84/usr/bin/php   mysqli OK    | pdo_mysql OK
```

PHP 8.3 di server ini **tidak punya `mysqli`**, jadi semua WordPress menampilkan
*"Your PHP installation appears to be missing the MySQL extension"*. Di halaman
**Select PHP Version** kotak `mysqli` terlihat tercentang, tetapi ekstensinya tidak termuat.

Hosting ini **tidak punya MultiPHP Manager** — hanya **Select PHP Version**
(CloudLinux PHP Selector), dan setelannya berlaku untuk **seluruh akun**.

Yang perlu dicoba, berurutan:
- a. cPanel → Select PHP Version → dropdown `8.3 (current)` → pilih `8.2`.
     Saya sudah mencoba tapi versinya tidak berpindah — bantu saya pastikan kenapa.
- b. Kalau dropdown gagal: override per-domain lewat `.htaccess`. Uji dulu di SATU situs,
     selalu buat cadangan, dan siapkan cara membatalkannya:
     ```bash
     cd ~/public_html/smpit-ha.thi.or.id
     cp .htaccess .htaccess.backup
     sed -i '1i AddHandler application/x-httpd-alt-php82___lsphp .php' .htaccess
     ```
     Uji: buka `https://smpit-ha.thi.or.id/cek-php-9x.php` (file uji sudah ada, isinya
     menampilkan versi PHP + status mysqli/pdo_mysql). Kalau 500, kembalikan dari backup.
- c. Kalau dua-duanya gagal, bantu saya menyusun laporan ke Hostinger.

Setelah mysqli hidup, **uji semua situs**: `smpit-ha`, `newthi`, `sdit`, `sditmoti`,
`smait`, `smpit`, `set`, `staging`, `lms`, `elibrary-smait`, `asset`, `admin`, `rahk`,
`pustaka`, `visitasi`, `sekolahbalita`, `sditbaithi`, `SIMBTQ`.

### 2. Ganti semua password

cPanel, seluruh akun FTP, user database MySQL, admin WordPress tiap situs, akun email.
Belum dikerjakan. Cara masuk penyerang belum diketahui, jadi ini penting.

### 3. Pulihkan `index.php` yang 0 byte

- **`perpustakaan.thi.or.id`** — SLiMS 9 v9.8.0 (`SENAYAN_VERSION_TAG` di
  `sysconfig.inc.php`). Butuh `index.php` asli dari rilis v9.8.0 (slims.web.id / GitHub).
  **Jangan instal ulang lewat Softaculous** — database berisi data buku dan anggota.
  Jangan timpa: `sysconfig.local.inc.php`, dan folder `files/`, `images/`, `repository/`.
  Catatan: folder ini punya `.htaccess` permalink WordPress padahal isinya SLiMS — perlu dicek.
- **`tkit.thi.or.id`** — aplikasi PHP custom, bukan WordPress/SLiMS. Isi folder:
  `app/`, `config/`, `cron/`, `database/`, `docs/`, `includes/`, `storage/`, `assets/`,
  `udpate/`, plus `admissions.php`, `profile.php`, `sentra.php`, `services.php`, `README.md`.
  Perlu diperiksa `.htaccess` dan `app/` untuk merekonstruksi `index.php`.
  Ada juga `cek-runtime-8f3a.php` yang belum jelas asalnya.
- `elibrary/` isinya **hanya 3 file karantina**, tidak ada file situs sama sekali.
- `admin.thi.or.id` hanya berisi `wp-admin`, `wp-signup.php`, `system`, `cgi-bin` — perlu dicek.

### 4. Bersihkan sisa malware WordPress

Ditemukan tapi **belum dikarantina** (perintahnya belum sempat dijalankan):
- `wp-content/plugins/wp-link-helper/` di 12 lokasi (berisi `wlh-vault`, `wlh-adopt`,
  `wlh-config`, `wlh-recovery`) — bukan plugin sah
- `wp-content/wp-<hex>-prev/` — 12 folder nama acak
- `wp-content/mu-plugins/wp-<hex>.php` di 8+ situs — `mu-plugins` aktif otomatis
- `wp-includes/mail/` di `sdit` dan `sdit/_lama` — mailer spam, **sudah** dikarantina
- 247 folder tersembunyi `.well-known/.<32-hex>system/` masih ada (isinya sudah dikarantina)

Perlu juga memeriksa file inti WordPress lain yang disusupi. Penanda yang **berhasil**:
ukuran menyimpang (`wp-blog-header.php` normal ~351 byte, `wp-load.php` ~3900,
`wp-settings.php` ~33000). Penanda yang **gagal**: izin `0444`/`0555` — terlalu banyak
(32.382 file), bukan jejak penyerang.

### 5. Update WordPress + semua plugin

Celah masuk kemungkinan dari WordPress/plugin usang — ada backdoor di
`newthi.thi.or.id/wp-content/uploads/`, folder yang bisa ditulis publik.

### 6. Lapor ke Hostinger

Sudah ada tiket berjalan. Yang perlu disampaikan:
- PHP 8.3 di srv175 tidak punya `mysqli` (php80/81/82/84 punya) — mohon dipasang
  atau bantu pindah ke 8.2
- File Manager gagal semua operasi tulis padahal kuota lapang
  (disk 41,43/50 GB, inode 489.460/1.000.000) dan **SSH bisa menulis normal**
- Minta backup **sebelum 16 Agustus 2026**, dan minta backup itu ditahan
  agar tidak terhapus rotasi otomatis
- Minta pemindaian malware sisi server dan pengecekan infeksi lintas-akun

## Cara kerja yang saya harapkan

- Beri perintah SSH siap salin, satu blok per langkah, dengan penanda `=== SELESAI ===`
  di akhir supaya saya tahu kapan sudah rampung.
- Untuk operasi merusak: **karantina (rename + chmod 000), jangan hapus permanen** —
  Hostinger memintanya untuk bukti, dan supaya bisa dibatalkan kalau salah tangkap.
- Arsipkan bukti sebelum mengubah apa pun.
- Verifikasi tiap langkah sebelum lanjut.
- Jawab dalam bahasa Indonesia.

Mulai dari **tugas nomor 1** — itu yang menghidupkan paling banyak situs sekaligus.
