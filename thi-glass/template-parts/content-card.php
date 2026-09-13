<?php
/**
 * Kartu artikel untuk daftar/arsip.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'glass glass--lift mcard' ); ?> data-reveal>
	<a class="mcard__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php thig_the_card_thumb(); ?>
	</a>
	<div class="mcard__body">
		<div class="mcard__meta">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php
			$thig_cats = get_the_category();
			if ( $thig_cats ) :
				?>
				<span class="sep" aria-hidden="true">&middot;</span>
				<a href="<?php echo esc_url( get_category_link( $thig_cats[0] ) ); ?>"><?php echo esc_html( $thig_cats[0]->name ); ?></a>
			<?php endif; ?>
		</div>

		<h2 style="font-size:var(--fs-lg)"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="mcard__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>

		<a class="link-arrow" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Baca selengkapnya', 'thi-glass' ); ?>
			<span class="screen-reader-text"><?php echo esc_html( get_the_title() ); ?></span>
			<?php echo thig_icon( 'arrow-right', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</article>
