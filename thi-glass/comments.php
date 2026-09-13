<?php
/**
 * Area komentar.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area glass">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$thig_count = get_comments_number();
			printf(
				/* translators: %d: jumlah komentar. */
				esc_html( _n( '%d Komentar', '%d Komentar', $thig_count, 'thi-glass' ) ),
				(int) $thig_count
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 44,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => thig_icon( 'chevron-left', 18 ) . '<span class="screen-reader-text">' . esc_html__( 'Komentar sebelumnya', 'thi-glass' ) . '</span>',
				'next_text' => thig_icon( 'chevron-right', 18 ) . '<span class="screen-reader-text">' . esc_html__( 'Komentar berikutnya', 'thi-glass' ) . '</span>',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Kolom komentar ditutup.', 'thi-glass' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit'       => 'btn btn--primary',
			'title_reply'        => __( 'Tinggalkan Komentar', 'thi-glass' ),
			'title_reply_to'     => __( 'Balas kepada %s', 'thi-glass' ),
			'cancel_reply_link'  => __( 'Batal balas', 'thi-glass' ),
			'label_submit'       => __( 'Kirim Komentar', 'thi-glass' ),
			'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Alamat email Anda tidak akan dipublikasikan. Kolom bertanda wajib diisi.', 'thi-glass' ) . '</p>',
			'comment_field'      => sprintf(
				'<p class="field"><label for="comment">%1$s</label><textarea id="comment" name="comment" rows="6" required aria-required="true"></textarea></p>',
				esc_html__( 'Komentar', 'thi-glass' )
			),
		)
	);
	?>
</div>
