<?php

namespace ImageProxy\Classes;

use ImageProxy\Utils\Ajax;
use ImageProxy\Utils\Assets;

class AjaxOut2 extends Ajax {

	use Assets;

	/**
	 * AjaxOut2 constructor.
	 */
	function __construct() {
		$name = "AjaxOut2";

		parent::__construct( $name );
		$this->add_js_css( $name );

	}

	/**
	 * @param $name
	 */
	private function add_js_css( $name ) {

		$handle = $this->addJs(
			$name,
			'header',
			[ 'jquery' ]
		);

		$this->varsAjax(
			$handle,
			[
				'ajax_url'        => $this->ajax_url,
				'ajax_url_action' => $this->ajax_url_action,
			]
		);
	}

	/**
	 * @param string $request
	 */
	public function callback( $request ) {
		unset( $request['action'] );
		var_dump( $request );
		die;

	}
}
