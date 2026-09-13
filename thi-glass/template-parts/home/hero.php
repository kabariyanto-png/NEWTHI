<?php
/**
 * Hero editorial: rata kiri, dua kolom, tipografi serif.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_title  = thig_opt( 'hero_title' );
$thig_accent = trim( (string) thig_opt( 'hero_title_accent' ) );
$thig_image  = thig_opt( 'hero_image', '' );

// Beri penanda tipis pada kata kunci judul, tanpa merusak escaping.
$thig_title_html = esc_html( $thig_title );
if ( $thig_accent ) {
	$thig_title_html = str_replace(
		esc_html( $thig_accent ),
		'<em>' . esc_html( $thig_accent ) . '</em>',
		$thig_title_html
	);
}

$thig_stats = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$num = thig_opt( "hero_stat{$i}_num", '' );
	$lbl = thig_opt( "hero_stat{$i}_label", '' );
	if ( '' !== $num && '' !== $lbl ) {
		$thig_stats[] = array(
			'num'    => $num,
			'suffix' => thig_opt( "hero_stat{$i}_suffix", '' ),
			'label'  => $lbl,
		);
	}
}
?>

<section class="hero" id="hero">
	<div class="hero__bg" aria-hidden="true">
		<div class="hero__grad"></div>
		<div class="hero__rule" data-parallax="0.05"></div>
		<div class="hero__noise"></div>
	</div>

	<div class="wrap">
		<div class="hero__inner<?php echo $thig_image ? ' hero__inner--split' : ''; ?>">

			<div class="hero__content">
				<?php if ( thig_opt( 'hero_badge' ) ) : ?>
					<p class="eyebrow" data-reveal="fade"><?php echo esc_html( thig_opt( 'hero_badge' ) ); ?></p>
				<?php endif; ?>

				<h1 class="hero__title" data-reveal data-reveal-delay="60">
					<?php echo wp_kses( $thig_title_html, array( 'em' => array(), 'br' => array() ) ); ?>
				</h1>

				<?php if ( thig_opt( 'hero_lead' ) ) : ?>
					<p class="hero__lead" data-reveal data-reveal-delay="120"><?php echo wp_kses_post( thig_opt( 'hero_lead' ) ); ?></p>
				<?php endif; ?>

				<div class="hero__cta" data-reveal data-reveal-delay="180">
					<?php if ( thig_opt( 'hero_cta1_text' ) ) : ?>
						<a class="btn btn--primary" href="<?php echo esc_url( thig_opt( 'hero_cta1_url' ) ); ?>">
							<?php echo esc_html( thig_opt( 'hero_cta1_text' ) ); ?>
						</a>
					<?php endif; ?>
					<?php if ( thig_opt( 'hero_cta2_text' ) ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( thig_opt( 'hero_cta2_url' ) ); ?>">
							<?php echo esc_html( thig_opt( 'hero_cta2_text' ) ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $thig_image ) : ?>
				<figure class="hero__media" data-reveal="fade" data-reveal-delay="120">
					<img class="hero__img" src="<?php echo esc_url( $thig_image ); ?>" alt=""
						data-parallax="0.10" fetchpriority="high" decoding="async">
				</figure>
			<?php endif; ?>

		</div>

		<?php if ( $thig_stats ) : ?>
			<div class="hero__stats" data-reveal-group>
				<?php foreach ( $thig_stats as $stat ) : ?>
					<div class="stat" data-reveal="fade">
						<span class="stat__num"
							data-count="<?php echo esc_attr( preg_replace( '/[^0-9.]/', '', $stat['num'] ) ); ?>"
							data-suffix="<?php echo esc_attr( $stat['suffix'] ); ?>"><?php echo esc_html( $stat['num'] . $stat['suffix'] ); ?></span>
						<span class="stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
