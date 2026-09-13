<?php
/**
 * Panel Customizer THI Glass.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitasi checkbox.
 *
 * @param mixed $value Nilai.
 * @return bool
 */
function thig_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitasi angka dalam rentang.
 *
 * @param mixed                $value   Nilai.
 * @param WP_Customize_Setting $setting Setting.
 * @return int
 */
function thig_sanitize_range( $value, $setting ) {
	$input_attrs = $setting->manager->get_control( $setting->id )->input_attrs;
	$min         = isset( $input_attrs['min'] ) ? (int) $input_attrs['min'] : 0;
	$max         = isset( $input_attrs['max'] ) ? (int) $input_attrs['max'] : 100;
	return max( $min, min( $max, (int) $value ) );
}

/**
 * Sanitasi ID kategori (0 = semua).
 *
 * @param mixed $value Nilai.
 * @return int
 */
function thig_sanitize_category( $value ) {
	$value = (int) $value;
	return ( 0 === $value || term_exists( $value, 'category' ) ) ? $value : 0;
}

/**
 * Registrasi seluruh opsi.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function thig_customize_register( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	/* Helper ringkas untuk mendaftar setting + control. */
	$add = function ( $id, $args ) use ( $wp_customize ) {
		$wp_customize->add_setting(
			'thig_' . $id,
			array(
				'default'           => array_key_exists( 'default', $args )
					? $args['default']
					: ( array_key_exists( $id, thig_defaults() ) ? thig_defaults()[ $id ] : '' ),
				'sanitize_callback' => isset( $args['sanitize'] ) ? $args['sanitize'] : 'sanitize_text_field',
				'transport'         => isset( $args['transport'] ) ? $args['transport'] : 'refresh',
			)
		);

		$control = array(
			'label'       => $args['label'],
			'section'     => $args['section'],
			'type'        => isset( $args['type'] ) ? $args['type'] : 'text',
			'description' => isset( $args['description'] ) ? $args['description'] : '',
		);
		if ( isset( $args['choices'] ) ) {
			$control['choices'] = $args['choices'];
		}
		if ( isset( $args['input_attrs'] ) ) {
			$control['input_attrs'] = $args['input_attrs'];
		}
		if ( isset( $args['active_callback'] ) ) {
			$control['active_callback'] = $args['active_callback'];
		}

		if ( isset( $args['control'] ) && 'color' === $args['control'] ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'thig_' . $id, $control ) );
		} elseif ( isset( $args['control'] ) && 'image' === $args['control'] ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'thig_' . $id, $control ) );
		} else {
			$wp_customize->add_control( 'thig_' . $id, $control );
		}
	};

	/* =====================================================================
	 * PANEL: Tampilan THI Glass
	 * ================================================================== */
	$wp_customize->add_panel(
		'thig_panel_design',
		array(
			'title'       => __( 'THI Glass — Tampilan', 'thi-glass' ),
			'description' => __( 'Warna merek, efek kaca (glassmorphism), dan tipografi.', 'thi-glass' ),
			'priority'    => 20,
		)
	);

	$wp_customize->add_section(
		'thig_sec_brand',
		array(
			'title' => __( 'Warna Merek', 'thi-glass' ),
			'panel' => 'thig_panel_design',
		)
	);

	$add(
		'color_primary',
		array(
			'label'       => __( 'Warna Primer', 'thi-glass' ),
			'section'     => 'thig_sec_brand',
			'control'     => 'color',
			'default'     => '#7C3AED',
			'sanitize'    => 'sanitize_hex_color',
			'description' => __( 'Dipakai untuk tombol utama, tautan, dan aksen navigasi.', 'thi-glass' ),
		)
	);
	$add(
		'color_secondary',
		array(
			'label'    => __( 'Warna Sekunder', 'thi-glass' ),
			'section'  => 'thig_sec_brand',
			'control'  => 'color',
			'default'  => '#A78BFA',
			'sanitize' => 'sanitize_hex_color',
		)
	);
	$add(
		'color_accent',
		array(
			'label'       => __( 'Warna Aksen / CTA', 'thi-glass' ),
			'section'     => 'thig_sec_brand',
			'control'     => 'color',
			'default'     => '#A16207',
			'sanitize'    => 'sanitize_hex_color',
			'description' => __( 'Warna teks di atasnya dipilih otomatis (putih/gelap) agar kontras memenuhi WCAG.', 'thi-glass' ),
		)
	);

	$wp_customize->add_section(
		'thig_sec_glass',
		array(
			'title'       => __( 'Efek Kaca & Bentuk', 'thi-glass' ),
			'panel'       => 'thig_panel_design',
			'description' => __( 'Atur intensitas glassmorphism. Nilai blur terlalu tinggi dapat memberatkan perangkat lama.', 'thi-glass' ),
		)
	);

	$add(
		'glass_blur',
		array(
			'label'       => __( 'Intensitas Blur (px)', 'thi-glass' ),
			'section'     => 'thig_sec_glass',
			'type'        => 'range',
			'default'     => 18,
			'sanitize'    => 'thig_sanitize_range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 32,
				'step' => 1,
			),
		)
	);
	$add(
		'glass_opacity',
		array(
			'label'       => __( 'Opasitas Panel Kaca (%)', 'thi-glass' ),
			'section'     => 'thig_sec_glass',
			'type'        => 'range',
			'default'     => 55,
			'sanitize'    => 'thig_sanitize_range',
			'input_attrs' => array(
				'min'  => 30,
				'max'  => 90,
				'step' => 5,
			),
			'description' => __( 'Semakin rendah semakin transparan. Jangan turunkan di bawah 40% bila latar sangat ramai — teks bisa sulit dibaca.', 'thi-glass' ),
		)
	);
	$add(
		'radius',
		array(
			'label'       => __( 'Kelengkungan Sudut (px)', 'thi-glass' ),
			'section'     => 'thig_sec_glass',
			'type'        => 'range',
			'default'     => 24,
			'sanitize'    => 'thig_sanitize_range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 40,
				'step' => 2,
			),
		)
	);
	$add(
		'load_google_fonts',
		array(
			'label'       => __( 'Muat Google Fonts (Plus Jakarta Sans + Inter)', 'thi-glass' ),
			'section'     => 'thig_sec_glass',
			'type'        => 'checkbox',
			'default'     => true,
			'sanitize'    => 'thig_sanitize_checkbox',
			'description' => __( 'Matikan bila ingin memakai font sistem — halaman jadi lebih cepat dan tidak memanggil server luar.', 'thi-glass' ),
		)
	);

	/* =====================================================================
	 * PANEL: Beranda
	 * ================================================================== */
	$wp_customize->add_panel(
		'thig_panel_home',
		array(
			'title'       => __( 'THI Glass — Beranda', 'thi-glass' ),
			'description' => __( 'Isi setiap bagian halaman depan. Bagian yang dimatikan tidak dirender sama sekali.', 'thi-glass' ),
			'priority'    => 21,
		)
	);

	/* --- Hero --- */
	$wp_customize->add_section(
		'thig_sec_hero',
		array(
			'title' => __( '1. Hero (Layar Pembuka)', 'thi-glass' ),
			'panel' => 'thig_panel_home',
		)
	);

	$add(
		'hero_badge',
		array(
			'label'   => __( 'Teks Lencana Kecil', 'thi-glass' ),
			'section' => 'thig_sec_hero',
			'default' => __( 'Terbuka untuk kolaborasi 2026', 'thi-glass' ),
		)
	);
	$add(
		'hero_title',
		array(
			'label'       => __( 'Judul Utama', 'thi-glass' ),
			'section'     => 'thig_sec_hero',
			'type'        => 'textarea',
			'default'     => __( 'Membangun dampak yang bertahan lama', 'thi-glass' ),
			'transport'   => 'postMessage',
		)
	);
	$add(
		'hero_title_accent',
		array(
			'label'       => __( 'Kata yang Diberi Gradien', 'thi-glass' ),
			'section'     => 'thig_sec_hero',
			'default'     => __( 'dampak', 'thi-glass' ),
			'description' => __( 'Tulis satu atau dua kata dari judul di atas. Kata itu akan diwarnai gradien merek.', 'thi-glass' ),
		)
	);
	$add(
		'hero_lead',
		array(
			'label'     => __( 'Paragraf Pembuka', 'thi-glass' ),
			'section'   => 'thig_sec_hero',
			'type'      => 'textarea',
			'default'   => __( 'Kami bekerja bersama komunitas, mitra, dan relawan untuk menghadirkan perubahan yang terukur dan berkelanjutan di seluruh Indonesia.', 'thi-glass' ),
			'sanitize'  => 'wp_kses_post',
			'transport' => 'postMessage',
		)
	);
	$add(
		'hero_cta1_text',
		array(
			'label'   => __( 'Tombol 1 — Teks', 'thi-glass' ),
			'section' => 'thig_sec_hero',
			'default' => __( 'Jelajahi Program', 'thi-glass' ),
		)
	);
	$add(
		'hero_cta1_url',
		array(
			'label'    => __( 'Tombol 1 — Tautan', 'thi-glass' ),
			'section'  => 'thig_sec_hero',
			'type'     => 'url',
			'default'  => '#program',
			'sanitize' => 'esc_url_raw',
		)
	);
	$add(
		'hero_cta2_text',
		array(
			'label'   => __( 'Tombol 2 — Teks', 'thi-glass' ),
			'section' => 'thig_sec_hero',
			'default' => __( 'Hubungi Kami', 'thi-glass' ),
		)
	);
	$add(
		'hero_cta2_url',
		array(
			'label'    => __( 'Tombol 2 — Tautan', 'thi-glass' ),
			'section'  => 'thig_sec_hero',
			'type'     => 'url',
			'default'  => '#kontak',
			'sanitize' => 'esc_url_raw',
		)
	);
	$add(
		'hero_image',
		array(
			'label'       => __( 'Gambar Latar Hero', 'thi-glass' ),
			'section'     => 'thig_sec_hero',
			'control'     => 'image',
			'sanitize'    => 'esc_url_raw',
			'description' => __( 'Opsional. Disarankan 1920×1080, format WebP/JPG di bawah 300 KB. Gambar ini bergerak parallax dan diberi lapisan gradien.', 'thi-glass' ),
		)
	);

	for ( $i = 1; $i <= 4; $i++ ) {
		$add(
			"hero_stat{$i}_num",
			array(
				/* translators: %d: nomor statistik. */
				'label'   => sprintf( __( 'Statistik %d — Angka', 'thi-glass' ), $i ),
				'section' => 'thig_sec_hero',
				'default' => '',
				'description' => 1 === $i ? __( 'Isi angka saja, contoh: 120. Angka akan dianimasikan menghitung naik.', 'thi-glass' ) : '',
			)
		);
		$add(
			"hero_stat{$i}_suffix",
			array(
				/* translators: %d: nomor statistik. */
				'label'   => sprintf( __( 'Statistik %d — Akhiran (mis. +, %%)', 'thi-glass' ), $i ),
				'section' => 'thig_sec_hero',
				'default' => '',
			)
		);
		$add(
			"hero_stat{$i}_label",
			array(
				/* translators: %d: nomor statistik. */
				'label'   => sprintf( __( 'Statistik %d — Keterangan', 'thi-glass' ), $i ),
				'section' => 'thig_sec_hero',
				'default' => '',
			)
		);
	}

	/* --- Tentang --- */
	$wp_customize->add_section(
		'thig_sec_about',
		array(
			'title' => __( '2. Tentang Kami', 'thi-glass' ),
			'panel' => 'thig_panel_home',
		)
	);
	$add(
		'about_enable',
		array(
			'label'    => __( 'Tampilkan bagian ini', 'thi-glass' ),
			'section'  => 'thig_sec_about',
			'type'     => 'checkbox',
			'default'  => true,
			'sanitize' => 'thig_sanitize_checkbox',
		)
	);
	$add(
		'about_eyebrow',
		array(
			'label'   => __( 'Label Kecil', 'thi-glass' ),
			'section' => 'thig_sec_about',
			'default' => __( 'Tentang Kami', 'thi-glass' ),
		)
	);
	$add(
		'about_title',
		array(
			'label'   => __( 'Judul', 'thi-glass' ),
			'section' => 'thig_sec_about',
			'type'    => 'textarea',
			'default' => __( 'Organisasi yang tumbuh bersama komunitasnya', 'thi-glass' ),
		)
	);
	$add(
		'about_text',
		array(
			'label'    => __( 'Uraian', 'thi-glass' ),
			'section'  => 'thig_sec_about',
			'type'     => 'textarea',
			'sanitize' => 'wp_kses_post',
			'default'  => __( 'Sejak berdiri, kami konsisten mendampingi program pendidikan, pemberdayaan ekonomi, dan penguatan kapasitas organisasi masyarakat sipil di berbagai daerah.', 'thi-glass' ),
		)
	);
	for ( $i = 1; $i <= 4; $i++ ) {
		$add(
			"about_point{$i}",
			array(
				/* translators: %d: nomor poin. */
				'label'   => sprintf( __( 'Poin %d', 'thi-glass' ), $i ),
				'section' => 'thig_sec_about',
				'default' => '',
			)
		);
	}
	$add(
		'about_image',
		array(
			'label'    => __( 'Gambar', 'thi-glass' ),
			'section'  => 'thig_sec_about',
			'control'  => 'image',
			'sanitize' => 'esc_url_raw',
		)
	);
	$add(
		'about_cta_text',
		array(
			'label'   => __( 'Tombol — Teks', 'thi-glass' ),
			'section' => 'thig_sec_about',
			'default' => __( 'Selengkapnya', 'thi-glass' ),
		)
	);
	$add(
		'about_cta_url',
		array(
			'label'    => __( 'Tombol — Tautan', 'thi-glass' ),
			'section'  => 'thig_sec_about',
			'type'     => 'url',
			'sanitize' => 'esc_url_raw',
		)
	);

	/* --- Program --- */
	$wp_customize->add_section(
		'thig_sec_program',
		array(
			'title'       => __( '3. Program / Layanan', 'thi-glass' ),
			'panel'       => 'thig_panel_home',
			'description' => __( 'Kartu program diambil otomatis dari kategori artikel yang dipilih, sehingga bisa dikelola dari menu Pos seperti biasa.', 'thi-glass' ),
		)
	);
	$add(
		'program_enable',
		array(
			'label'    => __( 'Tampilkan bagian ini', 'thi-glass' ),
			'section'  => 'thig_sec_program',
			'type'     => 'checkbox',
			'default'  => true,
			'sanitize' => 'thig_sanitize_checkbox',
		)
	);
	$add(
		'program_eyebrow',
		array(
			'label'   => __( 'Label Kecil', 'thi-glass' ),
			'section' => 'thig_sec_program',
			'default' => __( 'Apa yang Kami Kerjakan', 'thi-glass' ),
		)
	);
	$add(
		'program_title',
		array(
			'label'   => __( 'Judul', 'thi-glass' ),
			'section' => 'thig_sec_program',
			'type'    => 'textarea',
			'default' => __( 'Program unggulan kami', 'thi-glass' ),
		)
	);
	$add(
		'program_desc',
		array(
			'label'    => __( 'Deskripsi', 'thi-glass' ),
			'section'  => 'thig_sec_program',
			'type'     => 'textarea',
			'sanitize' => 'wp_kses_post',
			'default'  => '',
		)
	);

	$cats = array( 0 => __( '— Semua kategori —', 'thi-glass' ) );
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
		$cats[ $cat->term_id ] = $cat->name;
	}
	$add(
		'program_category',
		array(
			'label'    => __( 'Ambil dari Kategori', 'thi-glass' ),
			'section'  => 'thig_sec_program',
			'type'     => 'select',
			'choices'  => $cats,
			'default'  => 0,
			'sanitize' => 'thig_sanitize_category',
		)
	);
	$add(
		'program_count',
		array(
			'label'       => __( 'Jumlah Kartu', 'thi-glass' ),
			'section'     => 'thig_sec_program',
			'type'        => 'number',
			'default'     => 6,
			'sanitize'    => 'absint',
			'input_attrs' => array(
				'min' => 2,
				'max' => 12,
			),
		)
	);

	/* --- Statistik --- */
	$wp_customize->add_section(
		'thig_sec_stats',
		array(
			'title' => __( '4. Angka Dampak', 'thi-glass' ),
			'panel' => 'thig_panel_home',
		)
	);
	$add(
		'stats_enable',
		array(
			'label'    => __( 'Tampilkan bagian ini', 'thi-glass' ),
			'section'  => 'thig_sec_stats',
			'type'     => 'checkbox',
			'default'  => false,
			'sanitize' => 'thig_sanitize_checkbox',
		)
	);
	$add(
		'stats_title',
		array(
			'label'   => __( 'Judul', 'thi-glass' ),
			'section' => 'thig_sec_stats',
			'default' => __( 'Dampak dalam angka', 'thi-glass' ),
		)
	);
	for ( $i = 1; $i <= 4; $i++ ) {
		$add(
			"stat{$i}_num",
			array(
				/* translators: %d: nomor statistik. */
				'label'   => sprintf( __( 'Angka %d', 'thi-glass' ), $i ),
				'section' => 'thig_sec_stats',
			)
		);
		$add(
			"stat{$i}_suffix",
			array(
				/* translators: %d: nomor statistik. */
				'label'   => sprintf( __( 'Akhiran %d', 'thi-glass' ), $i ),
				'section' => 'thig_sec_stats',
			)
		);
		$add(
			"stat{$i}_label",
			array(
				/* translators: %d: nomor statistik. */
				'label'   => sprintf( __( 'Keterangan %d', 'thi-glass' ), $i ),
				'section' => 'thig_sec_stats',
			)
		);
	}

	/* --- Testimoni --- */
	$wp_customize->add_section(
		'thig_sec_testi',
		array(
			'title' => __( '5. Testimoni', 'thi-glass' ),
			'panel' => 'thig_panel_home',
		)
	);
	$add(
		'testi_enable',
		array(
			'label'    => __( 'Tampilkan bagian ini', 'thi-glass' ),
			'section'  => 'thig_sec_testi',
			'type'     => 'checkbox',
			'default'  => false,
			'sanitize' => 'thig_sanitize_checkbox',
		)
	);
	$add(
		'testi_title',
		array(
			'label'   => __( 'Judul', 'thi-glass' ),
			'section' => 'thig_sec_testi',
			'default' => __( 'Kata mereka tentang kami', 'thi-glass' ),
		)
	);
	$add(
		'testi_autoplay',
		array(
			'label'       => __( 'Putar otomatis', 'thi-glass' ),
			'section'     => 'thig_sec_testi',
			'type'        => 'checkbox',
			'default'     => false,
			'sanitize'    => 'thig_sanitize_checkbox',
			'description' => __( 'Otomatis dimatikan untuk pengunjung yang mengaktifkan "kurangi gerakan" di perangkatnya.', 'thi-glass' ),
		)
	);
	for ( $i = 1; $i <= 3; $i++ ) {
		$add(
			"testi{$i}_quote",
			array(
				/* translators: %d: nomor testimoni. */
				'label'    => sprintf( __( 'Testimoni %d — Kutipan', 'thi-glass' ), $i ),
				'section'  => 'thig_sec_testi',
				'type'     => 'textarea',
				'sanitize' => 'wp_kses_post',
			)
		);
		$add(
			"testi{$i}_name",
			array(
				/* translators: %d: nomor testimoni. */
				'label'   => sprintf( __( 'Testimoni %d — Nama', 'thi-glass' ), $i ),
				'section' => 'thig_sec_testi',
			)
		);
		$add(
			"testi{$i}_role",
			array(
				/* translators: %d: nomor testimoni. */
				'label'   => sprintf( __( 'Testimoni %d — Jabatan', 'thi-glass' ), $i ),
				'section' => 'thig_sec_testi',
			)
		);
		$add(
			"testi{$i}_photo",
			array(
				/* translators: %d: nomor testimoni. */
				'label'    => sprintf( __( 'Testimoni %d — Foto', 'thi-glass' ), $i ),
				'section'  => 'thig_sec_testi',
				'control'  => 'image',
				'sanitize' => 'esc_url_raw',
			)
		);
	}

	/* --- Mitra --- */
	$wp_customize->add_section(
		'thig_sec_partner',
		array(
			'title'       => __( '6. Mitra & Pendukung', 'thi-glass' ),
			'panel'       => 'thig_panel_home',
			'description' => __( 'Logo berjalan otomatis dan berhenti saat disorot kursor atau difokus keyboard.', 'thi-glass' ),
		)
	);
	$add(
		'partner_enable',
		array(
			'label'    => __( 'Tampilkan bagian ini', 'thi-glass' ),
			'section'  => 'thig_sec_partner',
			'type'     => 'checkbox',
			'default'  => false,
			'sanitize' => 'thig_sanitize_checkbox',
		)
	);
	$add(
		'partner_title',
		array(
			'label'   => __( 'Judul', 'thi-glass' ),
			'section' => 'thig_sec_partner',
			'default' => __( 'Dipercaya oleh', 'thi-glass' ),
		)
	);
	for ( $i = 1; $i <= 8; $i++ ) {
		$add(
			"partner{$i}_logo",
			array(
				/* translators: %d: nomor logo. */
				'label'    => sprintf( __( 'Logo %d', 'thi-glass' ), $i ),
				'section'  => 'thig_sec_partner',
				'control'  => 'image',
				'sanitize' => 'esc_url_raw',
			)
		);
		$add(
			"partner{$i}_name",
			array(
				/* translators: %d: nomor logo. */
				'label'       => sprintf( __( 'Logo %d — Nama (teks alternatif)', 'thi-glass' ), $i ),
				'section'     => 'thig_sec_partner',
				'description' => 1 === $i ? __( 'Wajib diisi agar pembaca layar bisa menyebut nama mitra.', 'thi-glass' ) : '',
			)
		);
	}

	/* --- Berita --- */
	$wp_customize->add_section(
		'thig_sec_news',
		array(
			'title' => __( '7. Berita Terbaru', 'thi-glass' ),
			'panel' => 'thig_panel_home',
		)
	);
	$add(
		'news_enable',
		array(
			'label'    => __( 'Tampilkan bagian ini', 'thi-glass' ),
			'section'  => 'thig_sec_news',
			'type'     => 'checkbox',
			'default'  => true,
			'sanitize' => 'thig_sanitize_checkbox',
		)
	);
	$add(
		'news_eyebrow',
		array(
			'label'   => __( 'Label Kecil', 'thi-glass' ),
			'section' => 'thig_sec_news',
			'default' => __( 'Kabar Terbaru', 'thi-glass' ),
		)
	);
	$add(
		'news_title',
		array(
			'label'   => __( 'Judul', 'thi-glass' ),
			'section' => 'thig_sec_news',
			'default' => __( 'Berita & kegiatan', 'thi-glass' ),
		)
	);
	$add(
		'news_category',
		array(
			'label'    => __( 'Ambil dari Kategori', 'thi-glass' ),
			'section'  => 'thig_sec_news',
			'type'     => 'select',
			'choices'  => $cats,
			'default'  => 0,
			'sanitize' => 'thig_sanitize_category',
		)
	);
	$add(
		'news_count',
		array(
			'label'       => __( 'Jumlah Artikel', 'thi-glass' ),
			'section'     => 'thig_sec_news',
			'type'        => 'number',
			'default'     => 3,
			'sanitize'    => 'absint',
			'input_attrs' => array(
				'min' => 2,
				'max' => 9,
			),
		)
	);

	/* --- CTA --- */
	$wp_customize->add_section(
		'thig_sec_cta',
		array(
			'title' => __( '8. Ajakan Bertindak (CTA)', 'thi-glass' ),
			'panel' => 'thig_panel_home',
		)
	);
	$add(
		'cta_enable',
		array(
			'label'    => __( 'Tampilkan bagian ini', 'thi-glass' ),
			'section'  => 'thig_sec_cta',
			'type'     => 'checkbox',
			'default'  => true,
			'sanitize' => 'thig_sanitize_checkbox',
		)
	);
	$add(
		'cta_title',
		array(
			'label'   => __( 'Judul', 'thi-glass' ),
			'section' => 'thig_sec_cta',
			'type'    => 'textarea',
			'default' => __( 'Mari berkolaborasi', 'thi-glass' ),
		)
	);
	$add(
		'cta_text',
		array(
			'label'    => __( 'Uraian', 'thi-glass' ),
			'section'  => 'thig_sec_cta',
			'type'     => 'textarea',
			'sanitize' => 'wp_kses_post',
			'default'  => __( 'Punya gagasan program, ingin menjadi mitra, atau sekadar ingin berdiskusi? Tim kami siap mendengar.', 'thi-glass' ),
		)
	);
	$add(
		'cta_btn1_text',
		array(
			'label'   => __( 'Tombol 1 — Teks', 'thi-glass' ),
			'section' => 'thig_sec_cta',
			'default' => __( 'Hubungi Kami', 'thi-glass' ),
		)
	);
	$add(
		'cta_btn1_url',
		array(
			'label'    => __( 'Tombol 1 — Tautan', 'thi-glass' ),
			'section'  => 'thig_sec_cta',
			'type'     => 'url',
			'sanitize' => 'esc_url_raw',
		)
	);
	$add(
		'cta_btn2_text',
		array(
			'label'   => __( 'Tombol 2 — Teks', 'thi-glass' ),
			'section' => 'thig_sec_cta',
			'default' => '',
		)
	);
	$add(
		'cta_btn2_url',
		array(
			'label'    => __( 'Tombol 2 — Tautan', 'thi-glass' ),
			'section'  => 'thig_sec_cta',
			'type'     => 'url',
			'sanitize' => 'esc_url_raw',
		)
	);

	/* =====================================================================
	 * SECTION: Kontak & Sosial
	 * ================================================================== */
	$wp_customize->add_section(
		'thig_sec_contact',
		array(
			'title'       => __( 'THI Glass — Kontak & Sosial', 'thi-glass' ),
			'priority'    => 22,
			'description' => __( 'Dipakai di footer dan pada data terstruktur (JSON-LD) untuk SEO.', 'thi-glass' ),
		)
	);

	$add(
		'org_description',
		array(
			'label'    => __( 'Deskripsi Singkat Organisasi', 'thi-glass' ),
			'section'  => 'thig_sec_contact',
			'type'     => 'textarea',
			'sanitize' => 'wp_kses_post',
		)
	);
	$add(
		'contact_address',
		array(
			'label'   => __( 'Alamat', 'thi-glass' ),
			'section' => 'thig_sec_contact',
			'type'    => 'textarea',
		)
	);
	$add(
		'contact_city',
		array(
			'label'   => __( 'Kota', 'thi-glass' ),
			'section' => 'thig_sec_contact',
		)
	);
	$add(
		'contact_phone',
		array(
			'label'   => __( 'Telepon', 'thi-glass' ),
			'section' => 'thig_sec_contact',
		)
	);
	$add(
		'contact_email',
		array(
			'label'    => __( 'Email', 'thi-glass' ),
			'section'  => 'thig_sec_contact',
			'type'     => 'email',
			'sanitize' => 'sanitize_email',
		)
	);

	foreach ( array( 'facebook', 'instagram', 'twitter', 'youtube', 'linkedin', 'tiktok', 'whatsapp' ) as $network ) {
		$add(
			'social_' . $network,
			array(
				/* translators: %s: nama jejaring sosial. */
				'label'    => sprintf( __( 'URL %s', 'thi-glass' ), ucfirst( $network ) ),
				'section'  => 'thig_sec_contact',
				'type'     => 'url',
				'sanitize' => 'esc_url_raw',
			)
		);
	}

	$add(
		'footer_credit',
		array(
			'label'    => __( 'Teks Hak Cipta Footer', 'thi-glass' ),
			'section'  => 'thig_sec_contact',
			'sanitize' => 'wp_kses_post',
			'default'  => '',
			'description' => __( 'Kosongkan untuk memakai format bawaan: © tahun — Nama Situs.', 'thi-glass' ),
		)
	);

	/* Selective refresh untuk judul & tagline. */
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.brand-name',
				'render_callback' => function () {
					return get_bloginfo( 'name', 'display' );
				},
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.brand-tag',
				'render_callback' => function () {
					return get_bloginfo( 'description', 'display' );
				},
			)
		);
	}
}
add_action( 'customize_register', 'thig_customize_register' );
