<?php

namespace ImageProxy\Classes;

use WP_Error;

class Sign
{

    private $key;

    private $salt;

    private $signatureSize = 8;

    public function __construct()
    {

    }


    public function sign($path)
    {
        $signatureSize = $this->signatureSize;

        $keyBin = pack("H*", $this->key);
        if (empty($keyBin)) {

            return new WP_Error('error', 'Key expected to be hex-encoded string');
        }

        $saltBin = pack("H*", $this->salt);

        if (empty($saltBin)) {

            return new WP_Error('error', 'Salt expected to be hex-encoded string');
        }

        $signature = hash_hmac('sha256', $saltBin . $path, $keyBin, true);

        $signature = pack('A' . $signatureSize, $signature);
        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return sprintf("/%s%s", $signature, $path);

    }

    public function builder($data, $url)
    {
        $default = [
            'resize' => 'fill',
            'width' => 300,
            'height' => 300,
            'gravity' => 'no',
            'enlarge' => 1,
            'extension' => 'png',
            'url' => $url
        ];

        $data = wp_parse_args($data, $default);

        $url = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');

        return "/{$data['resize']}/{$data['width']}/{$data['height']}/{$data['gravity']}/{$data['enlarge']}/{$url}.{$data['extension']}";

    }

}
