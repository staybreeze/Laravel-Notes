<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class UserEventSubscriber
{
    /**
     * 處理使用者登入事件
     */
    public function handleUserLogin(Login $event): void
    {
        // 這裡可以寫登入後的邏輯
    }

    /**
     * 處理使用者登出事件
     */
    public function handleUserLogout(Logout $event): void
    {
        // 這裡可以寫登出後的邏輯
    }

    /**
     * 註冊訂閱的事件與 handler
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events): void
    {
        // 註冊登入事件
        $events->listen(
            Login::class,
            [UserEventSubscriber::class, 'handleUserLogin']
        );

        // 註冊登出事件
        $events->listen(
            Logout::class,
            [UserEventSubscriber::class, 'handleUserLogout']
        );
    }
} 