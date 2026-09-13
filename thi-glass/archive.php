<?php
/**
 * Arsip kategori, tag, penulis, tanggal.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="page-hero">
	<div class="hero__bg" aria-hidden="true">
		<div class="hero__grad"></div>
		<div class="hero__rule" data-parallax="0.04"></div>
	</div>
	<div class="wrap">
		<div class="page-hero__content">
			<?php thig_the_breadcrumbs(); ?>
			<h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
			<?php
			$thig_desc = get_the_archive_description();
			if ( $thig_desc ) :
				?>
				<div class="muted"><?php echo wp_kses_post( $thig_desc ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="content-area<?php echo is_active_sidebar( 'sidebar-1' ) ? ' content-area--sidebar' : ''; ?>">
			<div>
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
			<?php get_sidebar(); ?>
		</div>
	</div>
</section>

<?php
get_footer();
