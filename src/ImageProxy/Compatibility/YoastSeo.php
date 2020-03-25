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

        add_filter('wpseo_opengraph_is_valid_image_url', [$this, 'validImageUrl'], 10, 2);

        add_filter('wpseo_opengraph_image', function ($url) {

            if (Reformer::checkComplete($url)) {

                $hostUrl = parse_url($url, PHP_URL_HOST);
                $hostSite = parse_url(site_url('/'), PHP_URL_HOST);

                if ($hostUrl == $hostSite) {

                    $newUrl = preg_replace("#(-\d+x\d+)(\.png|jpeg|jpg|gif)$#i", '${2}', $url);

                    return $this->proxy->builder(
                        [],
                        $newUrl
                    );

                }
            }

            return $url;
        });
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
