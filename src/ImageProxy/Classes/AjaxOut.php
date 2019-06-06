<?php

namespace ImageProxy\Classes;

use ImageProxy\Utils\Ajax;
use ImageProxy\Utils\Assets;

class AjaxOut extends Ajax {

	use Assets;

	public function init( $action_name ) {
		$this->add_js_css( $action_name );

	}

	public function callback( $request ) {
		$json = [ "out" => "AJAX Content" ];
		wp_send_json( $json );
	}

	private function add_js_css( $action ) {
		$handle = $this->addJs(
			$action,
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
}
