<?php
/**
 * Agenda kegiatan mendatang.
 *
 * Diambil dari kategori terpilih. Pos dengan tanggal terbit di masa depan
 * dianggap agenda mendatang; kalau tidak ada, pos terbaru yang ditampilkan.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

$thig_cat = (int) thig_opt( 'agenda_category', 0 );
if ( ! $thig_cat ) {
	return;
}

$thig_args = array(
	'cat'                 => $thig_cat,
	'posts_per_page'      => (int) thig_opt( 'agenda_count', 4 ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'post_status'         => array( 'publish', 'future' ),
	'orderby'             => 'date',
	'order'               => 'ASC',
	'date_query'          => array(
		array( 'after' => 'today' ),
	),
);

$thig_query = new WP_Query( $thig_args );

// Tidak ada agenda mendatang: tampilkan yang terbaru sebagai gantinya.
if ( ! $thig_query->have_posts() ) {
	wp_reset_postdata();
	unset( $thig_args['date_query'], $thig_args['post_status'] );
	$thig_args['order'] = 'DESC';
	$thig_query         = new WP_Query( $thig_args );
}
?>
<section class="section section--alt" id="agenda">
	<div class="wrap">
		<div class="sec-head" data-reveal>
			<p><span class="label-tab"><?php echo esc_html( thig_opt( 'agenda_label' ) ); ?></span></p>
			<h2 style="margin-top:.75rem"><?php echo esc_html( thig_opt( 'agenda_title' ) ); ?></h2>
		</div>

		<?php if ( $thig_query->have_posts() ) : ?>
			<ul class="agenda" data-reveal>
				<?php
				while ( $thig_query->have_posts() ) :
					$thig_query->the_post();
					?>
					<li class="agenda__item">
						<span class="agenda__date">
							<?php echo esc_html( get_the_date( 'j M' ) ); ?>
							<small><?php echo esc_html( get_the_date( 'Y' ) ); ?></small>
						</span>
						<span class="agenda__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</span>
					</li>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		<?php else : ?>
			<p class="agenda__empty" data-reveal><?php esc_html_e( 'Belum ada agenda dalam waktu dekat.', 'thi-glass' ); ?></p>
		<?php endif; ?>
	</div>
</section>
