<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
}
