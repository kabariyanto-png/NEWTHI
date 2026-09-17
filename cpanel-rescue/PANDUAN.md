# Panduan Perbaikan Hosting cPanel

Masalah yang ditangani:

1. **Akun ini disusupi pintu belakang (backdoor).** Ini yang paling mendesak — lihat Bagian 0.
2. `elibrary-smait.thi.or.id` menampilkan tulisan **"could not find driver"**.
3. Tombol **Delete** di cPanel File Manager tidak bereaksi.

Semua langkah di bawah dikerjakan lewat **browser** (cPanel + File Manager). Tidak ada satu pun
yang memerlukan FTP.

---

## Bagian 0 — Pintu belakang di akun ini (kerjakan lebih dulu)

### Apa yang ditemukan

Di folder `public_html/perpustakaan.thi.or.id` terdapat berkas PHP bernama acak yang bukan
bagian dari SLiMS:

| Berkas | Ukuran | Tanggal |
| --- | --- | --- |
| `bashtdvaz61zoneleuCfm.php` | 146,19 KB | 29 Agu 2026 |
| `lvsoeIj44frkjehrwqygCfm.php` | 5,13 KB | 30 Agu 2026 |
| `uopae61njiuk4gxoaCfm.php` | — | — |

Seluruh berkas asli SLiMS di folder yang sama tertanggal 24 Jun 2026.

Isi `lvsoeIj44frkjehrwqygCfm.php` sudah diperiksa. Berkas itu **loader**: ia mengunduh kode PHP
dari `https://gitlab.com/knightchaos57/logosaja/-/raw/main/ak.txt` (alamatnya disamarkan dalam
base64), lalu menjalankannya dengan `eval()`. Untuk memastikan berhasil, ia mencoba sepuluh cara
berbeda secara berurutan — `file_get_contents`, `curl`, `SplFileObject`, `fopen`, `fsockopen`,
`stream_socket_client`, fungsi `socket_*`, sampai memanggil `curl`/`wget` lewat `proc_open`,
`exec`, `shell_exec`, dan `passthru`.

Nama-nama fungsinya disamarkan lewat pemotongan string (`'file'.'_get'.'_con'.'tent'.'s'`) dan
escape heksadesimal (`"\x65\x78\x65\x63"`) agar lolos dari pemindai sederhana.

Parameter `$_GET['uu']` membuat siapa pun yang mengetahui alamat berkas itu dapat menunjuk ke URL
mana pun dan menjalankan kode apa pun. **Ini kendali penuh atas akun hosting dari jarak jauh**,
dan isi serangannya bisa diganti kapan saja tanpa menyentuh server lagi.

Temuan lain di folder yang sama:

- `index.php` berukuran **0 byte** (diubah 15 Sep 2026) — inilah sebab halaman tidak tampil.
- `.htaccess` berisi aturan permalink **WordPress**, padahal isi foldernya SLiMS. Berkas ini
  sendiri **bersih**, bukan malware.
- `google17f707bafb8849f4.html` — berkas verifikasi Google Search Console. Periksa di
  [Search Console](https://search.google.com/search-console) apakah ada pemilik yang tidak Anda
  kenal; penyerang memakai ini untuk mengklaim situs korban.

### Urutan penanganan

Kerjakan berurutan. Melewati langkah 1 membuat sisanya sia-sia.

**1. Ganti semua kata sandi — paling mendesak.**
cPanel, seluruh akun FTP, user database MySQL, admin SLiMS, admin WordPress setiap situs, dan
akun email. Selama kata sandi lama masih berlaku, penyerang bisa masuk lagi kapan saja.

**2. Jalankan `pindai.php` untuk menemukan sisanya.**
Akun ini berisi belasan domain dalam satu direktori home, sehingga **satu situs yang tembus
berarti seluruh akun terpapar.** Cara memakainya ada di bawah. Pindai `public_html` secara
keseluruhan, lalu karantina semua temuan bertingkat TINGGI.

**3. cPanel → Cron Jobs.**
Hapus jadwal yang tidak Anda buat. Penyerang biasa memasang cron untuk memasang ulang pintu
belakang beberapa menit setelah dihapus — tanpa langkah ini, pembersihan akan terulang terus.

**4. cPanel → FTP Accounts, Email Accounts, dan Manage Team.**
Hapus akun yang tidak Anda kenali.

**5. Minta backup ke penyedia hosting.**
Minta backup akun bertanggal **sebelum 29 Agustus 2026**. Memulihkan dari backup bersih jauh
lebih aman dan lebih cepat daripada membersihkan manual.

**6. Perbarui semua aplikasi.**
WordPress beserta seluruh plugin dan tema, SLiMS, dan Moodle. Celah masuknya hampir selalu ada
pada aplikasi atau plugin yang sudah usang — tanpa langkah ini, situs akan tembus lagi.

### Cara memakai `pindai.php`

1. Buka `pindai.php` dengan Notepad, ganti `GANTI_KATA_SANDI_INI` dengan kata sandi bebas milik
   Anda, simpan.
2. cPanel → **File Manager** → **Upload** ke folder mana pun di `public_html`.
3. Buka `https://domain-anda/pindai.php`, masukkan kata sandi.
4. Isi kolom folder dengan `/home/NAMA-AKUN/public_html`, lalu **Mulai pindai**.
   Kalau batas waktu tercapai sebelum selesai, pindai per folder domain satu per satu.
5. Temuan **TINGGI** sudah tercentang otomatis — periksa sekilas, lalu klik
   **Karantina yang dicentang**.
6. Temuan **SEDANG** perlu Anda lihat isinya dulu (File Manager → klik kanan → View). Pustaka
   pihak ketiga yang sah kadang ikut tertandai.
7. **Selesai memakainya, klik "Hapus skrip ini dari server".**

Karantina **tidak menghapus** berkas — hanya mengganti namanya menjadi
`<nama asli>.KARANTINA-<tanggal>` dan menjadikan permission-nya `000`, sehingga tidak bisa
dijalankan lagi tetapi masih bisa diperiksa atau dikembalikan bila ternyata keliru.

### Memulihkan `perpustakaan.thi.or.id`

Setelah pembersihan selesai, `index.php` yang 0 byte perlu diisi ulang. **Jangan instal ulang
lewat Softaculous** — itu menimpa database. Unduh SLiMS versi yang sama dari
[slims.web.id](https://slims.web.id), lalu salin kembali hanya berkas program yang hilang.
Yang **tidak boleh** ditimpa: `sysconfig.local.inc.php` (pengaturan koneksi database) dan folder
`files/`, `images/`, `repository/` (dokumen, sampul, dan berkas unggahan). Database berisi data
buku dan anggota Anda tidak tersentuh oleh proses ini.

---

## Bagian 1 — "could not find driver"

### Apa artinya

Pesan itu berasal dari PHP, bukan dari website Anda. Aplikasi memanggil database lewat PDO,
lalu PHP menjawab bahwa **driver PDO untuk MySQL tidak ada**.

Konsekuensinya penting: **database dan file website Anda utuh.** Yang bermasalah setelan PHP,
bukan datanya. Jangan menginstal ulang aplikasi lewat Softaculous — itu justru berisiko menimpa
data yang masih baik.

### Kondisi hosting ini (sudah diperiksa langsung dari cPanel)

Hosting ini memakai **CloudLinux PHP Selector** — menu **Select PHP Version** di bagian
**Software**. Menu **MultiPHP Manager** tidak tersedia, jadi versi PHP dan daftar ekstensi di
halaman itu berlaku untuk **seluruh akun**, bukan per domain.

Isi halaman tersebut:

| Hal | Nilai |
| --- | --- |
| Versi PHP akun | **8.3 (current)** |
| `pdo` | aktif |
| `pdo_mysql` | aktif |
| `pdo_sqlite` | aktif |
| `nd_mysqli` | aktif |
| `nd_pdo_mysql` | mati |

`nd_pdo_mysql` yang mati **bukan masalah**. Varian `nd_*` dan varian biasa adalah dua
implementasi driver yang sama dan saling menggantikan — cukup satu yang aktif, dan di sini
`pdo_mysql` sudah aktif. **Jangan mencentang keduanya.**

Kotak peringatan oranye yang muncul di halaman itu (*"igbinary enabled as dependency (redis)"*,
*"msgpack enabled as dependency (redis)"*) juga bukan error, melainkan pemberitahuan bahwa
mengaktifkan `redis` otomatis mengaktifkan ekstensi syaratnya.

### Kalau driver sudah aktif tapi website tetap error

Artinya **website itu tidak berjalan pada PHP 8.3 yang tampil di halaman tersebut.** Ada
override versi PHP yang khusus berlaku untuk folder domain itu — biasanya baris `AddHandler`
atau `SetHandler` di `.htaccess`, atau berkas `php.ini`/`.user.ini` di dalam folder domain.
Override semacam itu tidak terlihat sama sekali dari halaman PHP Selector.

Jalankan `diagnosa.php` (langkah di bawah) untuk melihat versi PHP dan daftar driver PDO yang
**sebenarnya** dipakai domain tersebut, bukan yang ditampilkan halaman PHP Selector. Alat itu
sekaligus menampilkan isi `.htaccess`, `php.ini`, dan `.user.ini` yang ditemukannya di folder
domain dan folder induknya.

### Cara menjalankan `diagnosa.php`

1. Buka `diagnosa.php` (di komputer Anda) dengan Notepad, ganti baris

   ```php
   $PASSWORD = 'GANTI_KATA_SANDI_INI';
   ```

   menjadi kata sandi bebas milik Anda, contoh `$PASSWORD = 'kunci-saya-2026';`. Simpan.
2. cPanel → **File Manager** → masuk ke `public_html/elibrary-smait.thi.or.id`
   → tombol **Upload** → pilih `diagnosa.php`.
3. Buka `https://elibrary-smait.thi.or.id/diagnosa.php`, masukkan kata sandi tadi.
4. Baca bagian **Kesimpulan** di halaman paling atas. Alat itu menyebutkan penyebab pastinya,
   driver PDO apa saja yang benar-benar termuat, dan isi berkas pilihan ekstensi PHP Selector
   (`~/.cl.selector/defaults.cfg`) — cukup untuk memastikan centang tadi benar-benar berlaku.
5. **Selesai memakainya, klik tombol merah "Hapus skrip ini dari server".**

Alat itu juga memeriksa hal-hal yang tidak terlihat dari cPanel: file `.htaccess` atau `php.ini`
nyasar di folder domain yang mematikan ekstensi, kredensial database aplikasi, dan uji koneksi
database langsung.

### Yang sebaiknya tidak dilakukan

- **Jangan** menginstal ulang aplikasi lewat Softaculous sebelum penyebabnya jelas — database lama
  bisa tertimpa.
- **Jangan** menghapus folder `public_html/elibrary-smait.thi.or.id`. File-nya tidak rusak.
- **Jangan** membuka berkas mencurigakan lewat alamat webnya di browser — itu menjalankannya.
  Gunakan File Manager → klik kanan → **View**, yang hanya menampilkan isinya.

---

## Bagian 2 — Delete di File Manager tidak berfungsi

### Kenapa bisa terjadi

cPanel File Manager tidak langsung menghapus file. Secara bawaan ia **memindahkan file ke folder
`.trash`** di direktori home. Kalau pemindahan itu gagal, tombol Delete tampak diam saja tanpa
pesan error. Penyebab tersering, berurutan dari yang paling sering:

1. **Kuota disk atau jumlah file (inode) sudah penuh** — tidak ada ruang untuk menyalin ke `.trash`.
2. **Folder `.trash` rusak permission-nya** atau dimiliki user lain.
3. **Ekstensi pemblokir iklan di Chrome** memblokir permintaan `fileop` milik File Manager.
4. **File dimiliki user lain** (misalnya dibuat oleh proses PHP dengan user berbeda).

### Perbaikan A — lewati kotak sampah

Saat kotak konfirmasi penghapusan muncul, **centang
"Skip the trash bin and permanently delete the files"** sebelum menekan Confirm.
Ini melewati `.trash` sepenuhnya dan menyelesaikan penyebab nomor 1 dan 2 sekaligus.

### Perbaikan B — periksa kuota, lalu kosongkan `.trash`

1. cPanel → **Disk Usage**. Lihat apakah pemakaian sudah mentok di kuota.
   Cek juga **File Usage / Inodes** di halaman statistik sebelah kiri cPanel.
2. cPanel → **File Manager** → menu **Settings** (kanan atas) → centang **Show Hidden Files** → Save.
3. Masuk ke direktori **Home**, cari folder `.trash`, hapus isinya dengan cara Perbaikan A di atas.

### Perbaikan C — uji di jendela penyamaran

Buka cPanel di **jendela Incognito** Chrome (`Ctrl + Shift + N`) lalu coba hapus lagi.
Kalau di sana berhasil, penyebabnya ekstensi browser — matikan pemblokir iklan Anda untuk
domain cPanel tersebut.

### Perbaikan D — pakai `hapus.php`

Kalau A sampai C tetap gagal, gunakan alat yang disertakan di folder ini. Alat ini menghapus
file **langsung dan permanen tanpa melewati `.trash`**, sehingga tetap bekerja walaupun kuota penuh.

1. Buka `hapus.php` dengan Notepad, ganti `GANTI_KATA_SANDI_INI` dengan kata sandi bebas milik Anda, simpan.
2. Upload lewat File Manager ke folder mana saja di dalam `public_html`.
3. Buka `https://domain-anda/hapus.php`, masukkan kata sandi.
4. Telusuri folder, centang item yang mau dihapus, klik **Hapus permanen yang dicentang**.
   Folder ikut terhapus beserta seluruh isinya.
   - Tombol **Kosongkan .trash sekarang** membereskan kotak sampah yang macet.
   - Item bertanda ⚠ berarti permission-nya kurang; alat ini otomatis melonggarkan permission
     lalu mencoba lagi, dan ada kotak **Perbaiki permission** untuk kasus yang membandel.
5. **Setelah selesai, klik "Hapus skrip ini dari server".**

---

## Catatan keamanan

`diagnosa.php`, `hapus.php`, dan `pindai.php` adalah **alat sementara**, bukan bagian dari website.

- Keduanya minta kata sandi dan menolak jalan sebelum Anda mengisinya.
- `hapus.php` mengunci semua operasi di dalam direktori home Anda; path `../`, symlink yang
  menunjuk keluar, dan direktori home itu sendiri ditolak.
- Keduanya mengirim header `noindex` supaya tidak terindeks Google.

Meski begitu, **hapus ketiga berkas dari server begitu pekerjaan selesai**. Masing-masing punya
tombol hapus-diri di bagian bawah halaman. Jangan pakai kata sandi yang sama dengan cPanel Anda —
terlebih karena akun ini sedang dalam kondisi tersusupi.
