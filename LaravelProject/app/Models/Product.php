<?php

namespace App\Models;

class Product
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    // 讓 Product 物件可以自動轉成字串
    public function __toString()
    {
        return $this->name . '（超人氣商品）';
    }
} 