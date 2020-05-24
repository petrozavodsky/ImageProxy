<?php

namespace ImageProxy\Compatibility;

use ImageProxy\Classes\Builder;
use ImageProxy\Classes\Reformer;
use ImageProxy\Classes\SelectCdnAddress;

class YoastSeo
{

    private $proxy;
    private $siteUrl = false;

    public function __construct()
    {
        $this->siteUrl = apply_filters('ImageProxy__site-host', false);

        $this->proxy = new Builder();

        add_filter('wpseo_image_sizes', function ($array) {

            array_unshift($array, 'image_600x600', 'image_600x314');

            return $array;
        });

        add_filter('ImageProxy__image-default-virtual-sizes', function ($array) {

            $array['image_600x314'] = [
                'width' => 600,
                'height' => 314,
                'crop' => true,
            ];

            $array['image_600x600'] = [
                'width' => 600,
                'height' => 600,
                'crop' => true,
            ];

            return $array;
        });
        add_filter('wpseo_opengraph_is_valid_image_url', [$this, 'validImageUrl'], 10, 2);

        add_filter('wpseo_opengraph_image', function ($url) {

            if (Reformer::checkComplete($url)) {

                $hostUrl = parse_url($url, PHP_URL_HOST);
                $hostSite = parse_url(site_url('/'), PHP_URL_HOST);

                if ($hostUrl == $hostSite) {

                    $newUrl = preg_replace("#-(\d+x\d+)\.(png|jpeg|jpg|gif)$#i", '.${2}', $url);

                    return $this->removeOrigin(
                        $this->proxy->builder(
                        [
                            'rs' => [
                                'resizing_type' => 'fill',
                                'width' => 600,
                                'height' => 600,
                                'dpr' => '',
                                'enlarge' => 0,
                                'extend' => '',
                            ],
                            'ext' =>'jpg',
                        ],
                       $this->replaceHost( $newUrl )
                    ));

                }
            }

            return $this->removeOrigin($url);
        });
    }

    private function replaceHost($url)
    {

        if (empty($this->siteUrl)) {
            return $url;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        $newHost = parse_url($this->siteUrl, PHP_URL_HOST);
        $newScheme = parse_url($this->siteUrl, PHP_URL_SCHEME);

        return str_replace("{$scheme}://{$host}", "{$newScheme}://{$newHost}", $url);
    }

    private function removeOrigin($string)
    {
        return preg_replace("/\?origin=.*$/mi", '', $string);
    }

    public function validImageUrl($valid, $url)
    {

        $hosts = SelectCdnAddress::getOptions();

        if (empty($hosts)) {
            return false;
        }

        foreach ($hosts as $host) {
            if (false !== stristr($url, $host)) {
                return true;
            }
        }

        return $valid;
    }

}
