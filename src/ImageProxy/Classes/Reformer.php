<?php

namespace ImageProxy\Classes;

class Reformer {
	private $proxy;

	public function __construct() {

		if ( ! is_admin() || wp_doing_ajax() ) {

			$this->proxy = new Builder();

			add_filter( 'wp_get_attachment_image_src', [ $this, 'src' ], 20, 3 );

			add_filter( 'wp_calculate_image_srcset', [ $this, 'srcset' ], 20, 5 );

			add_filter( 'the_content', [ $this, 'postHtml' ], 20 );

			add_filter( 'wp_get_attachment_metadata', [ $this, 'generateVirtualSizes' ], 20, 1 );

		}

		add_filter( 'intermediate_image_sizes_advanced', [ $this, 'disableGenerateThumbnails' ], 10, 1 );

	}

	public function disableGenerateThumbnails( $new_sizes ) {
		return $this->getDefaultImageSize();
	}

	/**
	 * @param $data
	 * Тут добавляем несуществующие размеры изображений
	 *
	 * @return mixed
	 */
	public function generateVirtualSizes( $data ) {

		$sizes = wp_get_additional_image_sizes();

		$sizes = array_merge( $sizes, $this->getDefaultImageSize() );

		$funct = function ( $elem ) {
			$c = 50;

			$width  = $elem['width'];
			$height = $elem['height'];

			if ( $c <= $width && $c <= $height ) {
				$out = [];

				if ( $width > $height ) {
					$p = $width / $height;
					$i = $width;

					for ( ; $i > $c; $i = $i - $c ) {

						if ( 300 > $i ) {
							continue;
						}

						$out[] = [ 'width' => $i, 'height' => round( $i / $p ) ];
					}
				} else {
					$p = $height / $width;
					$i = $height;

					for ( ; $i > $c; $i = $i - $c ) {

						if ( 300 > $i ) {
							continue;
						}

						$out[] = [ 'width' => round( $i / $p ), 'height' => $i ];
					}
				}

				return $out;
			}

			return false;
		};

		$items = [];
		foreach ( $sizes as $size ) {

			$width                                      = $size['width'];
			$height                                     = $size['height'];
			$items ["_srcset_image_{$width}x{$height}"] = $size;

			$additionalSizes = $funct( $size );

			if ( false !== $additionalSizes ) {
				foreach ( $additionalSizes as $additionalSize ) {
					$items ["_srcset_image_{$additionalSize['width']}x{$additionalSize['height']}"] = [
						'width'  => $additionalSize['width'],
						'height' => $additionalSize['height'],
						'crop'   => $size['crop']
					];
				}
			}

			$data['sizes'] = $items;
		}

		return $data;
	}

	/**
	 * @return array
	 *
	 * Получаем размеры стандарных миниатюр
	 */
	private function getDefaultImageSize() {
		$defaultSizes = [ 'thumbnail', 'medium', 'medium_large', 'large' ];

		$out = [];
		foreach ( $defaultSizes as $defaultSize ) {

			$width  = (int) get_option( "{$defaultSize}_size_w", 0 );
			$height = (int) get_option( "{$defaultSize}_size_h", 0 );

			$out[ $defaultSize ] = [
				'width'  => $width,
				'height' => $height,
				'crop'   => get_option( "{$defaultSize}_crop", false ),
			];
		}

		return $out;
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

		$sizes = wp_get_additional_image_sizes();
		$sizes = array_merge( $sizes, $this->getDefaultImageSize() );

		$s = "?origin=" . _wp_get_attachment_relative_path( $image[0] . "/" . basename( $image[0] ) );

		$image[0] = str_replace( '://royalcheese.lc/', '://royalcheese.ru/', $image['0'] );

		if ( isset( $image[0] ) ) {

			if ( is_string( $size ) ) {
				$sizeMeta = ( isset( $sizes[ $size ] ) ? $sizes[ $size ] : 0 );

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
