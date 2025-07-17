<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * 更新使用者密碼（雜湊後儲存）
     */
    public function update(Request $request): RedirectResponse
    {
        // 這裡僅示範，實務應加上驗證
        $request->user()->fill([
            'password' => Hash::make($request->newPassword)
        ])->save();
        return redirect('/profile');
    }

    /**
     * 驗證明文密碼是否正確
     */
    public function check(Request $request): string
    {
        $plain = $request->input('plain');
        $hashed = $request->user()->password;
        if (Hash::check($plain, $hashed)) {
            return '密碼正確';
        }
        return '密碼錯誤';
    }

    /**
     * 判斷密碼是否需重新雜湊
     */
    public function needsRehash(Request $request): string
    {
        $hashed = $request->user()->password;
        if (Hash::needsRehash($hashed)) {
            return '需要重新雜湊';
        }
        return '不需重新雜湊';
    }
} 