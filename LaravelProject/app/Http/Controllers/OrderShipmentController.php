<?php

namespace App\Http\Controllers;

use App\Events\OrderShipped;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Mail\OrderShipped as OrderShippedMail;
use Illuminate\Support\Facades\Mail;

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

        // 1. 基本寄信：寄給下單會員（假設 $request->user() 為會員）
        Mail::to($request->user())->send(new OrderShippedMail($order));

        // 2. cc/bcc 寄送（假設 $ccUsers, $bccUsers 已取得）
        // Mail::to($request->user())
        //     ->cc($ccUsers)
        //     ->bcc($bccUsers)
        //     ->send(new OrderShippedMail($order));

        // 3. 佇列寄信（背景寄送）
        // Mail::to($request->user())->queue(new OrderShippedMail($order));

        // 4. 多語系寄信（如寄送日文）
        // Mail::to($request->user())->locale('ja')->send(new OrderShippedMail($order));

        // 5. afterCommit 寄信（確保交易完成後才寄送）
        // Mail::to($request->user())->send((new OrderShippedMail($order))->afterCommit());

        // 6. 觸發事件，將訂單資料傳給 OrderShipped 事件
        OrderShipped::dispatch($order);

        return redirect('/orders');
    }
} 