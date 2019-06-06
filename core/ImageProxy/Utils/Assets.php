<?php

namespace ImageProxy\Utils;

trait Assets {

	private $loginPage = false;

	private $defaults_vars = [
		'css_patch' => "public/css/",
		'js_patch'  => "public/js/",
		'version'   => "1.0.3",
		'min'       => true
	];

	public function __get( $name ) {

		if ( $name == 'base_name' ) {
			return $this->basenameHelper();
		}

		if ( $name == 'file' ) {
			return $this->pluginDir();
		}

		if ( $name == 'url' ) {
			return $this->url();
		}

		if ( array_key_exists( $name, $this->defaults_vars ) ) {
			return $this->defaults_vars[ $name ];
		}

		return null;
	}

	public function url() {
		$plugins    = trailingslashit( plugins_url() );
		$plugin     = plugin_dir_url( __FILE__ );
		$plugin     = preg_replace( "#/$#", "", $plugin );
		$path_array = str_replace( $plugins, '', $plugin );
		$array      = explode( '/', $path_array );
		$path       = array_shift( $array );

		return trailingslashit( $plugins.$path );
	}

	public function basenameHelper() {
		$array = explode( '\\', __NAMESPACE__ );
		$id    = array_shift( $array );

		return $id;
	}

	/**
	 * @return string
	 */
	public function pluginDir() {
		$string = plugin_basename( __FILE__ );
		$array  = explode( '/', $string );
		$path   = array_shift( $array );

		return WP_PLUGIN_DIR . '/' . $path . '/';
	}

	/**
	 * @param mixed string|bool $val
	 *
	 * @return string
	 */
	public function pluginUrl( $val = false ) {
		$string      = plugin_basename( __FILE__ );
		$array       = explode( '/', $string );
		$path        = array_shift( $array );
		$plugins_url = plugin_dir_url( WP_PLUGIN_DIR . '/' . $path . '/' );
		if ( ! $val ) {
			return $plugins_url . $path . "/";
		}

		return $plugins_url . $path . "/" . $val;
	}

	/**
	 * @param string $handle
	 * @param bool $in_footer
	 * @param array $dep
	 * @param bool|string $version
	 * @param bool|string $src
	 *
	 * @return string
	 */
	public function registerJs( $handle, $in_footer = false, $dep = [], $version = false, $src = false ) {
		$this->basenameHelper();
		if ( ! $src ) {
        		$min= ".min";

			if(( defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS === false) || $this->min === false  ){
				$min= '';
			}
			
			$src     = $this->pluginUrl( "{$this->js_patch}{$this->base_name}-{$handle}{$min}.js" );
			$file_id = $this->base_name . "-" . $handle;
		} else {
			$file_id = $handle;
		}
		if ( ! $version ) {
			$version = $this->version;
		}

		$hook = "wp_enqueue_scripts";

		if ( is_admin() ) {
			$hook = "admin_enqueue_scripts";
		}

		if($this->loginPage){
			$hook = 'login_enqueue_scripts';
		}

		add_action( $hook, function () use ( $in_footer, $version, $dep, $src, $file_id ) {
			wp_enqueue_script(
				$file_id,
				$src,
				$dep,
				$version,
				$in_footer
			);
		}, 10 );

		return $file_id;
	}

	/**
	 * @param string $handle
	 * @param string $position
	 * @param array $dep
	 * @param bool|string $version
	 * @param bool|string $src
	 *
	 * @return string
	 */
	public function addJs( $handle, $position = "wp_enqueue_scripts", $dep = [], $version = false, $src = false ) {
		$in_footer = false;
		if ( $position == "wp_footer" || $position == "footer" || $position == "body" ) {
			$position  = "wp_footer";
			$in_footer = true;
		} elseif ( $position == "wp_head" || $position == "wp_enqueue_script" || $position == "header" || $position == "head" ) {
			 $position = "wp_head";
		} elseif ($position == 'admin' || $position == 'admin_header'|| $position  ==  'admin_head'){
			 $position = 'admin_enqueue_scripts';
		} elseif ($position  ==  'login' || $position  == 'login-page'){
			 $position = 'login_enqueue_scripts';
     		 $this->loginPage = true;
		}

		$handle = $this->registerJs( $handle, $position, $dep, $version, $src );
		add_action( $position, function () use ( $in_footer, $handle, $src, $dep, $version ) {
			wp_enqueue_script( $handle, $src, $dep, $version, $in_footer );
		} );

		return $handle;
	}

	/**
	 * @param string $handle
	 * @param array $dep
	 * @param bool|string $version
	 * @param bool|string $src
	 * @param string|string $media
	 *
	 * @return string
	 */
	public function registerCss( $handle, $dep = [], $version = false, $src = false, $media = 'all' ) {
		if ( ! $src ) {
			$src     = $this->pluginUrl( "{$this->css_patch}{$this->base_name}-{$handle}.css" );
			$file_id = $this->base_name . "-" . $handle;
		} else {
			$file_id = $handle;
		}
		if ( ! $version ) {
			$version = $this->version;
		}

		$hook = "wp_enqueue_scripts";

		if ( is_admin() ) {
			$hook = "admin_enqueue_scripts";
		}

		if($this->loginPage){
			$hook = 'login_enqueue_scripts';
		}

		add_action( $hook, function () use ( $media, $version, $dep, $src, $file_id ) {
			wp_register_style(
				$file_id,
				$src,
				$dep,
				$version,
				$media
			);
		}, 10 );

		return $file_id;
	}

	/**
	 * @param string $handle
	 * @param string $position
	 * @param array $dep
	 * @param bool|string $version
	 * @param bool|string $src
	 * @param string $media
	 *
	 * @return string
	 */
	public function addCss( $handle, $position = "wp_enqueue_scripts", $dep = [], $version = false, $src = false, $media = 'all' ) {
		if ( $position == "wp_footer" || $position == "footer" || $position == "body" ) {
			$position = "wp_footer";
		} elseif ( $position == "wp_head" || $position == "wp_enqueue_script" || $position == "header" || $position == "head" ) {
			 $position = "wp_enqueue_scripts";
		} elseif ($position == 'admin' || $position == 'admin_header'|| $position == 'admin_head'){
			 $position = 'admin_enqueue_scripts';
		} elseif ($position == 'login' || $position == 'login-page'){
			$position = 'login_enqueue_scripts';
			$this->loginPage = true;
		}

		$handle = $this->registerCss( $handle, $dep, $version, $src, $media );
		add_action( $position, function () use (  $handle ) {
			wp_enqueue_style( $handle );
		});

		return $handle;
	}

}
