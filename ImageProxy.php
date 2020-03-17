<?php
/*
Plugin Name: ImageProxy plugin
Plugin URI: https://alkoweb.ru
Author: Petrozavodsky
Author URI: https://alkoweb.ru
Text Domain: ImageProxy
Domain Path: /languages
Requires PHP: 7.0
Version: 1.0.3
License: GPLv3
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . "includes/Autoloader.php" );

if ( file_exists( plugin_dir_path( __FILE__ ) . "vendor/autoload.php" ) ) {
	require_once( plugin_dir_path( __FILE__ ) . "vendor/autoload.php" );
}

use ImageProxy\Admin\Page;
use ImageProxy\Autoloader;

new Autoloader( __FILE__, 'ImageProxy' );

use ImageProxy\Base\Wrap;
use ImageProxy\Classes\Reformer;
use ImageProxy\Compatibility\YoastSeo;

class ImageProxy extends Wrap {
	public $version = '1.0.3';
	public static $textdomine;

	public function __construct() {
		self::$textdomine = $this->setTextdomain();

		new Reformer();
		new Page();
		$this->pluginsCompat();
	}

	private function pluginsCompat() {
		new YoastSeo();
	}
}

function ImageProxy__init() {
	new ImageProxy();
}

add_action( 'plugins_loaded', 'ImageProxy__init', 30 );
