<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Laravel 框架介紹',
                'content' => 'Laravel 是一個優雅的 PHP Web 應用程式框架，提供了豐富的功能和工具來加速開發。它遵循 MVC 架構模式，讓程式碼更加組織化和易於維護。',
                'author' => '張小明',
                'is_published' => true,
                'view_count' => 150
            ],
            [
                'title' => 'RESTful API 設計原則',
                'content' => 'RESTful API 是一種軟體架構風格，它使用 HTTP 協議的標準方法來操作資源。良好的 RESTful API 設計應該遵循統一介面、無狀態、可快取等原則。',
                'author' => '李小華',
                'is_published' => true,
                'view_count' => 89
            ],
            [
                'title' => '資料庫優化技巧',
                'content' => '資料庫優化是提升應用程式效能的重要環節。透過適當的索引設計、查詢優化、正規化等技巧，可以大幅提升資料庫的查詢效能和整體系統表現。',
                'author' => '王小美',
                'is_published' => false,
                'view_count' => 0
            ],
            [
                'title' => '前端框架比較：React vs Vue',
                'content' => 'React 和 Vue 都是目前最受歡迎的前端框架。React 由 Facebook 開發，強調函數式程式設計；Vue 則更注重易用性和漸進式採用。選擇哪個框架取決於專案需求和團隊技術棧。',
                'author' => '陳小強',
                'is_published' => true,
                'view_count' => 234
            ],
            [
                'title' => '微服務架構實戰',
                'content' => '微服務架構將大型應用程式拆分成多個小型、獨立的服務。每個服務都有自己的資料庫和業務邏輯，透過 API 進行通訊。這種架構提供了更好的可擴展性和維護性。',
                'author' => '林小芳',
                'is_published' => false,
                'view_count' => 12
            ]
        ];

        foreach ($articles as $article) {
            Article::create($article);
        }
    }
}
