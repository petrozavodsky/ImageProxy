<?php

namespace ImageProxy\Classes;


class Reformer
{
    private $proxy;

    public function __construct()
    {
        $this->proxy = new Builder();

//        add_filter('wp_get_attachment_image_src', [$this, 'src'], 10, 3);

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


}
