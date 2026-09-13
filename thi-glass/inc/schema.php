<?php
/**
 * Data terstruktur (JSON-LD) & meta sosial.
 *
 * @package THI_Glass
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cetak JSON-LD Organization + WebSite (+ Article pada single post).
 */
function thig_json_ld() {
	$logo_id  = get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';

	$org = array(
		'@type'  => 'Organization',
		'@id'    => home_url( '/#organization' ),
		'name'   => get_bloginfo( 'name' ),
		'url'    => home_url( '/' ),
	);

	$description = thig_opt( 'org_description', get_bloginfo( 'description' ) );
	if ( $description ) {
		$org['description'] = $description;
	}
	if ( $logo_url ) {
		$org['logo'] = $logo_url;
	}

	$address = array_filter(
		array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => thig_opt( 'contact_address', '' ),
			'addressLocality' => thig_opt( 'contact_city', '' ),
			'addressCountry'  => thig_opt( 'contact_country', 'ID' ),
		)
	);
	if ( count( $address ) > 2 ) {
		$org['address'] = $address;
	}

	$phone = thig_opt( 'contact_phone', '' );
	$email = thig_opt( 'contact_email', '' );
	if ( $phone ) {
		$org['telephone'] = $phone;
	}
	if ( $email ) {
		$org['email'] = $email;
	}

	$socials = wp_list_pluck( thig_social_links(), 'url' );
	if ( $socials ) {
		$org['sameAs'] = array_values( $socials );
	}

	$graph = array(
		$org,
		array(
			'@type'     => 'WebSite',
			'@id'       => home_url( '/#website' ),
			'url'       => home_url( '/' ),
			'name'      => get_bloginfo( 'name' ),
			'publisher' => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		),
	);

	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'         => 'Article',
			'@id'           => get_permalink() . '#article',
			'headline'      => wp_strip_all_tags( get_the_title() ),
			'datePublished' => get_the_date( DATE_W3C ),
			'dateModified'  => get_the_modified_date( DATE_W3C ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'publisher'     => array( '@id' => home_url( '/#organization' ) ),
			'mainEntityOfPage' => get_permalink(),
			'image'         => has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'thig-wide' ) : $logo_url,
		);
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( array_filter( $graph ) ),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'thig_json_ld', 20 );

/**
 * Meta Open Graph & Twitter Card dasar (dilewati bila plugin SEO aktif).
 */
function thig_social_meta() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return; // Hindari duplikasi dengan plugin SEO.
	}

	$title = wp_get_document_title();
	$desc  = is_singular() && ! is_front_page()
		? wp_trim_words( wp_strip_all_tags( (string) get_the_excerpt() ), 30, '…' )
		: ( thig_opt( 'org_description', get_bloginfo( 'description' ) ) );

	$image = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( null, 'thig-wide' );
	} elseif ( thig_opt( 'hero_image', '' ) ) {
		$image = thig_opt( 'hero_image', '' );
	} elseif ( get_theme_mod( 'custom_logo' ) ) {
		$image = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
	}

	$tags = array(
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => $title,
		'og:description' => $desc,
		'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
		'og:url'         => is_singular() ? get_permalink() : home_url( add_query_arg( array() ) ),
		'og:locale'      => get_locale(),
	);
	if ( $image ) {
		$tags['og:image'] = $image;
	}

	foreach ( $tags as $property => $content ) {
		if ( $content ) {
			printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
		}
	}

	printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
}
add_action( 'wp_head', 'thig_social_meta', 5 );
