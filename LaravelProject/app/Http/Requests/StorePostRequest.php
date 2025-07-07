<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Str;

class StorePostRequest extends FormRequest
{
    /**
     * =========================
     * 舊寫法（僅定義 rules/authorize）
     * =========================
     */
    /*
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ];
    }
    */

    /**
     * =========================
     * 新寫法（完整 Form Request 實作）
     * =========================
     */

    /**
     * 是否遇到第一個錯誤就停止所有驗證
     * @var bool
     */
    protected $stopOnFirstFailure = false; // 可設 true 示範

    /**
     * 驗證失敗時自訂導向路徑
     * @var string|null
     */
    // protected $redirect = '/dashboard';
    // protected $redirectRoute = 'dashboard';

    /**
     * 是否授權此請求
     * 可依需求注入依賴、存取 route/model/user
     */
    public function authorize(): bool
    {
        // 範例：檢查是否有權限編輯特定資源
        // $comment = Comment::find($this->route('comment'));
        // return $comment && $this->user()->can('update', $comment);
        return true; // 預設允許
    }

    /**
     * 驗證規則
     * 可注入依賴
     */
    public function rules(): array
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
            'slug' => 'nullable|string',
        ];
    }

    /**
     * 自訂錯誤訊息
     */
    public function messages(): array
    {
        return [
            'title.required' => '標題必填',
            'title.unique' => '標題已存在',
            'title.max' => '標題最多 255 字',
            'body.required' => '內容必填',
        ];
    }

    /**
     * 自訂欄位名稱
     */
    public function attributes(): array
    {
        return [
            'title' => '文章標題',
            'body' => '文章內容',
        ];
    }

    /**
     * 進階：驗證後額外檢查
     * 可回傳 closure 或 invokable class
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('title') === 'forbidden') {
                    $validator->errors()->add('title', '標題不能為 forbidden');
                }
            }
            // 也可加入 new ValidateUserStatus, new ValidateShippingTime 等 invokable class
        ];
    }

    /**
     * 驗證前預處理資料
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('title', '')),
        ]);
    }

    /**
     * 驗證通過後處理
     */
    protected function passedValidation(): void
    {
        // 例如：自動覆寫某欄位
        // $this->replace(['title' => '已驗證']);
    }
} 