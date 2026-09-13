<?php
/**
 * Halaman depan.
 *
 * Jika Pengaturan > Membaca diatur ke "Halaman statis", isi halaman itu
 * tetap dirender di bawah bagian-bagian bawaan theme.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/home/hero' );

if ( thig_opt( 'about_enable' ) ) {
	get_template_part( 'template-parts/home/about' );
}
if ( thig_opt( 'program_enable' ) ) {
	get_template_part( 'template-parts/home/programs' );
}
if ( thig_opt( 'stats_enable' ) ) {
	get_template_part( 'template-parts/home/stats' );
}
if ( thig_opt( 'testi_enable' ) ) {
	get_template_part( 'template-parts/home/testimonials' );
}
if ( thig_opt( 'partner_enable' ) ) {
	get_template_part( 'template-parts/home/partners' );
}
if ( thig_opt( 'news_enable' ) ) {
	get_template_part( 'template-parts/home/news' );
}

// Konten halaman statis (bila ada isinya).
if ( 'page' === get_option( 'show_on_front' ) && have_posts() ) {
	while ( have_posts() ) {
		the_post();
		if ( trim( wp_strip_all_tags( get_the_content() ) ) !== '' ) {
			?>
			<section class="section">
				<div class="wrap">
					<div class="glass entry">
						<div class="prose"><?php the_content(); ?></div>
					</div>
				</div>
			</section>
			<?php
		}
	}
}

if ( thig_opt( 'cta_enable' ) ) {
	get_template_part( 'template-parts/home/cta' );
}

get_footer();
