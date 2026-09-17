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
lalu PHP menjawab bahwa **driver `pdo_mysql` tidak ada** pada versi PHP yang dipakai domain itu.

Konsekuensinya penting: **database dan file website Anda kemungkinan besar baik-baik saja.**
Yang kurang cuma satu centang ekstensi PHP. Jangan menginstal ulang aplikasi lewat Softaculous —
itu justru berisiko menimpa data yang masih utuh.

Alasan hanya satu domain yang kena: di cPanel, **versi PHP diatur per domain**. Domain lama
Anda (`elibrary.thi.or.id`, `sdit.thi.or.id`) memakai versi PHP yang ekstensinya sudah lengkap,
sedangkan `elibrary-smait.thi.or.id` yang baru dibuat jatuh ke versi default yang belum
diaktifkan ekstensinya.

### Perbaikan A — samakan versi PHP dengan domain yang sudah jalan (paling cepat)

1. cPanel → cari **MultiPHP Manager**.
2. Lihat baris `elibrary.thi.or.id` (domain yang normal). **Catat versi PHP-nya**, misalnya `PHP 8.1`.
3. Centang kotak di baris `elibrary-smait.thi.or.id`.
4. Di kotak **PHP Version** kanan atas, pilih versi yang sama persis dengan langkah 2.
5. Klik **Apply**, tunggu kira-kira 30 detik.
6. Buka `https://elibrary-smait.thi.or.id` — muat ulang paksa dengan `Ctrl + F5`.

Kalau sudah terbuka, selesai. Kalau belum, lanjut ke Perbaikan B.

### Perbaikan B — aktifkan ekstensi `pdo_mysql`

1. cPanel → **Select PHP Version** (pada sebagian hosting namanya **PHP Selector**).
   Kalau menu ini tidak ada, pakai **MultiPHP INI Editor** dan langsung ke Perbaikan C.
2. Di kotak domain bagian atas, **pilih `elibrary-smait.thi.or.id`**. Ini sering terlewat —
   kalau salah pilih domain, centangnya akan mendarat di website lain.
3. Buka tab **Extensions**.
4. Centang semuanya: `pdo`, `pdo_mysql`, `mysqli`, `mysqlnd`.
   - Bila daftarnya menyediakan `nd_pdo_mysql` **dan** `pdo_mysql`, cukup centang **salah satu**.
     Mencentang keduanya membuat PHP gagal start pada sebagian server.
5. Perubahan tersimpan otomatis. Tunggu 30 detik, lalu buka website dengan `Ctrl + F5`.

### Perbaikan C — kalau A dan B belum menyelesaikan

Jalankan alat diagnosa yang sudah disiapkan di folder ini:

1. Buka `diagnosa.php` (di komputer Anda) dengan Notepad, ganti baris

   ```php
   $PASSWORD = 'GANTI_KATA_SANDI_INI';
   ```

   menjadi kata sandi bebas milik Anda, contoh `$PASSWORD = 'kunci-saya-2026';`. Simpan.
2. cPanel → **File Manager** → masuk ke `public_html/elibrary-smait.thi.or.id`
   → tombol **Upload** → pilih `diagnosa.php`.
3. Buka `https://elibrary-smait.thi.or.id/diagnosa.php`, masukkan kata sandi tadi.
4. Baca bagian **Kesimpulan** di halaman paling atas. Alat itu menyebutkan penyebab pastinya
   dan versi PHP mana di server ini yang punya `pdo_mysql`, jadi Anda tinggal memilih versi itu
   di MultiPHP Manager.
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
