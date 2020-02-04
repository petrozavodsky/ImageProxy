<?php

namespace ImageProxy\Classes;


use DOMDocument;
use ImageProxy\Admin\Page;

class Reformer {
	private $proxy;

	public function __construct() {

		$this->proxy = new Builder();


		add_filter( 'wp_get_attachment_image_src', [ $this, 'src' ], 20, 3 );

		add_filter( 'wp_calculate_image_srcset', [ $this, 'srcset' ], 10, 5 );


		add_filter( 'the_content', [ $this, 'postHtml' ], 20 );

		// TODO тут добавляем несуществующие размеры изображений
//		add_filter( 'wp_get_attachment_metadata', [ $this, 'closure' ], 20, 2 );

		// так можно обрубить создание миниатюр при загрузке на сайт
//		add_filter( 'intermediate_image_sizes_advanced', function ( $new_sizes, $image_meta, $attachment_id ) {
//			$o = $new_sizes['thumbnail'];
//
//			return [
//				'thumbnail' => $new_sizes['thumbnail']
//			];
//		}, 10, 3 );

//		add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
//			if ( $attr['src'] ) {
//
//			}
//
//			return $attr;
//		}, 10, 3 );
	}


	public function urlBySourceSizes( $url, $width, $height ) {

		return $this->proxy->builder(
			[
				'width'  => empty( $width ) ? 0 : $width,
				'height' => empty( $height ) ? 0 : $height,
			],
			$url
		);

	}

	public function srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {

		$sizes = $image_meta['sizes'];

		$sizesByName = function ( $name ) use ( $sizes ) {
			$name = basename( $name );

			foreach ( $sizes as $size ) {

				if ( $name == $size['file'] ) {
					return $size;
				}
			}

			return false;
		};

		$dirUpload  = wp_get_upload_dir();
		$originFile = $dirUpload['baseurl'] . "/" . $image_meta['file'];

		$originFile = str_replace( '://royalcheese.lc/', '://royalcheese.ru/', $originFile );

		$out = [];

		foreach ( $sources as $source ) {
			$findSize = $sizesByName( $source['url'] );

			if ( empty( $findSize ) ) {
				$source['url'] = $image_src;
			} else {

				$source['url'] = $this->proxy->builder(
					[
						'width'  => empty( $findSize['width'] ) ? 0 : $findSize['width'],
						'height' => empty( $findSize['height'] ) ? 0 : $findSize['height'],
					],
					$originFile
				);
			}

			$out[] = $source;
		}


		return $out;


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


		$s = "?origin=" . _wp_get_attachment_relative_path( $image[0] . "/" . basename( $image[0] ) );

		$image[0] = str_replace( '://royalcheese.lc/', '://royalcheese.ru/', $image['0'] );

		if ( isset( $image[0] ) ) {

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
		$image[0] = $image[0] . $s;

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
