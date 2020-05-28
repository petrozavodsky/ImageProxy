<?php

namespace ImageProxy\Utils;

use ImageProxy\Base\Wrap;

trait Assets
{

    private $loginPage = false;

    private $defaultsVars = [
        'cssPatch' => "public/css/",
        'jsPatch' => "public/js/",
        'version' => "1.0.0",
        'min' => true
    ];

    public function __get($name)
    {

        if ($name == 'baseName') {
            return $this->basenameHelper();
        }

        if ($name == 'file') {
            return $this->pluginDir();
        }

        if ($name == 'url') {
            return $this->url();
        }

        if (array_key_exists($name, $this->defaultsVars)) {
            return $this->defaultsVars[$name];
        }

        return null;
    }

    public function url()
    {
        return Wrap::url();
    }

    public function basenameHelper()
    {
        $array = explode('\\', __NAMESPACE__);
        $id = array_shift($array);

        return $id;
    }

    /**
     * @return string
     */
    public function pluginDir()
    {
        $string = plugin_basename(__FILE__);
        $array = explode('/', $string);
        $path = array_shift($array);

        return WP_PLUGIN_DIR . '/' . $path . '/';
    }

    /**
     * @param mixed string|bool $val
     *
     * @return string
     */
    public function pluginUrl($val = false)
    {
        $string = plugin_basename(__FILE__);
        $array = explode('/', $string);
        $path = array_shift($array);
        $pluginsUrl = plugin_dir_url(WP_PLUGIN_DIR . '/' . $path . '/');
        if (!$val) {
            return $pluginsUrl . $path . "/";
        }

        return $pluginsUrl . $path . "/" . $val;
    }

    /**
     * @param string $handle
     * @param bool $inFooter
     * @param array $dep
     * @param bool|string $version
     * @param bool|string $src
     *
     * @return string
     */
    public function registerJs($handle, $inFooter = false, $dep = [], $version = false, $src = false)
    {

        if (!$src) {
            $min = ".min";

            if ((defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS === false) || $this->min === false) {
                $min = '';
            }

            $src = $this->pluginUrl("{$this->jsPatch}{$this->baseName}-{$handle}{$min}.js");
            $fileID = $this->baseName . "-" . $handle;
        } else {
            $fileID = $handle;
        }
        if (!$version) {
            $version = $this->version;
        }

        $hook = "wp_enqueue_scripts";

        if (is_admin()) {
            $hook = "admin_enqueue_scripts";
        }

        if ($this->loginPage) {
            $hook = 'login_enqueue_scripts';
        }

        add_action($hook, function () use ($inFooter, $version, $dep, $src, $fileID) {
            wp_register_script(
                $fileID,
                $src,
                $dep,
                $version,
                $inFooter
            );
        }, 9);

        return $fileID;
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
    public function addJs($handle, $position = "wp_enqueue_scripts", $dep = [], $version = false, $src = false)
    {
        $inFooter = false;
        if ($position == "wp_footer" || $position == "footer" || $position == "body") {
            $position = "wp_footer";
            $inFooter = true;
        } elseif ($position == "wp_head" || $position == "wp_enqueue_script" || $position == "header" || $position == "head") {
            $position = "wp_enqueue_scripts";
        } elseif ($position == 'admin' || $position == 'admin_header' || $position == 'admin_head') {
            $position = 'admin_enqueue_scripts';
        } elseif ($position == 'login' || $position == 'login-page') {
            $position = 'login_enqueue_scripts';
            $this->loginPage = true;
        }

        $handle = $this->registerJs($handle, $position, $dep, $version, $src);
        add_action($position, function () use ($inFooter, $handle, $src, $dep, $version) {
            wp_enqueue_script($handle, $src, $dep, $version, $inFooter);
        });

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
    public function registerCss($handle, $dep = [], $version = false, $src = false, $media = 'all')
    {
        if (!$src) {
            $src = $this->pluginUrl("{$this->cssPatch}{$this->baseName}-{$handle}.css");
            $fileID = $this->baseName . "-" . $handle;
        } else {
            $fileID = $handle;
        }
        if (!$version) {
            $version = $this->version;
        }

        $hook = "wp_enqueue_scripts";

        if (is_admin()) {
            $hook = "admin_enqueue_scripts";
        }

        if ($this->loginPage) {
            $hook = 'login_enqueue_scripts';
        }

        add_action($hook, function () use ($media, $version, $dep, $src, $fileID) {
            wp_register_style(
                $fileID,
                $src,
                $dep,
                $version,
                $media
            );
        },9);

        return $fileID;
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
    public function addCss($handle, $position = "wp_enqueue_scripts", $dep = [], $version = false, $src = false, $media = 'all')
    {
        if ($position == "wp_footer" || $position == "footer" || $position == "body") {
            $position = "wp_footer";
        } elseif ($position == "wp_head" || $position == "wp_enqueue_script" || $position == "header" || $position == "head") {
            $position = "wp_enqueue_scripts";
        } elseif ($position == 'admin' || $position == 'admin_header' || $position == 'admin_head') {
            $position = 'admin_enqueue_scripts';
        } elseif ($position == 'login' || $position == 'login-page') {
            $position = 'login_enqueue_scripts';
            $this->loginPage = true;
        }

        $handle = $this->registerCss($handle, $dep, $version, $src, $media);
        add_action($position, function () use ($handle) {
            wp_enqueue_style($handle);
        });

        return $handle;
    }

}
