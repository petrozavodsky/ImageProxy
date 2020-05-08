<?php

namespace ImageProxy\Compatibility;

use ImageProxy\Classes\Builder;
use ImageProxy\Classes\Reformer;
use ImageProxy\Classes\SelectCdnAddress;

class YoastSeo
{

    private $proxy;

    public function __construct()
    {
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

                    $newUrl = preg_replace("#-(\d+x\d+)\.(png|jpeg|jpg|gif)$#i", '${2}', $url);

                    return $this->proxy->builder(
                        [
                            //                            'rs' => [
                            //                                'resizing_type' => 'fill',
                            //                                'width' => 600,
                            //                                'height' => 600,
                            //                                'dpr' => '',
                            //                                'enlarge' => 0,
                            //                                'extend' => '',
                            //                            ],
                        ],
                        $newUrl
                    );

                }
            }

            return $url;
        });

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
