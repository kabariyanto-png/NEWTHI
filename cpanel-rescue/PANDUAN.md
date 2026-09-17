# Panduan Perbaikan Hosting cPanel

Dua masalah yang ditangani:

1. `elibrary-smait.thi.or.id` menampilkan tulisan **"could not find driver"**.
2. Tombol **Delete** di cPanel File Manager tidak bereaksi.

Semua langkah di bawah dikerjakan lewat **browser** (cPanel + File Manager). Tidak ada satu pun
yang memerlukan FTP.

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

`diagnosa.php` dan `hapus.php` adalah **alat sementara**, bukan bagian dari website.

- Keduanya minta kata sandi dan menolak jalan sebelum Anda mengisinya.
- `hapus.php` mengunci semua operasi di dalam direktori home Anda; path `../`, symlink yang
  menunjuk keluar, dan direktori home itu sendiri ditolak.
- Keduanya mengirim header `noindex` supaya tidak terindeks Google.

Meski begitu, **hapus kedua file dari server begitu pekerjaan selesai**. Masing-masing punya
tombol hapus-diri di bagian bawah halaman. Jangan pakai kata sandi yang sama dengan cPanel Anda.
