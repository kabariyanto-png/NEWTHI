# THI Glass — Theme WordPress

Theme klasik (tanpa page builder, tanpa proses build) bergaya lembaga: tipografi
serif, palet hijau–coklat, panel kaca tipis, parallax halus, mode gelap, dan panel
Customizer lengkap.

## Ringkas

| Aspek | Keterangan |
|---|---|
| Tipe | Classic theme (PHP), bukan block theme |
| Dependensi | **Nol.** Tanpa jQuery, tanpa GSAP, tanpa npm |
| Berat aset | ±46 KB CSS + ±21 KB JS (±11 KB CSS setelah gzip) |
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

**Glassmorphism** — dipakai terukur, bukan sebagai hiasan: bilah header, laci
navigasi, dan panel kartu. `backdrop-filter: blur() saturate()` dengan opasitas 78%
sehingga terbaca seperti kertas kalkir, bukan neon. Ada blok
`@supports not (backdrop-filter)` yang menaikkan opasitas panel agar teks tetap
terbaca di browser tanpa dukungan blur.

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

## Arah desain

Disusun agar terbaca sebagai situs lembaga, bukan halaman promosi:

- **Judul serif** (Lora) dengan isi sans (Inter) — kombinasi lazim untuk lembaga.
- **Rata kiri** di seluruh judul bagian dan hero; tidak ada blok rata tengah.
- **Sudut siku** (4–12px). Bentuk pil hanya untuk titik indikator.
- **Tanpa teks bergradien, tanpa orb blur, tanpa header pil melayang** — tiga ciri
  yang membuat sebuah halaman terlihat seperti templat generator.
- Gerakan ditahan: parallax hanya pada gambar hero dan garis latar, pergeseran
  dibatasi, hover mengangkat 3px.

## Warna

| Peran | Hex | Rasio kontras |
|---|---|---|
| Primer (hijau) | `#1E5B3F` | 8,00:1 dengan teks putih |
| Sekunder | `#4E8C6A` | — |
| Aksen (coklat) | `#7A4E2D` | 7,11:1 dengan teks putih |
| Latar | `#FAF8F3` | — |
| Teks | `#1C2B22` | 13,95:1 dengan latar |

Seluruh pasangan warna theme diuji terhadap WCAG AA; nilai terendah 5,22:1.
Ganti hijau dan coklat di **Customizer → THI Glass — Tampilan → Warna Merek**
bila THI punya kode warna resmi yang berbeda.
