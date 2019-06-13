<?php
/**
 * User: vladimir rambo petrozavodsky
 * Date: 2019-06-13
 */

namespace ImageProxy\Classes;


use WP_Error;

class SelectCdnAddress
{
    private $salt;

    private $addressList = [];

    public function __construct($salt, $addressList)
    {
        $this->salt = $salt;
        $this->addressList = $addressList;

        if (empty($salt)) {
            new WP_Error('empty_salt', 'pls add salt');
        }

        if (empty($this->addressList)) {
            new WP_Error('empty_address', 'pls set addresses array');

        }
    }

    /**
     * Получение адреса из списка
     *
     * @return mixed
     */
    public function getAddress()
    {

        $number = (int)substr(hexdec($this->salt), 0, 1);

        $count = count($this->addressList);

        $index = $this->residue($number, $count);

        $index = $index - 1;

        return (isset($this->addressList[$index]) ? $this->addressList[$index] : $this->addressList[0]);
    }

    /**
     * Определение офсета учитывая рамки допустимо мозмодных вариантов
     *
     * @param $number
     * @param $limit
     * @return mixed
     */
    private function residue($number, $limit)
    {
        if ($number > $limit) {
            return $this->residue($number - $limit, $limit);
        }

        return $number;
    }
}

