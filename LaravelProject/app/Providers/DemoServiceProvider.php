<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Transistor;
use App\Services\PodcastParser;
use App\Contracts\EventPusher;
use App\Services\RedisEventPusher;
use Illuminate\Contracts\Foundation\Application;

class DemoServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 基本綁定：每次都 new 新的
        $this->app->bind(Transistor::class, function (Application $app) {
            return new Transistor($app->make(PodcastParser::class));
        });

        // 單例綁定：全程只 new 一次
        $this->app->singleton(PodcastParser::class, function () {
            return new PodcastParser();
        });

        // 介面綁定實作
        $this->app->bind(EventPusher::class, RedisEventPusher::class);

        // Contextual Binding 範例
        $this->app->when('App\\Http\\Controllers\\DemoController')
            ->needs('App\\Services\\Transistor')
            ->give(function ($app) {
                return new Transistor(new PodcastParser());
            });

        // DemoService 綁定範例（
        if (!class_exists('App\\Services\\DemoService')) return;
        $this->app->singleton(\App\Services\DemoService::class, function ($app) {
            // 這裡可以注入依賴
            return new \App\Services\DemoService();
        });
    }

    public function boot()
    {
        // 可選：啟動時的初始化
    }
} 