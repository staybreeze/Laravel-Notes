<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Money\Money;

class LocalizationDemoController extends Controller
{
    public function show()
    {
        // 動態切換語系（例如根據使用者選擇）
        \App::setLocale('zh-TW');

        // 物件參數格式化
        $money = new Money(1000, 'TWD');
        $price = __('messages.price', ['amount' => $money]);

        // __toString() 物件參數
        $product = new Product('iPhone 15 Pro');
        $productInfo = __('messages.product_info', ['product' => $product]);

        return view('localization_demo', compact('price', 'productInfo'));
    }
} 