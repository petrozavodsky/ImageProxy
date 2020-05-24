<?php

namespace ImageProxy\Classes;

use ImageProxy\Admin\Page;
use WP_Error;

class Builder
{
    private $options = [];

    private $key;

    private $salt;

    public function __construct()
    {
        $this->options = Page::getOptions();
        $this->key = $this->options['key'];
        $this->salt = $this->options['salt'];
    }

    public function __get($prop)
    {
        if (in_array($prop, ['key', 'key'])) {
            return $this->options[$prop];
        } elseif ('host' == $prop) {
            $hosts = $this->parseHostsOptions();

            return $hosts[0];
        }
    }

    private function getHostByImage($salt)
    {
        $object = new SelectCdnAddress($salt, $this->parseHostsOptions());

        return $object->getAddress();
    }

    /**
     * Возвращает доступные домены CDN в виде массива
     *
     * @return array|string
     */
    private function parseHostsOptions()
    {
        $host = $this->options['host'];

        if (false === strpos($host, ',')) {
            return [trim($host)];
        }

        $array = explode(',', $host);

        return array_map('trim', $array);
    }

    public function sign($path)
    {
        $keyBin = pack("H*", $this->key);
        if (empty($keyBin)) {

            return new WP_Error('error', 'Key expected to be hex-encoded string');
        }

        $saltBin = pack("H*", $this->salt);

        if (empty($saltBin)) {

            return new WP_Error('error', 'Salt expected to be hex-encoded string');
        }

        $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $saltBin . $path, $keyBin, true)), '+/', '-_'), '=');

        return sprintf("/%s%s", $signature, $path);
    }

    public function builderBasic($data, $url)
    {

        $default = [
            'resize' => 'fill',
            'width' => 0,
            'height' => 0,
            'gravity' => 'no',
            'enlarge' => 1,
        ];

        $data = wp_parse_args($data, $default);

        array_unshift($data, '');

        array_push($data, rtrim(strtr(base64_encode($url), '+/', '-_'), '='));

        $path = implode('/', $data);

        return $this->getHostByImage($url) . $this->sign($path);
    }

    public function builder($data, $url)
    {

        $basicType = apply_filters('ImageProxy__builder-type', false);

        if ($basicType) {
            return $this->builderBasic($data, $url);
        }

        $default = [
            'rs' => [
                'resizing_type' => 'fill',
                'width' => 0,
                'height' => 0,
                'dpr' => '',
                'enlarge' => 0,
                'extend' => '',
            ],
            'g' => [
                'gravity_type' => 'ce',
                'x_offset' => 0,
                'y_offset' => 0,
            ],
            'c' => [
                'height' => 0,
                'width' => 0,
            ],
            't' => '',
            'q' => '',
            'mb' => '',
            'bg' => '',
            'bl' => '',
            'wm' => [
                'opacity' => '',
                'position' => '',
                'x_offset' => '',
                'y_offset' => '',
                'scale' => '',
            ],
            'pr' => [],
            'cb' => '',
            'fn' => '',
            'plain' => '',
            'ext' => '',
        ];

        $data = wp_parse_args($data, $default);

        if (isset($data['resize']) && !empty($data['resize'])) {
            $data['rs']['resizing_type'] = $data['resize'];
            unset($data['resize']);
        }

        if (isset($data['width']) && !empty($data['width'])) {
            $data['rs']['width'] = $data['width'];
            unset($data['width']);
        }

        if (isset($data['height']) && !empty($data['height'])) {
            $data['rs']['height'] = $data['height'];
            unset($data['height']);
        }

        if (isset($data['gravity']) && !empty($data['gravity'])) {
            $data['g']['gravity_type'] = $data['gravity'];
            unset($data['gravity']);
        }

        if (isset($data['enlarge']) && !empty($data['enlarge'])) {
            $data['rs']['enlarge'] = ('no' == $data['enlarge']) ? 0 : $data['enlarge'];
            unset($data['enlarge']);
        }

        $extension = $data['ext'];
        unset($data['ext']);

        $array = [];
        foreach ($data as $k => $v) {
            if (!empty($v)) {

                if (is_array($v) && !empty(array_diff($v, ['']))) {
                    $array[] = "{$k}:" . implode(':', array_diff($v, ['']));
                }

            }
        }

        if (!empty($data['cb'])) {
            $array[] = $data['cb'];
        }

        if (!empty($data['fn'])) {
            $array[] = $data['fn'];
        }

        if (!empty($data['plain'])) {

            if (!empty($extension)) {
                $url .= "@{$extension}";
            }

            $array[] = "plain/{$url}";

        } else {

            $url = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');

            if (!empty($extension)) {
                $url .= ".{$extension}";
            }

            $array[] = $url;
        }

        $query = "/" . implode('/', $array);

        return $this->getHostByImage($url) . $this->sign($query);
    }

}
