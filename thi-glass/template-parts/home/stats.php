<?php
/**
 * Bagian angka dampak.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_items = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$num = thig_opt( "stat{$i}_num", '' );
	$lbl = thig_opt( "stat{$i}_label", '' );
	if ( '' !== $num && '' !== $lbl ) {
		$thig_items[] = array(
			'num'    => $num,
			'suffix' => thig_opt( "stat{$i}_suffix", '' ),
			'label'  => $lbl,
		);
	}
}
if ( ! $thig_items ) {
	return;
}
?>
<section class="section section--tight section--ambient" id="dampak">
	<div class="wrap">
		<?php if ( thig_opt( 'stats_title' ) ) : ?>
			<div class="sec-head" data-reveal>
				<h2><?php echo esc_html( thig_opt( 'stats_title' ) ); ?></h2>
			</div>
		<?php endif; ?>

		<div class="glass stat-band" data-reveal="zoom">
			<?php foreach ( $thig_items as $item ) : ?>
				<div class="stat">
					<span class="stat__num"
						data-count="<?php echo esc_attr( preg_replace( '/[^0-9.]/', '', $item['num'] ) ); ?>"
						data-suffix="<?php echo esc_attr( $item['suffix'] ); ?>"><?php echo esc_html( $item['num'] . $item['suffix'] ); ?></span>
					<span class="stat__label"><?php echo esc_html( $item['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
