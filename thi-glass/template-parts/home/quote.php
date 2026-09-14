<?php
/**
 * Pita kutipan (ayat, hadis, atau motto lembaga).
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_quote = thig_opt( 'quote_text', '' );
if ( ! $thig_quote ) {
	return;
}
?>
<section class="quoteband" data-reveal="fade">
	<div class="wrap">
		<blockquote>
			<?php echo wp_kses_post( $thig_quote ); ?>
			<?php if ( thig_opt( 'quote_source', '' ) ) : ?>
				<cite><?php echo esc_html( thig_opt( 'quote_source', '' ) ); ?></cite>
			<?php endif; ?>
		</blockquote>
	</div>
</section>
