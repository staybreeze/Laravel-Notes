<?php

use App\Events\OrderShipped;
use App\Events\OrderFailedToShip;
use App\Events\OrderCreated;
use App\Listeners\SendShipmentNotification;
use App\Models\Order;
use Illuminate\Support\Facades\Event;

test('orders can be shipped', function () {
    Event::fake();

    // 模擬出貨流程...
    OrderShipped::dispatch(Order::factory()->make());
    OrderShipped::dispatch(Order::factory()->make());

    // 斷言事件有被 dispatch
    Event::assertDispatched(OrderShipped::class);

    // 斷言事件被 dispatch 兩次
    Event::assertDispatched(OrderShipped::class, 2);

    // 斷言某事件沒有被 dispatch
    Event::assertNotDispatched(OrderFailedToShip::class);

    // 斷言完全沒有事件被 dispatch（這裡會失敗，僅示範用法）
    // Event::assertNothingDispatched();
});

test('assert dispatched with closure', function () {
    Event::fake();
    $order = Order::factory()->make(['id' => 123]);
    OrderShipped::dispatch($order);
    Event::assertDispatched(function (OrderShipped $event) use ($order) {
        return $event->order->id === $order->id;
    });
});

test('assert listener is listening', function () {
    Event::assertListening(
        OrderShipped::class,
        SendShipmentNotification::class
    );
});

test('fake 部分事件', function () {
    Event::fake([
        OrderCreated::class,
    ]);
    $order = Order::factory()->create();
    Event::assertDispatched(OrderCreated::class);
    // 其他事件會正常 dispatch
    $order->update([
        // ...
    ]);
});

test('except 某些事件', function () {
    Event::fake()->except([
        OrderCreated::class,
    ]);
    // OrderCreated 事件會正常 dispatch，其他都 fake
});

test('fakeFor 區塊範圍', function () {
    $order = Event::fakeFor(function () {
        $order = Order::factory()->create();
        Event::assertDispatched(OrderCreated::class);
        return $order;
    });
    // 區塊外事件會正常 dispatch
    $order->update([
        // ...
    ]);
}); 