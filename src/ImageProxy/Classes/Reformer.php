<?php

namespace ImageProxy\Classes;


class Reformer
{
    private $proxy;

    public function __construct()
    {
        $this->proxy = new Builder();

//        d(
//            $this->proxy->builder(
//                [
//                    'width' => 300
//                ],
//                'https://brodude.ru/wp-content/uploads/2020/05/14/brodude.ru_14.05.2019_WuEJ9FOiNcGom.png'
//            )
//        );

        add_filter('wp_get_attachment_image_src', [$this, 'src'], 10, 3);
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

        d($image[0]);
        return $image;
    }


}
