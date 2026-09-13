<?php
/**
 * Penyesuaian markup navigasi.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tandai tautan yang memiliki sub-menu agar pembaca layar tahu ada popup.
 *
 * @param array   $atts Atribut tautan.
 * @param WP_Post $item Item menu.
 * @return array
 */
function thig_nav_link_attributes( $atts, $item ) {
	if ( ! empty( $item->classes ) && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$atts['aria-haspopup'] = 'true';
	}
	if ( ! empty( $item->url ) && thig_is_external_url( $item->url ) ) {
		$atts['rel'] = trim( ( isset( $atts['rel'] ) ? $atts['rel'] . ' ' : '' ) . 'noopener' );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'thig_nav_link_attributes', 10, 2 );

/**
 * Apakah URL mengarah ke luar situs ini?
 *
 * @param string $url URL.
 * @return bool
 */
function thig_is_external_url( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( empty( $host ) ) {
		return false;
	}
	return strtolower( $host ) !== strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
}
