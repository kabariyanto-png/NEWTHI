<?php
/**
 * Bagian "Tentang Kami".
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_points = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$point = thig_opt( "about_point{$i}", '' );
	if ( $point ) {
		$thig_points[] = $point;
	}
}
$thig_img = thig_opt( 'about_image', '' );
?>
<section class="section section--ambient" id="tentang">
	<div class="wrap">
		<div class="split<?php echo $thig_img ? '' : ' split--solo'; ?>">

			<div data-reveal="left">
				<?php if ( thig_opt( 'about_eyebrow' ) ) : ?>
					<p class="eyebrow"><?php echo esc_html( thig_opt( 'about_eyebrow' ) ); ?></p>
				<?php endif; ?>

				<h2><?php echo esc_html( thig_opt( 'about_title' ) ); ?></h2>

				<?php if ( thig_opt( 'about_text' ) ) : ?>
					<p class="muted mt-4"><?php echo wp_kses_post( thig_opt( 'about_text' ) ); ?></p>
				<?php endif; ?>

				<?php if ( $thig_points ) : ?>
					<ul class="check-list">
						<?php foreach ( $thig_points as $point ) : ?>
							<li>
								<?php echo thig_icon( 'check', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php echo esc_html( $point ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( thig_opt( 'about_cta_text' ) && thig_opt( 'about_cta_url', '' ) ) : ?>
					<p class="mt-6">
						<a class="btn btn--primary" href="<?php echo esc_url( thig_opt( 'about_cta_url', '' ) ); ?>">
							<?php echo esc_html( thig_opt( 'about_cta_text' ) ); ?>
							<?php echo thig_icon( 'arrow-right', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $thig_img ) : ?>
				<div class="split__media" data-reveal="right">
					<img src="<?php echo esc_url( $thig_img ); ?>"
						alt="<?php echo esc_attr( thig_opt( 'about_title' ) ); ?>"
						loading="lazy" decoding="async" data-parallax="0.07">
					<?php if ( thig_opt( 'hero_stat1_num', '' ) ) : ?>
						<div class="glass float-card">
							<span class="stat__num" style="font-size:var(--fs-xl)"><?php echo esc_html( thig_opt( 'hero_stat1_num', '' ) . thig_opt( 'hero_stat1_suffix', '' ) ); ?></span>
							<span class="stat__label"><?php echo esc_html( thig_opt( 'hero_stat1_label', '' ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
