<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    /**
     * 可大量賦值的欄位
     */
    protected $fillable = [
        'title',
        'content', 
        'author',
        'is_published',
        'view_count'
    ];

    /**
     * 轉換為陣列時要隱藏的欄位
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * 轉換為陣列時要顯示的欄位（覆蓋 $hidden）
     */
    protected $visible = [
        'id',
        'title',
        'content',
        'author',
        'is_published',
        'view_count'
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'is_published' => 'boolean',
        'view_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 增加瀏覽次數
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * 發布文章
     */
    public function publish(): void
    {
        $this->update(['is_published' => true]);
    }

    /**
     * 取消發布文章
     */
    public function unpublish(): void
    {
        $this->update(['is_published' => false]);
    }
}
