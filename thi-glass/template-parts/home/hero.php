<?php
/**
 * Bagian hero dengan parallax multi-lapis.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_title  = thig_opt( 'hero_title', __( 'Membangun dampak yang bertahan lama', 'thi-glass' ) );
$thig_accent = trim( (string) thig_opt( 'hero_title_accent' ) );
$thig_image  = thig_opt( 'hero_image', '' );

// Bungkus kata aksen dengan gradien, tanpa merusak escaping.
$thig_title_html = esc_html( $thig_title );
if ( $thig_accent ) {
	$thig_title_html = str_replace(
		esc_html( $thig_accent ),
		'<span class="grad">' . esc_html( $thig_accent ) . '</span>',
		$thig_title_html
	);
}

// Kumpulkan statistik yang terisi.
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

<section class="hero hero--center" id="hero">
	<div class="hero__bg" aria-hidden="true">
		<div class="hero__grad"></div>
		<?php if ( $thig_image ) : ?>
			<img class="hero__img" src="<?php echo esc_url( $thig_image ); ?>" alt="" data-parallax="0.18" fetchpriority="high" decoding="async">
		<?php endif; ?>
		<div class="orb orb--1" data-parallax="0.22"></div>
		<div class="orb orb--2" data-parallax="-0.14"></div>
		<div class="orb orb--3" data-parallax="0.10"></div>
		<div class="hero__grid" data-parallax="0.06"></div>
		<div class="hero__noise"></div>
	</div>

	<div class="wrap">
		<div class="hero__content">

			<?php if ( thig_opt( 'hero_badge' ) ) : ?>
				<p class="hero__badge" data-reveal="fade">
					<span class="dot" aria-hidden="true"></span>
					<?php echo esc_html( thig_opt( 'hero_badge' ) ); ?>
				</p>
			<?php endif; ?>

			<h1 class="hero__title" data-reveal data-reveal-delay="80">
				<?php echo wp_kses( $thig_title_html, array( 'span' => array( 'class' => array() ), 'br' => array() ) ); ?>
			</h1>

			<?php if ( thig_opt( 'hero_lead' ) ) : ?>
				<p class="hero__lead" data-reveal data-reveal-delay="160"><?php echo wp_kses_post( thig_opt( 'hero_lead' ) ); ?></p>
			<?php endif; ?>

			<div class="hero__cta" data-reveal data-reveal-delay="240">
				<?php if ( thig_opt( 'hero_cta1_text' ) ) : ?>
					<a class="btn btn--primary" href="<?php echo esc_url( thig_opt( 'hero_cta1_url', '#' ) ); ?>">
						<?php echo esc_html( thig_opt( 'hero_cta1_text' ) ); ?>
						<?php echo thig_icon( 'arrow-right', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<?php if ( thig_opt( 'hero_cta2_text' ) ) : ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( thig_opt( 'hero_cta2_url', '#' ) ); ?>">
						<?php echo esc_html( thig_opt( 'hero_cta2_text' ) ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $thig_stats ) : ?>
				<div class="hero__stats glass" data-reveal data-reveal-delay="320">
					<?php foreach ( $thig_stats as $stat ) : ?>
						<div class="stat">
							<span class="stat__num"
								data-count="<?php echo esc_attr( preg_replace( '/[^0-9.]/', '', $stat['num'] ) ); ?>"
								data-suffix="<?php echo esc_attr( $stat['suffix'] ); ?>"><?php echo esc_html( $stat['num'] . $stat['suffix'] ); ?></span>
							<span class="stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>

	<a class="hero__scroll" href="#konten-utama">
		<span class="mouse" aria-hidden="true"></span>
		<span><?php esc_html_e( 'Gulir', 'thi-glass' ); ?></span>
	</a>
</section>

<span id="konten-utama" class="screen-reader-text" tabindex="-1"><?php esc_html_e( 'Awal konten', 'thi-glass' ); ?></span>
