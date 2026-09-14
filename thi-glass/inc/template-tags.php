<?php
/**
 * Template tags & helper tampilan.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pustaka ikon SVG (stroke-based, Lucide-style). Tidak ada emoji sebagai ikon.
 *
 * @return array<string,string>
 */
function thig_icon_library() {
	return array(
		'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
		'arrow-up'    => '<path d="M12 19V5"/><path d="m5 12 7-7 7 7"/>',
		'check'       => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
		'chevron-down'=> '<path d="m6 9 6 6 6-6"/>',
		'chevron-left'=> '<path d="m15 18-6-6 6-6"/>',
		'chevron-right'=> '<path d="m9 18 6-6-6-6"/>',
		'close'       => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'search'      => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'sun'         => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'moon'        => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
		'play'        => '<polygon points="6 3 20 12 6 21 6 3"/>',
		'pause'       => '<rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/>',
		'calendar'    => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'user'        => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		'folder'      => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
		'tag'         => '<path d="M12.6 2.7a2 2 0 0 0-1.4-.6H4a2 2 0 0 0-2 2v7.2a2 2 0 0 0 .6 1.4l8.5 8.5a2 2 0 0 0 2.8 0l7.2-7.2a2 2 0 0 0 0-2.8Z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
		'clock'       => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'map-pin'     => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
		'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
		'mail'        => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'globe'       => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/>',
		'users'       => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
		'heart'       => '<path d="M19 14c1.5-1.5 3-3.3 3-5.5A5.5 5.5 0 0 0 12 5.4 5.5 5.5 0 0 0 2 8.5c0 2.2 1.5 4 3 5.5l7 7Z"/>',
		'target'      => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
		'shield'      => '<path d="M20 13c0 5-3.5 7.5-7.7 9a1 1 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.7a1 1 0 0 1 1.5 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1Z"/><path d="m9 12 2 2 4-4"/>',
		'book'        => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
		'sparkles'    => '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M19 15.5 19.8 18l2.5.8-2.5.8L19 22l-.8-2.4-2.5-.8 2.5-.8Z"/>',
		'facebook'    => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3Z"/>',
		'instagram'   => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.4A4 4 0 1 1 12.6 8 4 4 0 0 1 16 11.4Z"/><path d="M17.5 6.5h.01"/>',
		'twitter'     => '<path d="M4 4l7.7 10.3L4.5 20h1.7l6.3-6.8 5 6.8H20l-8-10.8L19.3 4h-1.7l-5.9 6.4L7 4Z"/>',
		'youtube'     => '<path d="M22 12s0-3.5-.4-5.1a2.6 2.6 0 0 0-1.8-1.8C18 4.7 12 4.7 12 4.7s-6 0-7.8.4a2.6 2.6 0 0 0-1.8 1.8C2 8.5 2 12 2 12s0 3.5.4 5.1a2.6 2.6 0 0 0 1.8 1.8c1.8.4 7.8.4 7.8.4s6 0 7.8-.4a2.6 2.6 0 0 0 1.8-1.8C22 15.5 22 12 22 12Z"/><path d="m10 15 5-3-5-3Z"/>',
		'linkedin'    => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-11h4v1.5A6 6 0 0 1 16 8Z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
		'whatsapp'    => '<path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.2-5.4A8.4 8.4 0 1 1 21 11.5Z"/><path d="M8.5 9.2c0 3 2.3 5.3 5.3 5.3l1-1.1-1.6-.9-.8.8a4.4 4.4 0 0 1-2.4-2.4l.8-.8-.9-1.6Z"/>',
		'tiktok'      => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5h.5"/><path d="M14 3a5 5 0 0 0 5 5"/>',
	);
}

/**
 * Cetak ikon SVG.
 *
 * @param string $name  Nama ikon.
 * @param int    $size  Ukuran px.
 * @param string $class Kelas tambahan.
 * @return string
 */
function thig_icon( $name, $size = 24, $class = '' ) {
	$lib = thig_icon_library();
	if ( ! isset( $lib[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon icon-%1$s %2$s" width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		(int) $size,
		$lib[ $name ]
	);
}

/**
 * Daftar kanal sosial yang terisi di Customizer.
 *
 * @return array<int,array<string,string>>
 */
function thig_social_links() {
	$channels = array(
		'facebook'  => __( 'Facebook', 'thi-glass' ),
		'instagram' => __( 'Instagram', 'thi-glass' ),
		'twitter'   => __( 'X / Twitter', 'thi-glass' ),
		'youtube'   => __( 'YouTube', 'thi-glass' ),
		'linkedin'  => __( 'LinkedIn', 'thi-glass' ),
		'tiktok'    => __( 'TikTok', 'thi-glass' ),
		'whatsapp'  => __( 'WhatsApp', 'thi-glass' ),
	);

	$out = array();
	foreach ( $channels as $key => $label ) {
		$url = thig_opt( 'social_' . $key, '' );
		if ( $url ) {
			$out[] = array(
				'key'   => $key,
				'label' => $label,
				'url'   => $url,
			);
		}
	}
	return $out;
}

/**
 * Cetak deretan tombol sosial.
 */
function thig_the_social_row() {
	$links = thig_social_links();
	if ( ! $links ) {
		return;
	}
	echo '<ul class="social-row">';
	foreach ( $links as $link ) {
		printf(
			'<li><a class="social-btn" href="%1$s" target="_blank" rel="noopener noreferrer"><span class="screen-reader-text">%2$s</span>%3$s</a></li>',
			esc_url( $link['url'] ),
			esc_html( $link['label'] ),
			thig_icon( $link['key'], 20 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statis dari pustaka internal.
		);
	}
	echo '</ul>';
}

/**
 * Meta artikel (tanggal, penulis, kategori, estimasi baca).
 *
 * @param bool $with_author Tampilkan penulis.
 */
function thig_the_post_meta( $with_author = true ) {
	echo '<div class="entry-meta">';

	printf(
		'<span class="meta-date">%1$s<time datetime="%2$s"> %3$s</time></span>',
		thig_icon( 'calendar', 14 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);

	if ( $with_author ) {
		printf(
			'<span class="sep">&middot;</span><span class="meta-author">%1$s <a href="%2$s">%3$s</a></span>',
			thig_icon( 'user', 14 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}

	$minutes = thig_reading_time();
	printf(
		'<span class="sep">&middot;</span><span class="meta-read">%1$s %2$s</span>',
		thig_icon( 'clock', 14 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		/* translators: %d: estimasi menit membaca. */
		esc_html( sprintf( _n( '%d menit baca', '%d menit baca', $minutes, 'thi-glass' ), $minutes ) )
	);

	echo '</div>';
}

/**
 * Estimasi waktu baca dalam menit (±200 kata/menit).
 *
 * @return int
 */
function thig_reading_time() {
	$words = str_word_count( wp_strip_all_tags( (string) get_the_content() ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Breadcrumbs sederhana dengan markup aksesibel.
 */
function thig_the_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$sep   = '<span class="sep" aria-hidden="true">/</span>';
	$items = array( sprintf( '<a href="%s">%s</a>', esc_url( home_url( '/' ) ), esc_html__( 'Beranda', 'thi-glass' ) ) );

	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = sprintf( '<a href="%s">%s</a>', esc_url( get_category_link( $cats[0] ) ), esc_html( $cats[0]->name ) );
		}
		$items[] = '<span aria-current="page">' . esc_html( wp_trim_words( get_the_title(), 7, '…' ) ) . '</span>';
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $ancestor ) ), esc_html( get_the_title( $ancestor ) ) );
		}
		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_archive() ) {
		$items[] = '<span aria-current="page">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
	} elseif ( is_search() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'Hasil pencarian', 'thi-glass' ) . '</span>';
	} elseif ( is_404() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'Halaman tidak ditemukan', 'thi-glass' ) . '</span>';
	}

	printf(
		'<nav class="breadcrumbs" aria-label="%s">%s</nav>',
		esc_attr__( 'Remah roti', 'thi-glass' ),
		implode( $sep, $items ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Tiap bagian sudah di-escape.
	);
}

/**
 * Gambar sampul dengan fallback gradien bila post tanpa featured image.
 *
 * @param string $size Ukuran gambar.
 */
function thig_the_card_thumb( $size = 'thig-card' ) {
	if ( has_post_thumbnail() ) {
		the_post_thumbnail(
			$size,
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => the_title_attribute( array( 'echo' => false ) ),
			)
		);
		return;
	}
	printf(
		'<div class="mcard__fallback" aria-hidden="true" style="width:100%%;height:100%%;background:linear-gradient(160deg,var(--c-primary),color-mix(in oklab,var(--c-primary) 55%%,#000));opacity:.9"></div>'
	);
}

/**
 * Paginasi dengan ikon panah.
 */
function thig_the_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => thig_icon( 'chevron-left', 18 ) . '<span class="screen-reader-text">' . esc_html__( 'Halaman sebelumnya', 'thi-glass' ) . '</span>',
			'next_text'          => thig_icon( 'chevron-right', 18 ) . '<span class="screen-reader-text">' . esc_html__( 'Halaman berikutnya', 'thi-glass' ) . '</span>',
			'screen_reader_text' => __( 'Navigasi halaman', 'thi-glass' ),
			'aria_label'         => __( 'Halaman', 'thi-glass' ),
		)
	);
}
