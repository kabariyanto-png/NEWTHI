<?php
/**
 * Sambutan pimpinan (Kepala Sekolah / Ketua Yayasan).
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_text = thig_opt( 'sambutan_text', '' );
if ( ! $thig_text ) {
	return;
}
$thig_photo = thig_opt( 'sambutan_photo', '' );
?>
<section class="section section--ambient" id="sambutan">
	<div class="wrap">
		<div class="sec-head" data-reveal>
			<?php if ( thig_opt( 'sambutan_label' ) ) : ?>
				<p><span class="label-tab"><?php echo esc_html( thig_opt( 'sambutan_label' ) ); ?></span></p>
			<?php endif; ?>
			<h2 style="margin-top:.75rem"><?php echo esc_html( thig_opt( 'sambutan_title' ) ); ?></h2>
		</div>

		<div class="sambutan">
			<?php if ( $thig_photo ) : ?>
				<figure class="sambutan__figure" data-reveal="left">
					<img src="<?php echo esc_url( $thig_photo ); ?>"
						alt="<?php echo esc_attr( thig_opt( 'sambutan_name', '' ) ); ?>"
						loading="lazy" decoding="async">
					<figcaption class="sambutan__caption">
						<span class="sambutan__name"><?php echo esc_html( thig_opt( 'sambutan_name', '' ) ); ?></span>
						<?php if ( thig_opt( 'sambutan_role', '' ) ) : ?>
							<span class="sambutan__role"><?php echo esc_html( thig_opt( 'sambutan_role', '' ) ); ?></span>
						<?php endif; ?>
					</figcaption>
				</figure>
			<?php endif; ?>

			<div class="sambutan__body" data-reveal="right">
				<blockquote><?php echo wp_kses_post( wpautop( $thig_text ) ); ?></blockquote>

				<?php if ( ! $thig_photo && thig_opt( 'sambutan_name', '' ) ) : ?>
					<p>
						<strong><?php echo esc_html( thig_opt( 'sambutan_name', '' ) ); ?></strong><br>
						<span class="muted"><?php echo esc_html( thig_opt( 'sambutan_role', '' ) ); ?></span>
					</p>
				<?php endif; ?>

				<?php if ( thig_opt( 'sambutan_cta_text', '' ) && thig_opt( 'sambutan_cta_url', '' ) ) : ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( thig_opt( 'sambutan_cta_url', '' ) ); ?>">
						<?php echo esc_html( thig_opt( 'sambutan_cta_text', '' ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
