<?php
/**
 * Halaman statis.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<section class="page-hero">
		<div class="hero__bg" aria-hidden="true">
			<div class="hero__grad"></div>
			<div class="hero__rule" data-parallax="0.04"></div>
		</div>
		<div class="wrap">
			<div class="page-hero__content">
				<?php thig_the_breadcrumbs(); ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<article <?php post_class( 'glass entry' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="entry-thumb"><?php the_post_thumbnail( 'thig-wide' ); ?></figure>
				<?php endif; ?>

				<div class="prose">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Halaman', 'thi-glass' ) . '">',
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
