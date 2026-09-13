<?php
/**
 * Halaman 404.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="hero hero--centered">
	<div class="hero__bg" aria-hidden="true">
		<div class="hero__grad"></div>
		<div class="hero__rule" data-parallax="0.04"></div>
	</div>

	<div class="wrap">
		<div class="hero__content">
			<p class="eyebrow"><?php esc_html_e( 'Kesalahan 404', 'thi-glass' ); ?></p>
			<h1 class="hero__title"><?php esc_html_e( 'Halaman tidak ditemukan', 'thi-glass' ); ?></h1>
			<p class="hero__lead"><?php esc_html_e( 'Alamat yang Anda tuju mungkin sudah dipindahkan atau dihapus. Coba cari kembali, atau kembali ke beranda.', 'thi-glass' ); ?></p>

			<div class="hero__search"><?php get_search_form(); ?></div>

			<div class="hero__cta">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Kembali ke Beranda', 'thi-glass' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
