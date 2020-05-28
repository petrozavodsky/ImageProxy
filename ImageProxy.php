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

if (!defined('ABSPATH')) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . "includes/Autoloader.php");

if (file_exists(plugin_dir_path(__FILE__) . "vendor/autoload.php")) {
    require_once(plugin_dir_path(__FILE__) . "vendor/autoload.php");
}

use ImageProxy\Admin\Page;
use ImageProxy\Admin\TestImageBlock;
use ImageProxy\Autoloader;

new Autoloader(__FILE__, 'ImageProxy');

use ImageProxy\Base\Wrap;
use ImageProxy\Classes\Reformer;
use ImageProxy\Compatibility\YoastSeo;

class ImageProxy extends Wrap
{

    public $version = '1.0.1';

    public static $textdomine;

    private $handler = false;

    public $elements = [];

    public function __construct()
    {
        self::$textdomine = $this->setTextdomain();
    }

    public function misc()
    {

        add_filter('ImageProxy__convert-image-url', function ($url, $args = []) {

            if (!empty(Page::getOption('active'))) {

                if (empty($this->handler)) {
                    $this->handler = new ImageProxy\Classes\Handler();
                }

                return $this->handler->convert($url, $args);
            }

            return $url;
        }, 10, 2);

    }

    public function addPage()
    {
        $this->elements['Page'] = new Page();
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'settingsLink']);

        if (apply_filters('ImageProxy__test-image-block-enable', false)) {
            $testImage = new TestImageBlock();
            $testImage->init();
        };
    }

    public function settingsLink($links)
    {
        $linkText = __('Settings', 'ImageProxy');
        $url = esc_url(admin_url('admin.php?page=' . Page::$slug));
        $links['settings'] = "<a href='{$url}'>{$linkText}</a>";

        return $links;
    }

    public function active()
    {

        if (!empty(Page::getOption('active')) && apply_filters('ImageProxy__converter-enable', true)) {
            $reformer = new Reformer();
            $reformer->init();
            $this->elements['Reformer'] = $reformer;

            $this->pluginsCompat();
        }
    }

    private function pluginsCompat()
    {
        $this->elements['CompatYoastSeo'] = new YoastSeo();
    }
}

function ImageProxy__init()
{

    global $ImageProxy__var;

    $plugin = new ImageProxy();
    $plugin->addPage();
    $plugin->active();
    $plugin->misc();

    $ImageProxy__var = $plugin->elements;
}

add_action('plugins_loaded', 'ImageProxy__init', 30);
