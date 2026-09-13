# Panduan Pemasangan di Hostinger (cPanel / hPanel)

Perkiraan waktu: 10 menit untuk pemasangan, 20 menit untuk mengisi konten.

---

## ⚠️ Langkah 0 — Backup dulu (jangan dilewati)

Mengganti theme tidak menghapus konten, tapi **menu, widget, dan pengaturan tampilan
theme lama bisa hilang**. Backup dulu:

**Cara cepat lewat hPanel Hostinger:**
1. Masuk hPanel → **Websites** → pilih `thi.or.id` → **Dashboard**
2. Menu kiri: **Files → Backups** → **Generate new backup** (atau unduh backup terakhir)
3. Tunggu sampai selesai, lalu unduh file-nya.

Kalau memakai cPanel klasik: **Files → Backup → Download a Full Account Backup**.

---

## Langkah 1 — Cara termudah: unggah dari WP Admin

1. Buka `https://www.thi.or.id/wp-admin`
2. **Tampilan (Appearance) → Tema (Themes) → Tambah Baru → Unggah Tema**
3. Pilih file **`thi-glass.zip`**
4. Klik **Pasang Sekarang (Install Now)**
5. **JANGAN aktifkan dulu.** Baca Langkah 3 lebih dulu.

Kalau muncul error *"The uploaded file exceeds the upload_max_filesize"*, pakai
Langkah 2. (File ini hanya ±80 KB, jadi seharusnya tidak terjadi.)

---

## Langkah 2 — Alternatif: lewat File Manager cPanel

1. hPanel → **Files → File Manager**
2. Masuk ke folder: `public_html/wp-content/themes/`
   *(kalau WordPress terpasang di subfolder, sesuaikan jalurnya)*
3. Klik **Upload** → pilih `thi-glass.zip`
4. Kembali ke folder `themes/`, klik kanan file zip → **Extract**
5. Pastikan hasilnya folder `thi-glass/` yang **langsung berisi `style.css`**,
   bukan `thi-glass/thi-glass/style.css`. Kalau bersarang, pindahkan isinya naik satu level.
6. Hapus file `.zip`-nya agar tidak memenuhi disk.

---

## Langkah 3 — Uji coba tanpa mengubah situs live (disarankan)

Jangan langsung aktifkan di situs yang sedang berjalan. Dua pilihan aman:

**A. Live Preview** (paling cepat, nol risiko)
- **Tampilan → Tema** → arahkan kursor ke THI Glass → klik **Live Preview**
- Anda bisa mengatur warna dan isi di sini. Situs publik belum berubah sampai
  Anda menekan **Aktifkan & Terbitkan**.

**B. Staging** (paling aman kalau situs ramai)
- hPanel → **Websites → Dashboard → Staging** → buat staging
- Pasang dan uji di staging, baru **Push to Live** kalau sudah puas.

---

## Langkah 4 — Setelah aktif, isi ini berurutan

**Tampilan → Sesuaikan (Customize):**

1. **Identitas Situs** — unggah logo (PNG transparan, tinggi ±92px), isi judul & tagline.

2. **THI Glass — Tampilan → Warna Merek**
   Bawaannya sudah hijau `#1E5B3F` dan coklat `#7A4E2D`. Kalau THI punya kode
   warna resmi yang berbeda, ganti di sini — warna teks di atas tombol dipilih
   otomatis agar kontrasnya tetap memenuhi standar.

3. **THI Glass — Beranda → 1. Hero**
   - Judul Utama, lalu isi **"Kata yang Diberi Gradien"** dengan satu kata dari
     judul itu — kata tersebut akan berwarna gradien.
   - **Gambar Latar Hero**: 1920×1080, kompres dulu ke WebP/JPG **di bawah 300 KB**.
     Gambar besar akan membuat halaman berat.
   - Statistik 1–4: isi angka saja (`120`), akhiran (`+`), lalu keterangannya.
     Angka akan menghitung naik saat terlihat.

4. **Beranda → 3. Program** dan **7. Berita**
   Pilih **kategori** sumbernya. Kalau kategori belum ada:
   **Pos → Kategori**, buat misalnya "Program" dan "Berita", lalu tempatkan pos
   ke kategori itu. Kartu akan muncul otomatis.

5. **Beranda → 4, 5, 6** (Angka Dampak, Testimoni, Mitra) bawaannya **mati**.
   Nyalakan hanya kalau Anda sudah punya isinya — bagian kosong tidak dirender.

6. **THI Glass — Kontak & Sosial** — alamat, telepon, email, tautan media sosial.
   Ini juga dipakai Google lewat data terstruktur.

**Tampilan → Menu:** buat menu, centang lokasi **Menu Utama**. Buat satu lagi untuk
**Menu Footer** dan **Menu Legal** kalau perlu.

**Pengaturan → Membaca:** kalau ingin beranda memakai halaman statis, pilih
"Halaman statis". Theme tetap menampilkan semua bagian beranda; isi halaman itu
akan muncul di bawahnya.

---

## Langkah 5 — Kalau ada yang tidak beres

| Gejala | Penyebab & solusi |
|---|---|
| Tampilan polos tanpa gaya | Bersihkan cache: plugin cache (LiteSpeed/WP Rocket) → Purge All. Lalu Ctrl+Shift+R di browser. |
| Layar putih total | Aktifkan debug: di `wp-config.php` ubah `WP_DEBUG` jadi `true`, buka lagi, catat pesannya. Ganti balik ke theme lama lewat File Manager: ganti nama folder `thi-glass` jadi `thi-glass-off`, WordPress otomatis kembali ke theme bawaan. |
| Panel kaca terlihat buram / teks sulit dibaca | Customizer → Efek Kaca → naikkan **Opasitas Panel Kaca** (bawaan 78%). |
| Halaman terasa berat di HP | Turunkan **Intensitas Blur** (bawaan 12px) ke 0–8px, dan pastikan gambar hero di bawah 300 KB. |
| Menu tidak muncul | Tampilan → Menu → centang lokasi **Menu Utama** → Simpan. |
| Kartu Program kosong | Belum ada pos di kategori yang dipilih. Buat pos, beri kategori itu. |

---

## Yang perlu Anda putuskan

Hijau dan coklat sudah jadi bawaan. Nilai persisnya (`#1E5B3F` / `#7A4E2D`) saya
pilih agar kontrasnya aman — saya belum bisa membuka `thi.or.id` dari lingkungan
kerja saya, jadi belum tahu kode warna resmi THI. Kalau ada, kirimkan dan saya
pasang sebagai bawaan; atau ganti sendiri lewat Customizer.
