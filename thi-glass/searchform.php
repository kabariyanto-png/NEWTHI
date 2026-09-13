<?php
/**
 * Formulir pencarian.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_id = 'search-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $thig_id ); ?>">
		<span class="screen-reader-text"><?php esc_html_e( 'Cari di situs ini', 'thi-glass' ); ?></span>
		<input type="search" id="<?php echo esc_attr( $thig_id ); ?>" class="search-field" name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php esc_attr_e( 'Cari artikel…', 'thi-glass' ); ?>">
	</label>
	<button type="submit" class="icon-btn" style="background:var(--c-primary);color:var(--c-primary-ink)">
		<span class="screen-reader-text"><?php esc_html_e( 'Cari', 'thi-glass' ); ?></span>
		<?php echo thig_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</form>
