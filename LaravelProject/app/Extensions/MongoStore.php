<?php
// 路徑：app/Extensions/MongoStore.php
namespace App\Extensions;

use Illuminate\Contracts\Cache\Store;

class MongoStore implements Store
{
    // 取得單一 key
    public function get($key) {
        // TODO: 實作 MongoDB 查詢
    }
    // 取得多個 key
    public function many(array $keys) {
        // TODO: 實作 MongoDB 查詢
    }
    // 寫入快取
    public function put($key, $value, $seconds) {
        // TODO: 實作 MongoDB 寫入
    }
    // 批次寫入
    public function putMany(array $values, $seconds) {
        // TODO: 實作 MongoDB 批次寫入
    }
    // 自增
    public function increment($key, $value = 1) {
        // TODO: 實作 MongoDB 自增
    }
    // 自減
    public function decrement($key, $value = 1) {
        // TODO: 實作 MongoDB 自減
    }
    // 永久寫入
    public function forever($key, $value) {
        // TODO: 實作 MongoDB 永久寫入
    }
    // 刪除快取
    public function forget($key) {
        // TODO: 實作 MongoDB 刪除
    }
    // 清空所有快取
    public function flush() {
        // TODO: 實作 MongoDB 清空
    }
    // 取得快取前綴字串
    public function getPrefix() {
        // TODO: 回傳前綴字串
    }
} 