<?php

namespace ImageProxy\Classes;

use ImageProxy\Admin\Page;
use WP_Error;

class Builder {

	private $options = [];

	private $key = '943b421c9eb07c830af81030552c86009268de4e532ba2ee2eab8247c6da0881';

	private $salt = '520f986b998545b4785e0defbc4f3c1203f22de2374a3d53cb7a7fe9fea309c5';

	private $host = 'https://cdn-0.royalcheese.ru';

	public function __construct() {
		$this->options = Page::getOptions();
	}

	public function __get( $prop ) {
		if ( in_array( $prop, [ 'key', 'key' ] ) ) {
			return $this->options[ $prop ];
		} elseif ( 'host' == $prop ) {
			$hosts = $this->parseHostsOptions();

			return $hosts[0];
		}
	}

	private function getHostByImage( $salt ) {
		$object = new SelectCdnAddress( $salt, $this->parseHostsOptions() );

		return $object->getAddress();
	}

	/**
	 * Возвращает доступные домены CDN в виде массива
	 *
	 * @return array|string
	 */
	private function parseHostsOptions() {
		$host = $this->options['host'];

		if ( false === strpos( $host, ',' ) ) {
			return [ trim( $host ) ];
		}

		$array = explode( ',', $host );

		return array_map( 'trim', $array );
	}

	public function sign( $path ) {
		$keyBin = pack( "H*", $this->key );
		if ( empty( $keyBin ) ) {

			return new WP_Error( 'error', 'Key expected to be hex-encoded string' );
		}

		$saltBin = pack( "H*", $this->salt );

		if ( empty( $saltBin ) ) {

			return new WP_Error( 'error', 'Salt expected to be hex-encoded string' );
		}

		$signature = rtrim( strtr( base64_encode( hash_hmac( 'sha256', $saltBin . $path, $keyBin, true ) ), '+/', '-_' ), '=' );

		return sprintf( "/%s%s", $signature, $path );
	}

	public function builder( $data, $url ) {
		$default = [
			'resize'  => 'fill',
			'width'   => 0,
			'height'  => 0,
			'gravity' => 'no',
			'enlarge' => 1,
		];

		$data = wp_parse_args( $data, $default );

		array_unshift( $data, '' );

		array_push( $data, rtrim( strtr( base64_encode( $url ), '+/', '-_' ), '=' ) );

		$path = implode( '/', $data );

		return $this->getHostByImage( $url ) . $this->sign( $path );

	}

	public function builderAdvanced( $data, $url ) {
		$default = [
			'rs'    => [
				'resizing_type' => 'fill',
				'width'         => 0,
				'height'        => 0,
				'dpr'           => '',
				'enlarge'       => 0,
				'extend'        => '',
				'g'             => '',
				'c'             => '',
				't'             => '',
				'q'             => '',
				'mb'            => '',
				'bg'            => '',
				'bl'            => '',
			],
			'wm'    => [
				'opacity'  => '',
				'position' => '',
				'x_offset' => '',
				'y_offset' => '',
				'scale'    => '',
			],
			'pr'    => [],
			'cb'    => '',
			'fn'    => '',
			'plain' => '',
			'ext'   => '',
		];

		$data = wp_parse_args( $data, $default );

		$extension = $data['ext'];
		unset( $data['ext'] );

		$array = [];
		foreach ( $data as $k => $v ) {
			if ( ! empty( $v ) ) {

				if ( is_array( $v ) && ! empty( array_diff( $v, [ '' ] ) ) ) {
					$array[] = "{$k}:" . implode( ':', array_diff( $v, [ '' ] ) );
				}

			}
		}

		if ( ! empty( $data['cb'] ) ) {
			$array[] = $data['cb'];
		}

		if ( ! empty( $data['fn'] ) ) {
			$array[] = $data['fn'];
		}

		if ( ! empty( $data['plain'] ) ) {

			if ( ! empty( $extension ) ) {
				$url .= "@{$extension}";
			}

			$array[] = "plain/{$url}";

		} else {

			$url = rtrim( strtr( base64_encode( $url ), '+/', '-_' ), '=' );

			if ( ! empty( $extension ) ) {
				$url .= ".{$extension}";
			}

			$array[] = $url;
		}

		$query = "/" . implode( '/', $array );

		return $this->getHostByImage( $url ) . $this->sign( $query );

	}

}
