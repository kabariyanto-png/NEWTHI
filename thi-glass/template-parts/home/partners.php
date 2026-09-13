<?php
/**
 * Deretan logo mitra (marquee).
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_logos = array();
for ( $i = 1; $i <= 8; $i++ ) {
	$logo = thig_opt( "partner{$i}_logo", '' );
	if ( $logo ) {
		$thig_logos[] = array(
			'url'  => $logo,
			'name' => thig_opt( "partner{$i}_name", '' ),
		);
	}
}
if ( ! $thig_logos ) {
	return;
}
?>
<section class="section section--tight" id="mitra">
	<div class="wrap">
		<?php if ( thig_opt( 'partner_title' ) ) : ?>
			<p class="eyebrow" style="display:flex;justify-content:center;margin-bottom:2rem">
				<?php echo esc_html( thig_opt( 'partner_title' ) ); ?>
			</p>
		<?php endif; ?>

		<div class="marquee" data-reveal="fade">
			<div class="marquee__track">
				<ul class="marquee__group">
					<?php foreach ( $thig_logos as $logo ) : ?>
						<li>
							<img src="<?php echo esc_url( $logo['url'] ); ?>"
								alt="<?php echo esc_attr( $logo['name'] ? $logo['name'] : __( 'Logo mitra', 'thi-glass' ) ); ?>"
								loading="lazy" decoding="async">
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>
