<?php

namespace ImageProxy\Classes;


use ImageProxy\Admin\Page;
use WP_Error;

class SelectCdnAddress {
	private $salt;

	private $addressList = [];

	public function __construct( $salt, $addressList ) {
		$this->salt        = $salt;
		$this->addressList = $addressList;

		if ( empty( $salt ) ) {
			new WP_Error( 'empty_salt', 'pls add salt' );
		}

		if ( empty( $this->addressList ) ) {
			new WP_Error( 'empty_address', 'pls set addresses array' );

		}
	}

	/**
	 * Получаем хосты из базы
	 * @return array
	 */
	public static function getOptions() {
		$options = get_option( Page::$slug, [
			'key'  => '',
			'salt' => '',
			'host' => '',
		] );

		if ( isset( $options['host'] ) && ! empty( $options['host'] ) ) {
			$hosts = explode( ',', $options['host'] );
			$hosts = array_map( 'trim', $hosts );

			return $hosts;
		}

		return [];
	}

	/**
	 * Получение адреса из списка
	 *
	 * @return mixed
	 */
	public function getAddress() {

        $saltHex = preg_replace("#[^a-f\d]#i", '', $this->salt);

        $number = (int)substr(hexdec($saltHex), 0, 1);

		$count = count( $this->addressList );

		$index = $this->residue( $number, $count );

		$index = $index - 1;

		return ( isset( $this->addressList[ $index ] ) ? $this->addressList[ $index ] : $this->addressList[0] );
	}

	/**
     * Определение офсета учитывая рамки допустимо  возмодных вариантов
	 *
	 * @param $number
	 * @param $limit
	 *
	 * @return mixed
	 */
	private function residue( $number, $limit ) {
		if ( $number > $limit ) {
			return $this->residue( $number - $limit, $limit );
		}

		return $number;
	}
}

