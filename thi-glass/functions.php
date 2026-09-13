<?php
/**
 * THI Glass — functions & definitions.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

define( 'THIG_VERSION', '1.0.0' );
define( 'THIG_DIR', get_template_directory() );
define( 'THIG_URI', get_template_directory_uri() );

/* -------------------------------------------------------------------------
 * 1. Theme setup
 * ---------------------------------------------------------------------- */
function thig_setup() {
	load_theme_textdomain( 'thi-glass', THIG_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/theme.css' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 92,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Menu Utama', 'thi-glass' ),
			'footer'  => __( 'Menu Footer (kolom tautan)', 'thi-glass' ),
			'legal'   => __( 'Menu Legal (bawah footer)', 'thi-glass' ),
		)
	);

	add_image_size( 'thig-card', 800, 500, true );
	add_image_size( 'thig-wide', 1600, 900, true );

	// Palet warna editor blok mengikuti token theme.
	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'Primer', 'thi-glass' ),
				'slug'  => 'thig-primary',
				'color' => thig_opt( 'color_primary', '#1E5B3F' ),
			),
			array(
				'name'  => __( 'Sekunder', 'thi-glass' ),
				'slug'  => 'thig-secondary',
				'color' => thig_opt( 'color_secondary', '#4E8C6A' ),
			),
			array(
				'name'  => __( 'Aksen', 'thi-glass' ),
				'slug'  => 'thig-accent',
				'color' => thig_opt( 'color_accent', '#7A4E2D' ),
			),
			array(
				'name'  => __( 'Teks', 'thi-glass' ),
				'slug'  => 'thig-fg',
				'color' => '#1C2B22',
			),
			array(
				'name'  => __( 'Latar', 'thi-glass' ),
				'slug'  => 'thig-bg',
				'color' => '#FAF8F3',
			),
			array(
				'name'  => __( 'Putih', 'thi-glass' ),
				'slug'  => 'thig-white',
				'color' => '#FFFFFF',
			),
		)
	);

	add_theme_support(
		'editor-font-sizes',
		array(
			array(
				'name' => __( 'Kecil', 'thi-glass' ),
				'slug' => 'small',
				'size' => 15,
			),
			array(
				'name' => __( 'Normal', 'thi-glass' ),
				'slug' => 'normal',
				'size' => 17,
			),
			array(
				'name' => __( 'Besar', 'thi-glass' ),
				'slug' => 'large',
				'size' => 21,
			),
			array(
				'name' => __( 'Sangat Besar', 'thi-glass' ),
				'slug' => 'huge',
				'size' => 32,
			),
		)
	);

	// Lebar konten untuk oEmbed.
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'thig_setup' );

/* -------------------------------------------------------------------------
 * 2. Helper: ambil opsi Customizer
 * ---------------------------------------------------------------------- */
/**
 * Nilai bawaan seluruh opsi theme.
 *
 * Sumber kebenaran tunggal: dipakai oleh thig_opt() DAN oleh Customizer.
 * Tanpa ini, get_theme_mod() tidak mengetahui default yang didaftarkan
 * Customizer, sehingga situs yang baru dipasang tampil kosong.
 *
 * @return array<string,mixed>
 */
function thig_defaults() {
	static $defaults = null;
	if ( null !== $defaults ) {
		return $defaults;
	}

	$defaults = array(
		// Tampilan.
		'color_primary'     => '#1E5B3F',
		'color_secondary'   => '#4E8C6A',
		'color_accent'      => '#7A4E2D',
		'glass_blur'        => 12,
		'glass_opacity'     => 78,
		'radius'            => 8,
		'load_google_fonts' => true,

		// Hero.
		'hero_badge'        => __( 'Organisasi Nirlaba', 'thi-glass' ),
		'hero_title'        => __( 'Bekerja bersama masyarakat untuk perubahan yang berkelanjutan', 'thi-glass' ),
		'hero_title_accent' => __( 'berkelanjutan', 'thi-glass' ),
		'hero_lead'         => __( 'Kami menjalankan program pendidikan, pemberdayaan ekonomi, dan penguatan kapasitas organisasi masyarakat di berbagai daerah di Indonesia.', 'thi-glass' ),
		'hero_cta1_text'    => __( 'Program Kami', 'thi-glass' ),
		'hero_cta1_url'     => '#program',
		'hero_cta2_text'    => __( 'Hubungi Kami', 'thi-glass' ),
		'hero_cta2_url'     => '#kontak',

		// Tentang.
		'about_enable'      => true,
		'about_eyebrow'     => __( 'Profil', 'thi-glass' ),
		'about_title'       => __( 'Tentang organisasi kami', 'thi-glass' ),
		'about_text'        => __( 'Sejak berdiri, kami mendampingi program pendidikan, pemberdayaan ekonomi, dan penguatan kapasitas organisasi masyarakat sipil di berbagai daerah.', 'thi-glass' ),
		'about_cta_text'    => __( 'Selengkapnya', 'thi-glass' ),

		// Program.
		'program_enable'    => true,
		'program_eyebrow'   => __( 'Program', 'thi-glass' ),
		'program_title'     => __( 'Bidang kerja kami', 'thi-glass' ),
		'program_category'  => 0,
		'program_count'     => 6,

		// Angka dampak.
		'stats_enable'      => false,
		'stats_title'       => __( 'Capaian kami', 'thi-glass' ),

		// Testimoni.
		'testi_enable'      => false,
		'testi_title'       => __( 'Testimoni', 'thi-glass' ),
		'testi_autoplay'    => false,

		// Mitra.
		'partner_enable'    => false,
		'partner_title'     => __( 'Mitra kami', 'thi-glass' ),

		// Berita.
		'news_enable'       => true,
		'news_eyebrow'      => __( 'Publikasi', 'thi-glass' ),
		'news_title'        => __( 'Berita dan kegiatan', 'thi-glass' ),
		'news_category'     => 0,
		'news_count'        => 3,

		// CTA.
		'cta_enable'        => true,
		'cta_title'         => __( 'Hubungi kami', 'thi-glass' ),
		'cta_text'          => __( 'Untuk kerja sama program, informasi kegiatan, atau pertanyaan lain, silakan hubungi kami melalui kontak di bawah ini.', 'thi-glass' ),
		'cta_btn1_text'     => __( 'Hubungi Kami', 'thi-glass' ),
		'cta_btn1_url'      => '#kontak',

		// Kontak.
		'contact_country'   => 'ID',
	);

	return $defaults;
}

/**
 * Ambil opsi Customizer.
 *
 * @param string $key     Kunci tanpa prefiks.
 * @param mixed  $default Nilai bawaan. Bila null, diambil dari thig_defaults().
 * @return mixed
 */
function thig_opt( $key, $default = null ) {
	if ( null === $default ) {
		$defaults = thig_defaults();
		$default  = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : '';
	}
	return get_theme_mod( 'thig_' . $key, $default );
}

/* -------------------------------------------------------------------------
 * 3. Assets
 * ---------------------------------------------------------------------- */
function thig_assets() {
	// Google Fonts — dimuat hanya bila diizinkan di Customizer (privasi/kecepatan).
	if ( thig_opt( 'load_google_fonts' ) ) {
		wp_enqueue_style(
			'thig-fonts',
			'https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Inter:wght@400;500;600&display=swap',
			array(),
			null
		);
	}

	wp_enqueue_style( 'thig-theme', THIG_URI . '/assets/css/theme.css', array(), THIG_VERSION );
	wp_enqueue_style( 'thig-style', get_stylesheet_uri(), array( 'thig-theme' ), THIG_VERSION );
	wp_add_inline_style( 'thig-theme', thig_dynamic_css() );

	wp_enqueue_script( 'thig-theme', THIG_URI . '/assets/js/theme.js', array(), THIG_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'thig_assets' );

/** Preconnect ke Google Fonts agar font tidak menghambat render. */
function thig_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation && thig_opt( 'load_google_fonts' ) && wp_style_is( 'thig-fonts', 'enqueued' ) ) {
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'thig_resource_hints', 10, 2 );

/** Tambahkan defer pada script theme. */
function thig_defer_script( $tag, $handle ) {
	if ( 'thig-theme' === $handle && false === strpos( $tag, 'defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'thig_defer_script', 10, 2 );

/**
 * Tanam kelas .js-on sebelum paint pertama.
 *
 * Animasi reveal menyembunyikan elemen lewat CSS. Penanda ini memastikan
 * penyembunyian itu HANYA terjadi saat JavaScript benar-benar berjalan —
 * tanpa JS, halaman tampil utuh, bukan kosong.
 */
function thig_js_class() {
	echo "<script>document.documentElement.classList.add('js-on');</script>\n";
}
add_action( 'wp_head', 'thig_js_class', 1 );

/** Terapkan tema gelap sebelum paint agar tidak berkedip (FOUC). */
function thig_theme_boot_script() {
	?>
<script>(function(){try{var m=localStorage.getItem('thiglass-theme');var d=m?m==='dark':window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.setAttribute('data-theme',d?'dark':'light');}catch(e){}})();</script>
	<?php
}
add_action( 'wp_head', 'thig_theme_boot_script', 2 );

/* -------------------------------------------------------------------------
 * 4. Body classes
 * ---------------------------------------------------------------------- */
function thig_body_classes( $classes ) {
	if ( is_front_page() && ! is_paged() ) {
		$classes[] = 'has-hero';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) || is_page() || is_front_page() ) {
		$classes[] = 'no-sidebar';
	}
	if ( is_singular() ) {
		$classes[] = 'is-singular';
	}
	return $classes;
}
add_filter( 'body_class', 'thig_body_classes' );

/* -------------------------------------------------------------------------
 * 5. Widget areas
 * ---------------------------------------------------------------------- */
function thig_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar Blog', 'thi-glass' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Tampil di samping daftar & detail artikel.', 'thi-glass' ),
			'before_widget' => '<section id="%1$s" class="widget glass %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	for ( $i = 1; $i <= 2; $i++ ) {
		register_sidebar(
			array(
				/* translators: %d: nomor kolom footer. */
				'name'          => sprintf( __( 'Footer Kolom %d', 'thi-glass' ), $i ),
				'id'            => 'footer-' . $i,
				'description'   => __( 'Kolom widget di area footer.', 'thi-glass' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'thig_widgets_init' );

/* -------------------------------------------------------------------------
 * 6. Excerpt
 * ---------------------------------------------------------------------- */
add_filter( 'excerpt_length', function () { return 24; }, 999 );
add_filter( 'excerpt_more', function () { return '&hellip;'; } );

/* -------------------------------------------------------------------------
 * 7. Includes
 * ---------------------------------------------------------------------- */
require_once THIG_DIR . '/inc/dynamic-css.php';
require_once THIG_DIR . '/inc/template-tags.php';
require_once THIG_DIR . '/inc/nav-walker.php';
require_once THIG_DIR . '/inc/customizer.php';
require_once THIG_DIR . '/inc/schema.php';
