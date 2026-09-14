<?php
/**
 * Empat kolom kategori: Pengumuman, Blog Guru, Fasilitas, Kegiatan.
 * Tiap kolom menarik pos dari satu kategori — satu sorotan + daftar ringkas.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_cols = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$cat = (int) thig_opt( "col{$i}_category", 0 );
	if ( $cat ) {
		$thig_cols[] = array(
			'cat'   => $cat,
			'title' => thig_opt( "col{$i}_title", '' ),
		);
	}
}
if ( ! $thig_cols ) {
	return;
}
?>
<section class="section" id="informasi">
	<div class="wrap">

		<?php if ( thig_opt( 'cols_title', '' ) ) : ?>
			<div class="sec-head" data-reveal>
				<h2><?php echo esc_html( thig_opt( 'cols_title', '' ) ); ?></h2>
			</div>
		<?php endif; ?>

		<div class="colblock" data-reveal-group>
			<?php
			foreach ( $thig_cols as $col ) :
				$q = new WP_Query(
					array(
						'cat'                 => $col['cat'],
						'posts_per_page'      => (int) thig_opt( 'cols_count', 4 ),
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					)
				);
				if ( ! $q->have_posts() ) {
					wp_reset_postdata();
					continue;
				}

				$term  = get_term( $col['cat'], 'category' );
				$label = $col['title'] ? $col['title'] : ( $term && ! is_wp_error( $term ) ? $term->name : '' );
				$first = true;
				?>
				<div class="colcat" data-reveal>
					<div class="colcat__head">
						<span class="label-tab"><?php echo esc_html( $label ); ?></span>
					</div>

					<ul class="colcat__list">
					<?php
					while ( $q->have_posts() ) :
						$q->the_post();

						if ( $first ) {
							$first = false;
							?>
							<a class="colcat__lead" href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'thig-card', array( 'loading' => 'lazy', 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
								<?php else : ?>
									<span class="mcard__fallback" aria-hidden="true" style="display:block;aspect-ratio:4/3;background:linear-gradient(160deg,var(--c-primary),color-mix(in oklab,var(--c-primary) 55%,#000))"></span>
								<?php endif; ?>
								<span class="colcat__lead-body">
									<time class="colcat__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
									<span class="colcat__lead-title"><?php the_title(); ?></span>
								</span>
							</a>
							<?php
							continue;
						}
						?>
						<li class="colcat__item">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
							<?php else : ?>
								<span class="noimg" aria-hidden="true"></span>
							<?php endif; ?>
							<span>
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</span>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
					</ul>

					<?php if ( $term && ! is_wp_error( $term ) ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( get_category_link( $term ) ); ?>">
							<?php esc_html_e( 'Selengkapnya', 'thi-glass' ); ?>
							<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<?php
			endforeach;
			?>
		</div>

	</div>
</section>
