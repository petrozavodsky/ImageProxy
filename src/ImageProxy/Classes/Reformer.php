<?php

namespace ImageProxy\Classes;


use DOMDocument;
use ImageProxy\Admin\Page;

class Reformer
{
    private $proxy;

    public function __construct()
    {

        $this->proxy = new Builder();

        add_filter('wp_get_attachment_image_src', [$this, 'src'], 10, 3);

        add_filter('the_content', [$this, 'postHtml']);

    }

    public function postHtml($html)
    {


        return $html;
    }

    public function src($image, $attachment_id, $size)
    {
        global $_wp_additional_image_sizes;


        $sizeMeta = $_wp_additional_image_sizes[$size];

        $image[0] = $this->proxy->builder(
            [
                'width' => $sizeMeta['width'],
                'height' => $sizeMeta['height']
            ],
            $image[0]
        );

        return $image;
    }


    public function regexSrc($str)
    {
        preg_match_all('~<img.*>~im', $str, $images);

        $array = [];

        foreach ($images[0] as $image) {
            $height = $this->getAttribute('height', $image);
            $width = $this->getAttribute('width', $image);
            $src = $this->getAttribute('src', $image);

            $array[$src] = $this->proxy->builder(
                [
                    'width' => $width,
                    'height' => $height
                ],
                $src
            );
        }

        return str_replace(array_keys($array), array_values($array), $str);
    }


    /**
     * Get html attribute by name
     * @param $str
     * @param $atr
     * @return mixed
     */
    public function getAttribute($atr, $str)
    {
        preg_match("~{$atr}=[\"|'](.*)[\"|']\s~imU", $str, $m);

        return $m[1];
    }

}
