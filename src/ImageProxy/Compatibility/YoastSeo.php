<?php

namespace ImageProxy\Compatibility;

use ImageProxy\Classes\SelectCdnAddress;

class YoastSeo {

	public function __construct() {
		add_filter( 'wpseo_opengraph_is_valid_image_url', [ $this, 'validImageUrl' ], 10, 2 );
	}

	public function validImageUrl( $valid, $url ) {

		$hosts = SelectCdnAddress::getOptions();

		if ( empty( $hosts ) ) {
			return false;
		}

		foreach ( $hosts as $host ) {
			if ( false !== stristr( $url, $host ) ) {
				return true;
			}
		}

		return $valid;
	}

}
