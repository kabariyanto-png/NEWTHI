<?php
/**
 * Slider testimoni yang aksesibel.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_items = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$quote = thig_opt( "testi{$i}_quote", '' );
	if ( $quote ) {
		$thig_items[] = array(
			'quote' => $quote,
			'name'  => thig_opt( "testi{$i}_name", '' ),
			'role'  => thig_opt( "testi{$i}_role", '' ),
			'photo' => thig_opt( "testi{$i}_photo", '' ),
		);
	}
}
if ( ! $thig_items ) {
	return;
}
$thig_autoplay = thig_opt( 'testi_autoplay' ) ? 'true' : 'false';
?>
<section class="section section--alt" id="testimoni">
	<div class="wrap wrap--narrow">

		<div class="sec-head sec-head--center" data-reveal>
			<h2><?php echo esc_html( thig_opt( 'testi_title' ) ); ?></h2>
		</div>

		<div class="tslider" data-autoplay="<?php echo esc_attr( $thig_autoplay ); ?>" data-interval="7000"
			role="group" aria-roledescription="<?php esc_attr_e( 'karosel', 'thi-glass' ); ?>"
			aria-label="<?php esc_attr_e( 'Testimoni', 'thi-glass' ); ?>" data-reveal="zoom">

			<div class="tslider__track glass">
				<div class="tslider__list">
					<?php foreach ( $thig_items as $index => $item ) : ?>
						<div class="tslide" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'thi-glass' ); ?>"
							aria-label="<?php echo esc_attr( sprintf( '%1$d / %2$d', $index + 1, count( $thig_items ) ) ); ?>">
							<blockquote><?php echo wp_kses_post( $item['quote'] ); ?></blockquote>
							<div class="tslide__person">
								<?php if ( $item['photo'] ) : ?>
									<img src="<?php echo esc_url( $item['photo'] ); ?>" alt="" loading="lazy" decoding="async" width="52" height="52">
								<?php endif; ?>
								<div>
									<div class="tslide__name"><?php echo esc_html( $item['name'] ); ?></div>
									<?php if ( $item['role'] ) : ?>
										<div class="tslide__role"><?php echo esc_html( $item['role'] ); ?></div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<p class="tslider__live screen-reader-text" aria-live="polite" aria-atomic="true"></p>

			<?php if ( count( $thig_items ) > 1 ) : ?>
				<div class="tslider__ctrl">
					<button type="button" class="icon-btn glass tslider__prev" aria-label="<?php esc_attr_e( 'Testimoni sebelumnya', 'thi-glass' ); ?>">
						<?php echo thig_icon( 'chevron-left', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>

					<div class="tslider__dots"></div>

					<button type="button" class="icon-btn glass tslider__next" aria-label="<?php esc_attr_e( 'Testimoni berikutnya', 'thi-glass' ); ?>">
						<?php echo thig_icon( 'chevron-right', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>

					<?php if ( 'true' === $thig_autoplay ) : ?>
						<button type="button" class="icon-btn glass tslider__play" aria-pressed="true"
							aria-label="<?php esc_attr_e( 'Jeda pergantian testimoni', 'thi-glass' ); ?>">
							<?php echo thig_icon( 'pause', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
