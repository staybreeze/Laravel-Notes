<?php

namespace App\Http\Controllers;

use App\Events\OrderShipmentStatusUpdated;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    public function updateShipmentStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->shipment_status = $request->input('status');
        $order->save();

        // 觸發事件，推播到前端
        OrderShipmentStatusUpdated::dispatch($order);

        return response()->json(['success' => true]);
    }
} 