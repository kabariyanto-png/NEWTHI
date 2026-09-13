<?php
/**
 * Bagian berita terbaru.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_args = array(
	'posts_per_page'      => max( 2, (int) thig_opt( 'news_count' ) ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
$thig_cat = (int) thig_opt( 'news_category' );
if ( $thig_cat ) {
	$thig_args['cat'] = $thig_cat;
}

$thig_query = new WP_Query( $thig_args );
if ( ! $thig_query->have_posts() ) {
	return;
}

$thig_blog_url = get_permalink( get_option( 'page_for_posts' ) );
?>
<section class="section section--ambient" id="berita">
	<div class="wrap">

		<div class="sec-head sec-head--row" data-reveal>
			<div>
				<?php if ( thig_opt( 'news_eyebrow' ) ) : ?>
					<p class="eyebrow"><?php echo esc_html( thig_opt( 'news_eyebrow' ) ); ?></p>
				<?php endif; ?>
				<h2><?php echo esc_html( thig_opt( 'news_title' ) ); ?></h2>
			</div>
			<?php if ( $thig_blog_url ) : ?>
				<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( $thig_blog_url ); ?>">
					<?php esc_html_e( 'Semua berita', 'thi-glass' ); ?>
					<?php echo thig_icon( 'arrow-right', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="grid grid--3" data-reveal-group>
			<?php
			while ( $thig_query->have_posts() ) :
				$thig_query->the_post();
				?>
				<article <?php post_class( 'glass glass--lift mcard' ); ?> data-reveal>
					<a class="mcard__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php thig_the_card_thumb(); ?>
					</a>
					<div class="mcard__body">
						<div class="mcard__meta">
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<span class="sep" aria-hidden="true">&middot;</span>
							<span><?php echo esc_html( sprintf( /* translators: %d: menit. */ _n( '%d menit baca', '%d menit baca', thig_reading_time(), 'thi-glass' ), thig_reading_time() ) ); ?></span>
						</div>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="mcard__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16, '…' ) ); ?></p>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

	</div>
</section>
