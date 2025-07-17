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