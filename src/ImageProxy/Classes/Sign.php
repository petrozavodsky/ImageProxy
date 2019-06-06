<?php

namespace ImageProxy\Classes;

use WP_Error;

class Sign
{

    private $key;

    private $salt;

    private $signatureSize = 8;

    private $host = 'http://localhost:8080';

    public function __construct()
    {
        d(
            $this->builder(['extension'=>'webp'],'https://brodude.ru/wp-content/uploads/2019/06/5/brodude.ru_5.06.2019_374b4o1Uy29g4-612x496.jpg')
        );
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
            'host' => $this->host,
            'resize' => 'fill',
            'width' => 300,
            'height' => 300,
            'gravity' => 'no',
            'enlarge' => 1,
            'url' => $url,
            'extension' => 'png',
        ];

        $data = wp_parse_args($data, $default);

        $default['url'] = rtrim(strtr(base64_encode($default['url']), '+/', '-_'), '=');

        return "{$data['host']}/{$data['resize']}/{$data['width']}/{$data['height']}/{$data['gravity']}/{$data['enlarge']}/{$default['url']}.{$data['extension']}";
    }

}
