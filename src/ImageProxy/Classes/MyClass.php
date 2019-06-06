<?php

namespace ImageProxy\Classes;

use ImageProxy\Utils\Assets;

class MyClass
{

    private $key;

    private $salt;

    public function __construct()
    {

    }


    public function sign(){

        $keyBin = pack("H*" , $this->key);
        if(empty($keyBin)) {
            die('Key expected to be hex-encoded string');
        }

        $saltBin = pack("H*" , $this->salt);
        if(empty($saltBin)) {
            die('Salt expected to be hex-encoded string');
        }

        $resize = 'fill';
        $width = 300;
        $height = 300;
        $gravity = 'no';
        $enlarge = 1;
        $extension = 'png';

        $url = 'http://img.example.com/pretty/image.jpg';
        $encodedUrl = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');

        $path = "/{$resize}/{$width}/{$height}/{$gravity}/{$enlarge}/{$encodedUrl}.{$extension}";

        $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $saltBin.$path, $keyBin, true)), '+/', '-_'), '=');

        print(sprintf("/%s%s", $signature, $path));

    }
}
