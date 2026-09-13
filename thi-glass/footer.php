<?php
/**
 * Footer situs.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_phone   = thig_opt( 'contact_phone', '' );
$thig_email   = thig_opt( 'contact_email', '' );
$thig_address = thig_opt( 'contact_address', '' );
$thig_city    = thig_opt( 'contact_city', '' );
$thig_credit  = thig_opt( 'footer_credit', '' );
?>
</main><!-- #main -->

<footer class="site-footer" id="kontak">
	<div class="wrap footer-inner">
		<div class="footer-grid">

			<div class="footer-col footer-about">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="brand">
						<span class="brand-mark" aria-hidden="true"><?php echo esc_html( mb_substr( wp_strip_all_tags( get_bloginfo( 'name' ) ), 0, 3 ) ); ?></span>
						<span class="brand-text"><span class="brand-name"><?php bloginfo( 'name' ); ?></span></span>
					</span>
				<?php endif; ?>

				<?php
				$thig_desc = thig_opt( 'org_description', get_bloginfo( 'description' ) );
				if ( $thig_desc ) :
					?>
					<p><?php echo wp_kses_post( $thig_desc ); ?></p>
				<?php endif; ?>

				<?php thig_the_social_row(); ?>
			</div>

			<div class="footer-col">
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<h2 class="widget-title"><?php esc_html_e( 'Tautan Cepat', 'thi-glass' ); ?></h2>
					<nav aria-label="<?php esc_attr_e( 'Navigasi footer', 'thi-glass' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'depth'          => 1,
							)
						);
						?>
					</nav>
				<?php elseif ( is_active_sidebar( 'footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'footer-1' ); ?>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
					<?php dynamic_sidebar( 'footer-2' ); ?>
				<?php else : ?>
					<h2 class="widget-title"><?php esc_html_e( 'Terbaru', 'thi-glass' ); ?></h2>
					<ul>
						<?php
						foreach ( get_posts( array( 'numberposts' => 4 ) ) as $thig_recent ) {
							printf(
								'<li><a href="%s">%s</a></li>',
								esc_url( get_permalink( $thig_recent ) ),
								esc_html( wp_trim_words( get_the_title( $thig_recent ), 8, '…' ) )
							);
						}
						?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<h2 class="widget-title"><?php esc_html_e( 'Hubungi Kami', 'thi-glass' ); ?></h2>
				<ul>
					<?php if ( $thig_address || $thig_city ) : ?>
						<li class="info-tile" style="padding:0;gap:.6rem;margin-bottom:.5rem">
							<span class="info-tile__icon" style="width:34px;height:34px;border-radius:10px"><?php echo thig_icon( 'map-pin', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo nl2br( esc_html( trim( $thig_address . "\n" . $thig_city ) ) ); ?></span>
						</li>
					<?php endif; ?>

					<?php if ( $thig_phone ) : ?>
						<li class="info-tile" style="padding:0;gap:.6rem;margin-bottom:.5rem">
							<span class="info-tile__icon" style="width:34px;height:34px;border-radius:10px"><?php echo thig_icon( 'phone', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $thig_phone ) ); ?>"><?php echo esc_html( $thig_phone ); ?></a>
						</li>
					<?php endif; ?>

					<?php if ( $thig_email ) : ?>
						<li class="info-tile" style="padding:0;gap:.6rem">
							<span class="info-tile__icon" style="width:34px;height:34px;border-radius:10px"><?php echo thig_icon( 'mail', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<a href="mailto:<?php echo esc_attr( $thig_email ); ?>"><?php echo esc_html( $thig_email ); ?></a>
						</li>
					<?php endif; ?>
				</ul>
			</div>

		</div>

		<div class="footer-bottom">
			<p>
				<?php
				if ( $thig_credit ) {
					echo wp_kses_post( $thig_credit );
				} else {
					printf(
						/* translators: 1: tahun berjalan, 2: nama situs. */
						esc_html__( '© %1$s %2$s. Seluruh hak cipta dilindungi.', 'thi-glass' ),
						esc_html( gmdate( 'Y' ) ),
						esc_html( get_bloginfo( 'name' ) )
					);
				}
				?>
			</p>

			<?php if ( has_nav_menu( 'legal' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Navigasi legal', 'thi-glass' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'legal',
							'container'      => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</footer>

<button type="button" class="to-top" aria-label="<?php esc_attr_e( 'Kembali ke atas halaman', 'thi-glass' ); ?>">
	<?php echo thig_icon( 'arrow-up', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</button>

<?php wp_footer(); ?>
</body>
</html>
