<?php
/**
 * Pita ajakan bertindak.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

if ( ! thig_opt( 'cta_title' ) ) {
	return;
}
?>
<section class="section">
	<div class="wrap">
		<div class="cta-band" data-reveal="zoom">
			<h2><?php echo esc_html( thig_opt( 'cta_title' ) ); ?></h2>

			<?php if ( thig_opt( 'cta_text' ) ) : ?>
				<p><?php echo wp_kses_post( thig_opt( 'cta_text' ) ); ?></p>
			<?php endif; ?>

			<div class="cta-band__actions">
				<?php if ( thig_opt( 'cta_btn1_text' ) ) : ?>
					<a class="btn btn--primary" href="<?php echo esc_url( thig_opt( 'cta_btn1_url', '#kontak' ) ); ?>">
						<?php echo esc_html( thig_opt( 'cta_btn1_text' ) ); ?>
						<?php echo thig_icon( 'arrow-right', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<?php if ( thig_opt( 'cta_btn2_text', '' ) ) : ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( thig_opt( 'cta_btn2_url', '#' ) ); ?>">
						<?php echo esc_html( thig_opt( 'cta_btn2_text', '' ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
