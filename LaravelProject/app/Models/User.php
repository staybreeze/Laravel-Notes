<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// 這行引用 Laravel 內建的 User 基底類別
// Authenticatable 提供認證、登入、密碼等功能
// 通常 User 模型會繼承這個類別，才能用 Laravel 的 Auth 系統
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// User 模型繼承 Authenticatable，代表這個模型具備 Laravel 認證、登入、密碼等功能
// 這是 Laravel Auth 系統運作的基礎，讓 User 可以被當作可認證的使用者
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * 【重要】此方法名稱必須固定為 resolveRouteBinding，Laravel 路由模型綁定時才會自動呼叫。
     * 若需自訂路由參數的查詢邏輯，請覆寫本方法。
     * 其他自訂 function 名稱 Laravel 不會自動執行。
     *
     * 自訂路由綁定解析邏輯：用 name 欄位查找 User
     *
     * @param  mixed  $value  路由參數值
     * @param  string|null  $field  欄位名稱（Laravel 8+ 支援）
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('name', $value)->firstOrFail();
    }

    /**
     * 【重要】此方法名稱必須固定為 resolveChildRouteBinding，Laravel 巢狀路由模型綁定時才會自動呼叫。
     * 若需自訂巢狀模型的解析邏輯，請覆寫本方法。
     * 其他 function 名稱 Laravel 不會自動執行。
     *
     * 巢狀綁定時的子模型解析邏輯，可依需求自訂
     *
     * @param  string  $childType  子模型類型
     * @param  mixed  $value  路由參數值
     * @param  string|null  $field  欄位名稱
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return parent::resolveChildRouteBinding($childType, $value, $field);
    }

    /**
     * 判斷是否為管理員
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * 判斷是否可更新指定分類
     */
    public function canUpdateCategory(int $categoryId): bool
    {
        // 範例：假設管理員或 categoryId=1 可更新
        return $this->isAdmin() || $categoryId === 1;
    }
}
