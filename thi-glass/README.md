# THI Glass — Theme WordPress

Theme klasik (tanpa page builder, tanpa proses build) dengan desain glassmorphism,
parallax multi-lapis, mode gelap, dan panel Customizer lengkap.

## Ringkas

| Aspek | Keterangan |
|---|---|
| Tipe | Classic theme (PHP), bukan block theme |
| Dependensi | **Nol.** Tanpa jQuery, tanpa GSAP, tanpa npm |
| Berat aset | ±34 KB CSS + ±17 KB JS (sebelum kompresi server) |
| Minimum | WordPress 6.0, PHP 7.4 |
| Bahasa | Antarmuka Bahasa Indonesia, siap diterjemahkan (text domain `thi-glass`) |

## Struktur

```
thi-glass/
├── style.css              header theme (wajib WordPress)
├── functions.php          setup, aset, widget, include
├── header.php footer.php  kerangka halaman
├── front-page.php         beranda (memanggil template-parts/home/*)
├── index.php page.php single.php archive.php search.php 404.php
├── comments.php sidebar.php searchform.php
├── inc/
│   ├── customizer.php     seluruh opsi Customizer
│   ├── dynamic-css.php    variabel CSS dari opsi + pemilih kontras WCAG
│   ├── template-tags.php  ikon SVG, meta, breadcrumb, paginasi
│   ├── nav-walker.php     atribut ARIA pada menu
│   └── schema.php         JSON-LD + Open Graph
├── template-parts/
│   ├── content-card.php content-none.php
│   └── home/              hero, about, programs, stats, testimonials,
│                          partners, news, cta
└── assets/css/theme.css  assets/js/theme.js
```

## Efek yang dipakai

**Parallax** — dikerjakan sendiri dengan `requestAnimationFrame` + `IntersectionObserver`.
Hanya lapisan dekoratif (orb gradien, grid, gambar latar) yang bergerak; teks dan
kontrol tidak pernah diparalaks. Pergeseran dibatasi maksimal 24% agar lapisan tidak
pernah keluar wadahnya, dan `will-change` dilepas begitu elemen keluar viewport.

**Glassmorphism** — `backdrop-filter: blur() saturate()` dengan border tipis dan
highlight `inset`. Ada blok `@supports not (backdrop-filter)` yang menaikkan opasitas
panel, sehingga di browser tanpa dukungan blur teks tetap terbaca, bukan menempel di
latar ramai.

**Aksesibilitas** — seluruh gerakan dimatikan oleh `prefers-reduced-motion: reduce`
(parallax, reveal, marquee, autoplay slider, smooth scroll). Fokus terlihat 3px,
target sentuh minimal 44×44px, skip link, focus trap pada drawer, `aria-live` pada
slider, dan warna teks di atas tombol dipilih otomatis lewat perhitungan luminansi
WCAG di `inc/dynamic-css.php`.

## Kustomisasi

Semua lewat **Tampilan → Sesuaikan**:

- **THI Glass — Tampilan**: warna primer/sekunder/aksen, intensitas blur, opasitas
  kaca, kelengkungan sudut, aktif/nonaktif Google Fonts.
- **THI Glass — Beranda**: 8 bagian, masing-masing bisa dimatikan. Kartu Program dan
  Berita ditarik dari kategori pos yang Anda pilih, jadi kontennya dikelola dari menu
  Pos seperti biasa.
- **THI Glass — Kontak & Sosial**: alamat, telepon, email, 7 kanal sosial. Data ini
  juga dipakai untuk JSON-LD `Organization`.

Menu: **Tampilan → Menu**, tiga lokasi tersedia (Utama, Footer, Legal).

## Catatan

Warna bawaan (ungu + emas) adalah placeholder dari sistem desain, **bukan** warna
merek THI. Ganti di Customizer setelah theme aktif.
