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
	public $version = '1.0.1';
	public static $textdomine;

	public $elements = [];

	public function __construct() {
		self::$textdomine = $this->setTextdomain();
	}

	public function addPage() {
		$this->elements['Page'] = new Page();
	}

	public function active() {

		if ( ! empty( Page::getOption( 'active' ) ) ) {
			$reformer = new Reformer();
			$reformer->init();
			$this->elements['Reformer'] = $reformer;

			$this->pluginsCompat();
		}
	}

	private function pluginsCompat() {
		$this->elements['CompatYoastSeo'] = new YoastSeo();
	}
}

function ImageProxy__init() {

	global $ImageProxy__var;

	$plugin = new ImageProxy();
	$plugin->addPage();
	$plugin->active();

	$ImageProxy__var = $plugin->elements;
}

add_action( 'plugins_loaded', 'ImageProxy__init', 30 );
