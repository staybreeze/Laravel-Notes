<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ManualValidatorDemoController extends Controller
{
    /**
     * 顯示表單頁面
     */
    public function create(): View
    {
        // 回傳 demo 表單 view
        return view('demo.validator.create');
    }

    /**
     * 處理表單送出與手動驗證
     */
    public function store(Request $request): RedirectResponse
    {
        // 手動建立 Validator 實例
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ], [
            'title.required' => '標題必填',
            'title.unique' => '標題已存在',
            'title.max' => '標題最多 255 字',
            'body.required' => '內容必填',
        ], [
            'title' => '文章標題',
            'body' => '文章內容',
        ]);

        // 進階：after 方法可加額外驗證
        $validator->after(function ($validator) use ($request) {
            if ($request->input('title') === 'forbidden') {
                $validator->errors()->add('title', '標題不能為 forbidden');
            }
        });

        // 停止於第一個錯誤（可選）
        // $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            // 驗證失敗，重導回表單並帶錯誤訊息與輸入資料
            return redirect()->route('manual.validator.create')
                ->withErrors($validator)
                ->withInput();
        }

        // 取得所有驗證通過的資料
        $validated = $validator->validated();
        // 只取部分欄位
        $only = $validator->safe()->only(['title', 'body']);
        // 排除部分欄位
        $except = $validator->safe()->except(['slug']);

        // 這裡可進行資料儲存...
        return redirect()->route('manual.validator.create')->with('success', '手動 Validator 驗證通過！');
    }
} 