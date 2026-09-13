<?php
/**
 * Hasil pencarian.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="page-hero">
	<div class="hero__bg" aria-hidden="true"><div class="hero__grad"></div><div class="hero__rule" data-parallax="0.04"></div></div>
	<div class="wrap">
		<div class="page-hero__content">
			<?php thig_the_breadcrumbs(); ?>
			<h1>
				<?php
				printf(
					/* translators: %s: kata kunci pencarian. */
					esc_html__( 'Hasil untuk “%s”', 'thi-glass' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
			<p>
				<?php
				global $wp_query;
				$thig_found = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
				printf(
					/* translators: %d: jumlah hasil. */
					esc_html( _n( '%d artikel ditemukan.', '%d artikel ditemukan.', $thig_found, 'thi-glass' ) ),
					$thig_found
				);
				?>
			</p>
			<div class="hero__search"><?php get_search_form(); ?></div>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--2" data-reveal-group>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>
			<?php thig_the_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
