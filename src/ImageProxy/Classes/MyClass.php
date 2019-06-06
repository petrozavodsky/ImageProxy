<?php

namespace ImageProxy\Classes;

use ImageProxy\Utils\Assets;
use WP_Error;

class MyClass
{

    private $key;

    private $salt;

    public function __construct()
    {

    }


    public function sign($data)
    {
        $default = [
            'resize' => 'fill',
            'width' => 300,
            'height' => 300,
            'gravity' => 'no',
            'enlarge' => 1,
            'extension' => 'png',
        ];

        $data = wp_parse_args($data, $default);


        $signatureSize = 8;
        $keyBin = pack("H*", $this->key);
        if (empty($keyBin)) {

            return new WP_Error('error', 'Key expected to be hex-encoded string');
        }

        $saltBin = pack("H*", $this->salt);
        if (empty($saltBin)) {

            return new WP_Error('error', 'Salt expected to be hex-encoded string');
        }

        $url = 'http://img.example.com/pretty/image.jpg';
        $encodedUrl = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');

        $path = "/{$data['resize']}/{$data['width']}/{$data['height']}/{$data['gravity']}/{$data['enlarge']}/{$encodedUrl}.{$data['extension']}";

        $signature = hash_hmac('sha256', $saltBin . $path, $keyBin, true);
        $signature = pack('A' . $signatureSize, $signature);
        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        print(sprintf("/%s%s", $signature, $path));

    }
}
