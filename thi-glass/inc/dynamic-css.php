<?php
/**
 * CSS variabel dinamis dari Customizer.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hitung luminansi relatif sebuah warna hex (0..1).
 *
 * @param string $hex Warna hex.
 * @return float
 */
function thig_luminance( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return 0.5;
	}
	$channels = array();
	foreach ( array( 0, 2, 4 ) as $offset ) {
		$value = hexdec( substr( $hex, $offset, 2 ) ) / 255;
		$channels[] = ( $value <= 0.03928 ) ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
	}
	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/**
 * Pilih warna teks (putih/gelap) yang kontrasnya memenuhi WCAG di atas $bg.
 *
 * @param string $bg Warna latar hex.
 * @return string
 */
function thig_contrast_ink( $bg ) {
	return thig_luminance( $bg ) > 0.45 ? '#141029' : '#FFFFFF';
}

/**
 * Bangun blok CSS custom property dari nilai Customizer.
 *
 * @return string
 */
function thig_dynamic_css() {
	$primary   = thig_opt( 'color_primary', '#126B38' );
	$secondary = thig_opt( 'color_secondary', '#3E9A63' );
	$accent    = thig_opt( 'color_accent', '#E4B94C' );

	$blur    = (int) thig_opt( 'glass_blur' );
	$opacity = (float) thig_opt( 'glass_opacity' ) / 100;
	$radius  = (int) thig_opt( 'radius' );

	$vars = array(
		'--c-primary'      => $primary,
		'--c-primary-ink'  => thig_contrast_ink( $primary ),
		'--c-secondary'    => $secondary,
		'--c-accent'       => $accent,
		'--c-accent-ink'   => thig_contrast_ink( $accent ),
		'--glass-blur'     => $blur . 'px',
		'--glass-bg'       => sprintf( 'rgba(255,255,255,%s)', round( $opacity, 2 ) ),
		'--glass-bg-strong'=> sprintf( 'rgba(255,255,255,%s)', round( min( $opacity + 0.23, 0.94 ), 2 ) ),
		'--r-lg'           => $radius . 'px',
		'--r-xl'           => ( $radius + 8 ) . 'px',
	);

	$css = ':root{';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	$css .= '}';

	// Mode gelap: kaca berbasis gelap, bukan putih transparan.
	$css .= sprintf(
		'[data-theme="dark"]{--glass-bg:rgba(12,17,14,%s);--glass-bg-strong:rgba(8,12,10,%s);}',
		round( $opacity, 2 ),
		round( min( $opacity + 0.27, 0.94 ), 2 )
	);

	if ( ! thig_opt( 'load_google_fonts' ) ) {
		$css .= ':root{--font-head:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;--font-body:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;}';
	}

	return $css;
}
