<?php


namespace ImageProxy\Classes;


class Handler {

	private $proxy;

	public function __construct() {
		$this->proxy = new Builder();
	}

	/**
	 * @param string $url image url
	 * @param array $args proxy params
	 *
	 * @return string converted image url
	 */
	public function convert( $url, $args = [] ) {

		return $this->proxy->builder( $args, $url );
	}

}