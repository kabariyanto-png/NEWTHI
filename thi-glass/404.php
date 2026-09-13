<?php
/**
 * Halaman 404.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="hero hero--center" style="min-height:auto;padding-block:calc(var(--header-h) + 4rem) 5rem">
	<div class="hero__bg" aria-hidden="true">
		<div class="hero__grad"></div>
		<div class="orb orb--1" data-parallax="0.2"></div>
		<div class="orb orb--2" data-parallax="-0.14"></div>
		<div class="hero__grid" data-parallax="0.06"></div>
	</div>

	<div class="wrap">
		<div class="hero__content">
			<p class="eyebrow" style="justify-content:center"><?php esc_html_e( 'Kesalahan 404', 'thi-glass' ); ?></p>
			<h1 class="hero__title"><?php esc_html_e( 'Halaman tidak ditemukan', 'thi-glass' ); ?></h1>
			<p class="hero__lead"><?php esc_html_e( 'Alamat yang Anda tuju mungkin sudah dipindahkan atau dihapus. Coba cari kembali, atau kembali ke beranda.', 'thi-glass' ); ?></p>

			<div style="max-width:460px;margin:0 auto 2rem"><?php get_search_form(); ?></div>

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
