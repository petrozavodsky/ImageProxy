<?php

namespace ImageProxy\Utils;

abstract class Ajax {

	protected $ajax_url;
	protected $ajax_url_action;


	/**
	 * Ajax constructor.
	 *
	 * @param string $action_name
	 * @param string $type
	 *
	 */
	public function __construct( $action_name, $type = 'front' ) {

		$this->ajax_url        = $this->createAjaxUrl();
		$this->ajax_url_action = $this->createAjaxUrlAction( $action_name );

		$this->$type( $action_name );

		if ( method_exists( $this, 'init' ) ) {
			$this->init( $action_name );
		}
	}

	/**
	 * @return string
	 */
	protected function createAjaxUrl() {
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * @param $action
	 *
	 * @return string
	 */
	protected function createAjaxUrlAction( $action ) {
		return add_query_arg( [ 'action' => $action ], $this->ajax_url );
	}

	/**
	 *
	 * @param string $handle
	 * @param array $data
	 *
	 */
	public function varsAjax( $handle, $data ) {

        $actions = [
            'login_enqueue_scripts',
            'wp_enqueue_scripts',
            'admin_enqueue_scripts'
        ];

        foreach ($actions as $action)

		add_action( $action, function () use ( $data, $handle ) {
			wp_localize_script(
				$handle,
				str_replace( '-', '_', $handle . "__vars" ),
				$data
			);
		}, 80 );

	}

	public function front( $action_name, $callback = 'payload_action' ) {
		add_action( 'wp_ajax_' . $action_name, [ $this, $callback]  );
		add_action( 'wp_ajax_nopriv_' . $action_name, [ $this, $callback ] );
	}


	public function admin( $action_name, $callback = 'payload_action' ) {
		add_action( 'wp_ajax_' . $action_name, [$this, $callback] );
	}

	public function payload() {
		$request = $_REQUEST;
		unset( $request['action'] );
		$this->callback( $request );
	}

	public function payloadAction() {
		$request = $_REQUEST;
		$this->callback( $request );
		die;
	}

	abstract public function callback( $request );

}
