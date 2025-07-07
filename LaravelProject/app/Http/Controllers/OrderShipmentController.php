<?php

namespace App\Http\Controllers;

use App\Events\OrderShipped;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrderShipmentController extends Controller
{
    /**
     * 出貨指定訂單，並觸發 OrderShipped 事件
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // 取得訂單
        $order = Order::findOrFail($request->order_id);

        // ...這裡可寫出貨邏輯...

        // 觸發事件，將訂單資料傳給 OrderShipped 事件
        OrderShipped::dispatch($order);

        return redirect('/orders');
    }
} 