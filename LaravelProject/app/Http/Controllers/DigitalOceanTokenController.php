<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DigitalOceanTokenController extends Controller
{
    /**
     * 儲存 DigitalOcean API token（加密後存入資料庫）
     */
    public function store(Request $request): RedirectResponse
    {
        // - fill() 是 Eloquent 的批量賦值方法，可一次性設定多個欄位的值（如 token）
        // - fill() 只會改物件屬性，不會自動存進資料庫，需搭配 save() 才會寫入
        // - 只有 $fillable 屬性中列出的欄位才能被 fill，防止批量賦值漏洞
        $request->user()->fill([
            'token' => Crypt::encryptString($request->token),
        ])->save();

        return redirect('/secrets');
    }

    /**
     * 解密並顯示 token 範例
     */
    public function show(Request $request): string
    {
        $encrypted = $request->user()->token;
        try {
            $decrypted = Crypt::decryptString($encrypted);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return '解密失敗';
        }
        return 'Token: ' . $decrypted;
    }
} 