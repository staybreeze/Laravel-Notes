<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

// - 本控制器沒有 use App\Models\User，但可以用 $request->user() 或 auth()->user()，
//   因為 Laravel Auth 系統會自動回傳目前登入的 User 實例（根據 config/auth.php 設定）。
// - 只有當你直接寫 User::create(...)、new User()、或 type-hint User 時，才需要 use App\Models\User。

class SecretController extends Controller
{
    // 儲存加密後的資料
    public function store(Request $request)
    {
        $encrypted = Crypt::encryptString($request->input('secret'));
        // 假設有一個 secrets 資料表
        $request->user()->secrets()->create([
            'content' => $encrypted,
        ]);
        return redirect()->back()->with('message', '已加密儲存！');
    }

    // 讀取並解密資料
    public function show($id)
    {
        $secret = auth()->user()->secrets()->findOrFail($id);
        try {
            $decrypted = Crypt::decryptString($secret->content);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(403, '資料已損毀或被竄改');
        }
        return view('secrets.show', ['secret' => $decrypted]);
    }
} 