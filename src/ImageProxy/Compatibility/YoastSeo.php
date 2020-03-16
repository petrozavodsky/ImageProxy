<?php

namespace ImageProxy\Compatibility;

class YoastSeo {

	public function __construct() {
		add_filter( 'wpseo_opengraph_is_valid_image_url', [ $this, 'validImageUrl' ], 10, 2 );
	}

	public function validImageUrl( $valid, $url ) {

		if ( $url ) {
			return true;
		}

		return $valid;
	}
}
