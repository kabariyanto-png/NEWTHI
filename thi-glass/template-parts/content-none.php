<?php
/**
 * Tampilan bila tidak ada konten.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="glass entry text-center">
	<h2><?php esc_html_e( 'Belum ada yang bisa ditampilkan', 'thi-glass' ); ?></h2>

	<?php if ( is_search() ) : ?>
		<p class="muted mt-4"><?php esc_html_e( 'Kata kunci Anda tidak menemukan hasil. Coba istilah lain yang lebih umum.', 'thi-glass' ); ?></p>
		<div class="mt-6" style="max-width:420px;margin-inline:auto"><?php get_search_form(); ?></div>
	<?php else : ?>
		<p class="muted mt-4"><?php esc_html_e( 'Konten untuk bagian ini belum tersedia. Silakan kembali lagi nanti.', 'thi-glass' ); ?></p>
		<p class="mt-6"><a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Kembali ke beranda', 'thi-glass' ); ?></a></p>
	<?php endif; ?>
</div>
