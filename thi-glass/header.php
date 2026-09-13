<?php
/**
 * Header situs.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Lompat ke konten utama', 'thi-glass' ); ?></a>
<div class="scroll-progress" aria-hidden="true"></div>

<header class="site-header" id="site-header">
	<div class="wrap">
		<div class="header-inner">

			<?php
			$has_logo = has_custom_logo();
			if ( $has_logo ) {
				the_custom_logo();
			} else {
				?>
				<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="brand-mark" aria-hidden="true"><?php echo esc_html( mb_substr( wp_strip_all_tags( get_bloginfo( 'name' ) ), 0, 3 ) ); ?></span>
					<span class="brand-text">
						<span class="brand-name"><?php bloginfo( 'name' ); ?></span>
						<?php if ( get_bloginfo( 'description' ) ) : ?>
							<span class="brand-tag"><?php bloginfo( 'description' ); ?></span>
						<?php endif; ?>
					</span>
				</a>
				<?php
			}
			?>

			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Navigasi utama', 'thi-glass' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'depth'          => 3,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="header-actions">
				<button type="button" class="icon-btn theme-toggle"
					aria-pressed="false"
					data-label-dark="<?php esc_attr_e( 'Aktifkan mode gelap', 'thi-glass' ); ?>"
					data-label-light="<?php esc_attr_e( 'Aktifkan mode terang', 'thi-glass' ); ?>"
					aria-label="<?php esc_attr_e( 'Aktifkan mode gelap', 'thi-glass' ); ?>">
					<span class="icon-sun"><?php echo thig_icon( 'sun', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="icon-moon"><?php echo thig_icon( 'moon', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</button>

				<?php
				$header_cta_text = thig_opt( 'hero_cta2_text' );
				$header_cta_url  = thig_opt( 'hero_cta2_url' );
				if ( $header_cta_text && $header_cta_url ) :
					?>
					<a class="btn btn--primary btn--sm header-cta" href="<?php echo esc_url( $header_cta_url ); ?>">
						<?php echo esc_html( $header_cta_text ); ?>
					</a>
				<?php endif; ?>

				<button type="button" class="icon-btn nav-toggle" aria-expanded="false" aria-controls="site-drawer"
					aria-label="<?php esc_attr_e( 'Buka menu navigasi', 'thi-glass' ); ?>">
					<span></span><span></span><span></span>
				</button>
			</div>

		</div>
	</div>
</header>

<div class="nav-scrim"></div>

<aside class="drawer" id="site-drawer" aria-label="<?php esc_attr_e( 'Menu navigasi', 'thi-glass' ); ?>">
	<div class="drawer-head">
		<span class="brand-name"><?php bloginfo( 'name' ); ?></span>
		<button type="button" class="icon-btn drawer-close" aria-label="<?php esc_attr_e( 'Tutup menu', 'thi-glass' ); ?>">
			<?php echo thig_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>

	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<nav aria-label="<?php esc_attr_e( 'Navigasi utama (seluler)', 'thi-glass' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 3,
					'menu_id'        => 'drawer-menu',
				)
			);
			?>
		</nav>
	<?php endif; ?>

	<div class="drawer-foot">
		<?php get_search_form(); ?>
		<?php if ( $header_cta_text && $header_cta_url ) : ?>
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( $header_cta_url ); ?>"><?php echo esc_html( $header_cta_text ); ?></a>
		<?php endif; ?>
		<?php thig_the_social_row(); ?>
	</div>
</aside>

<main id="main" class="site-main">
