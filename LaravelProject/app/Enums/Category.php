<?php

namespace App\Enums;

// Category Enum 範例，適用於隱式 Enum 綁定
// 用於路由參數自動驗證與型別提示

enum Category: string
{
    case Fruits = 'fruits';
    case People = 'people';
} 