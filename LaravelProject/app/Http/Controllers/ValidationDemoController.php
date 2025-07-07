<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StorePostRequest;

class ValidationDemoController extends Controller
{
    /**
     * 顯示表單頁面
     */
    public function create(): View
    {
        // 回傳 demo 表單 view
        return view('demo.validation.create');
    }

    /**
     * =========================
     * 舊寫法（直接在 Controller 內驗證）
     * =========================
     */
    /*
    public function store(Request $request): RedirectResponse
    {
        // 直接在 Controller 內驗證
        $validated = $request->validate([
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ]);
        // 這裡可進行資料儲存...
        return redirect()->route('demo.validation.create')->with('success', 'Controller 內驗證通過！');
    }
    */

    /**
     * =========================
     * 新寫法（使用 Form Request）
     * =========================
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        // ========================================
        // Form Request 使用說明
        // ========================================
        
        // 1. 驗證已經在 StorePostRequest 中自動完成
        // 2. 如果驗證失敗，會自動重導回前頁並顯示錯誤訊息
        // 3. 如果驗證成功，才會執行到這裡
        
        // ========================================
        // 取得驗證過的資料（三種方式）
        // ========================================
        
        // 方式一：取得所有驗證通過的資料（陣列格式）
        $validated = $request->validated();
        // 結果：['title' => '文章標題', 'body' => '文章內容']
        
        // 方式二：只取部分欄位
        $only = $request->safe()->only(['title', 'body']);
        // 結果：只包含 title 和 body，排除其他欄位
        
        // 方式三：排除部分欄位
        $except = $request->safe()->except(['slug']);
        // 結果：包含除了 slug 以外的所有欄位
        
        // ========================================
        // 為什麼這個範例比較簡單？
        // ========================================
        
        // 1. StorePostRequest 使用預設設定：
        //    - authorize() 預設回傳 true（允許所有請求）
        //    - messages() 預設使用語系檔錯誤訊息
        //    - attributes() 預設使用語系檔欄位名稱
        //    - 沒有 prepareForValidation() 和 after()
        
        // 2. 實際專案中會這樣寫：
        //    - 自訂 authorize() 檢查權限
        //    - 自訂 messages() 提供特殊錯誤訊息
        //    - 自訂 attributes() 提供中文欄位名稱
        //    - 加入 prepareForValidation() 預處理資料
        //    - 加入 after() 複雜驗證邏輯
        
        // ========================================
        // 實際使用範例
        // ========================================
        
        // 這裡可以進行資料儲存...
        // Post::create($validated);
        // 或者
        // Post::create($only->toArray());
        
        return redirect()->route('demo.validation.create')->with('success', 'Form Request 驗證通過！');
    }
} 