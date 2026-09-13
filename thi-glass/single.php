<?php
/**
 * Detail artikel.
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
				<?php thig_the_post_meta(); ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<div class="content-area<?php echo is_active_sidebar( 'sidebar-1' ) ? ' content-area--sidebar' : ''; ?>">

				<div>
					<article <?php post_class( 'glass entry' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="entry-thumb">
								<?php the_post_thumbnail( 'thig-wide', array( 'fetchpriority' => 'high' ) ); ?>
							</figure>
						<?php endif; ?>

						<div class="prose">
							<?php
							the_content();
							wp_link_pages(
								array(
									'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Halaman artikel', 'thi-glass' ) . '">',
									'after'  => '</nav>',
								)
							);
							?>
						</div>

						<footer class="entry-footer">
							<?php
							$thig_tags = get_the_tags();
							if ( $thig_tags ) :
								?>
								<div class="tag-row">
									<?php foreach ( $thig_tags as $thig_tag ) : ?>
										<a class="chip" href="<?php echo esc_url( get_tag_link( $thig_tag ) ); ?>">
											<?php echo thig_icon( 'tag', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											<?php echo esc_html( $thig_tag->name ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<div class="share-row">
								<span class="muted" style="font-size:var(--fs-xs)"><?php esc_html_e( 'Bagikan:', 'thi-glass' ); ?></span>
								<?php
								$thig_url   = rawurlencode( get_permalink() );
								$thig_title = rawurlencode( get_the_title() );
								$thig_share = array(
									'whatsapp' => 'https://wa.me/?text=' . $thig_title . '%20' . $thig_url,
									'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $thig_url,
									'twitter'  => 'https://twitter.com/intent/tweet?url=' . $thig_url . '&text=' . $thig_title,
									'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $thig_url,
								);
								foreach ( $thig_share as $thig_net => $thig_link ) {
									printf(
										'<a class="social-btn" href="%1$s" target="_blank" rel="noopener noreferrer"><span class="screen-reader-text">%2$s</span>%3$s</a>',
										esc_url( $thig_link ),
										/* translators: %s: nama jejaring sosial. */
										esc_html( sprintf( __( 'Bagikan ke %s', 'thi-glass' ), ucfirst( $thig_net ) ) ),
										thig_icon( $thig_net, 18 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									);
								}
								?>
							</div>
						</footer>
					</article>

					<?php
					$thig_prev = get_previous_post();
					$thig_next = get_next_post();
					if ( $thig_prev || $thig_next ) :
						?>
						<nav class="post-nav" aria-label="<?php esc_attr_e( 'Navigasi artikel', 'thi-glass' ); ?>">
							<?php if ( $thig_prev ) : ?>
								<a class="glass glass--lift prev" href="<?php echo esc_url( get_permalink( $thig_prev ) ); ?>">
									<span class="dir"><?php esc_html_e( 'Sebelumnya', 'thi-glass' ); ?></span>
									<span class="ttl"><?php echo esc_html( get_the_title( $thig_prev ) ); ?></span>
								</a>
							<?php endif; ?>
							<?php if ( $thig_next ) : ?>
								<a class="glass glass--lift next" href="<?php echo esc_url( get_permalink( $thig_next ) ); ?>">
									<span class="dir"><?php esc_html_e( 'Berikutnya', 'thi-glass' ); ?></span>
									<span class="ttl"><?php echo esc_html( get_the_title( $thig_next ) ); ?></span>
								</a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>

				<?php get_sidebar(); ?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
