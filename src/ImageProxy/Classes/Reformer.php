<?php

namespace ImageProxy\Classes;


use DOMDocument;
use ImageProxy\Admin\Page;

class Reformer {
	private $proxy;

	public function __construct() {

		$this->proxy = new Builder();

		add_filter( 'wp_get_attachment_image_src', [ $this, 'src' ], 20, 3 );
		add_filter( 'the_content', [ $this, 'postHtml' ], 20 );
		add_filter( 'wp_get_attachment_metadata', [ $this, 'srcset' ], 20, 2 );

		// так можно обрубить создание миниатюр при загрузке на сайт
//		add_filter( 'intermediate_image_sizes_advanced', function ( $new_sizes, $image_meta, $attachment_id ) {
//			$o = $new_sizes['thumbnail'];
//
//			return [
//				'thumbnail' => $new_sizes['thumbnail']
//			];
//		}, 10, 3 );
	}

	public function srcset( $data, $pid ) {
//		$data['sizes'] = [
//			'image_1440x540' => [
//				'file'      => "miniatyura-bol-1440x540.jpg",
//				'width'     => 1140,
//				'height'    => 450,
//				'mime-type' => "image/jpeg",
//			],
//			'image_720x290'  => [
//				'file'      => "miniatyura-bol-720x290.jpg",
//				'width'     => 720,
//				'height'    => 290,
//				'mime-type' => "image/jpeg",
//			],
//		];

		if ( 189889 == $pid ) {
			$sizes = $data['sizes'];
			d(
				$data,
				$sizes
			);
		}

		return $data;
	}

	private function checkDomainReplace( $url ) {
		$pattern = "/^.*" . str_replace(
				[ '/', 'http', 'https', 'www.' ],
				[ '\\/', '', '', '' ],
				site_url( '' )
			) . '/iU';

		preg_match( $pattern, $url, $matches );

		if ( empty( $matches ) ) {
			return false;
		}

		return true;
	}

	public function postHtml( $html ) {

		return $this->regexSrc( $html );
	}

	public function src( $image, $attachment_id, $size ) {
		global $_wp_additional_image_sizes;

		$image[0] = str_replace( '://royalcheese.lc/', '://royalcheese.ru/', $image['0'] );

		if ( isset( $image[0] ) ) {

			if ( stristr( $image[0], '2020/01/23/miniatyura-bol' ) ) {

			}

			if ( is_string( $size ) ) {
				$sizeMeta = ( isset( $_wp_additional_image_sizes[ $size ] ) ? $_wp_additional_image_sizes[ $size ] : 0 );

				$image[0] = $this->proxy->builder(
					[
						'width'  => empty( $sizeMeta['width'] ) ? 0 : $sizeMeta['width'],
						'height' => empty( $sizeMeta['height'] ) ? 0 : $sizeMeta['height'],
					],
					$image[0]
				);
			} elseif ( is_array( $size ) ) {
				$image[0] = $this->proxy->builder(
					[
						'width'  => ! isset( $size[0] ) ? 0 : $size[0],
						'height' => ! isset( $size[1] ) ? 0 : $size[1],
					],
					$image[0]
				);
			}

		}

		return $image;
	}


	public function regexSrc( $str ) {
		preg_match_all( '~<img.*>~im', $str, $images );

		$array = [];

		if ( isset( $images[0] ) && ! empty( $images[0] ) ) {

			foreach ( $images[0] as $image ) {

				$src = $this->getAttribute( 'src', $image );

				if ( $this->checkDomainReplace( $src ) ) {

					$height        = $this->getAttribute( 'height', $image );
					$width         = $this->getAttribute( 'width', $image );
					$array[ $src ] = $this->proxy->builder(
						[
							'width'  => $width,
							'height' => $height
						],
						$src
					);

				}

			}

			if ( ! empty( $array ) ) {
				return str_replace( array_keys( $array ), array_values( $array ), $str );
			}
		}

		return $str;
	}


	/**
	 * Get html attribute by name
	 *
	 * @param $str
	 * @param $atr
	 *
	 * @return mixed
	 */
	public function getAttribute( $atr, $str ) {
		preg_match( "~{$atr}=[\"|'](.*)[\"|']\s~imU", $str, $m );

		if ( isset( $m[1] ) ) {
			return $m[1];
		}

		return '';
	}

}
