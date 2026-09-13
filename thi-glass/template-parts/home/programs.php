<?php
/**
 * Bagian program / layanan — diambil dari kategori terpilih.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_args = array(
	'posts_per_page'      => max( 2, (int) thig_opt( 'program_count' ) ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
$thig_cat = (int) thig_opt( 'program_category' );
if ( $thig_cat ) {
	$thig_args['cat'] = $thig_cat;
}

$thig_query = new WP_Query( $thig_args );
if ( ! $thig_query->have_posts() ) {
	return;
}
?>
<section class="section section--alt" id="program">
	<div class="wrap">

		<div class="sec-head sec-head--center" data-reveal>
			<?php if ( thig_opt( 'program_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( thig_opt( 'program_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( thig_opt( 'program_title' ) ); ?></h2>
			<?php if ( thig_opt( 'program_desc', '' ) ) : ?>
				<p><?php echo wp_kses_post( thig_opt( 'program_desc', '' ) ); ?></p>
			<?php endif; ?>
		</div>

		<div class="grid grid--3" data-reveal-group>
			<?php
			while ( $thig_query->have_posts() ) :
				$thig_query->the_post();
				?>
				<article <?php post_class( 'glass mcard tilt' ); ?> data-reveal>
					<div class="mcard__media">
						<?php thig_the_card_thumb(); ?>
						<?php
						$thig_cats = get_the_category();
						if ( $thig_cats ) :
							?>
							<span class="chip chip--brand mcard__badge"><?php echo esc_html( $thig_cats[0]->name ); ?></span>
						<?php endif; ?>
					</div>
					<div class="mcard__body tilt__inner">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="mcard__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
						<a class="link-arrow" href="<?php the_permalink(); ?>">
							<?php esc_html_e( 'Pelajari', 'thi-glass' ); ?>
							<span class="screen-reader-text"><?php echo esc_html( get_the_title() ); ?></span>
							<?php echo thig_icon( 'arrow-right', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

	</div>
</section>
