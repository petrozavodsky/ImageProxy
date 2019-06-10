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

//        add_filter('wp_get_attachment_image_src', [$this, 'src'], 10, 3);

        add_filter('the_content', [$this, 'postHtml']);

//        d(
//            $this->proxy->builder(
//                [
//                    'width' => 0,
//                    'height' => 0
//                ],
//               'https://brodude.ru/wp-content/uploads/2019/05/28/brodude.ru_28.05.2019_rWiqVagh0cRm7.gif'
//            )
//        );
    }

    public function postHtml($html)
    {
        $this->regexSrc($html);
        return $html;
    }

    public function src($image, $attachment_id, $size)
    {
        global $_wp_additional_image_sizes;

        if (1 == get_current_user_id()) {
            $sizeMeta = $_wp_additional_image_sizes[$size];

            $image[0] = $this->proxy->builder(
                [
                    'width' => $sizeMeta['width'],
                    'height' => $sizeMeta['height']
                ],
                $image[0]
            );
        }

        return $image;
    }


    public function regexSrc($str)
    {
        preg_match('~<img.*?src=?["|\']([^"]+)?["|\'].*?>~', $str, $m);

        $height = $this->getAttribute('height', $m[0]);


    }

    public function regexSrcset($str)
    {

        preg_match('<img.*?srcset="([^"]+)".*?>', $str, $m);

        $array = explode(',', $m[1]);

        foreach ($array as $val) {
            $url = preg_replace_callback(
                '#(.*)\s+?(\d+\w)#',
                function ($matches) {
                    return $matches[1];
                },
                $val
            );

            d($url);

        }

    }

    /**
     * Get html attribute by name
     * @param $str
     * @param $atr
     * @return mixed
     */
    public function getAttribute( $atr, $str)
    {
        preg_match("~{$atr}=?[\"|'](\w+?)?[\"|']~", $str, $m);
        d($m);

        return $m[1];
    }

}
