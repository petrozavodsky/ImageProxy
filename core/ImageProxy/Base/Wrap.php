<?php

namespace ImageProxy\Base;

class Wrap {

	public $version = '1.0.0';
	public $cssPatch = "public/css/";
	public $jsPatch = "public/js/";

	private $defaultsVars = [
		'cssPatch' => "public/css/",
		'jsPatch'  => "public/js/",
		'version'   => "1.0.3",
		'min'       => true
	];

	/**
	 * @param $name
	 *
	 * @return mixed|null|string
	 */
	public function __get( $name ) {

		if ( $name == 'baseName' ) {
			return $this->basenameHelper();
		}

		if ( $name == 'space' ) {
			return $this->basenameHelper();
		}

		if ( $name == 'prefix' ) {
			return '_' . $this->basenameHelper();

		}

		if ( $name == 'pluginPath' ) {
			return $this->pluginDir();
		}

		if ( $name == 'pluginUrl' ) {
			return $this->url();
		}

		if ( $name == 'plugin_text_domain' ) {
			return $this->getTextDomain();
		}

		if ( array_key_exists( $name, $this->defaultsVars ) ) {
			return $this->defaultsVars[ $name ];
		}

		return null;
	}

	/**
	 * @return string
	 */
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
	 * @return string
	 */
	public static function url() {
		$plugins    = trailingslashit( plugins_url() );
		$plugin     = plugin_dir_url( __FILE__ );
		$plugin     = preg_replace( "#/$#", "", $plugin );
		$pathArray = str_replace( $plugins, '', $plugin );
		$array      = explode( '/', $pathArray );
		$path       = array_shift( $array );

		return trailingslashit( $plugins . $path );
	}


	/**
	 * @param string $val
	 *
	 * @return $this
	 */
	public function setVersion( $val ) {
		$this->version = $val;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTextDomain() {
		$string = plugin_basename( __FILE__ );
		$array  = explode( '/', $string );
		$path   = array_shift( $array );

		return $path;
	}

	/**
	 *
	 * @return bool True on success, false on failure.
	 */
	public function setTextdomain( $domine = false ) {
		if ( ! $domine ) {
			$domine = $this->baseName;
		}

		load_textdomain( $domine, $this->pluginPath . "languages/{$this->pluginTextDomain}-" . get_locale() . '.mo' );

		return $domine;
	}


}
