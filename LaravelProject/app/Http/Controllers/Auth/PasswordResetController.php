<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * 顯示忘記密碼表單
     */
    public function showForgotForm()
    {
        return view('auth.forgot_password');
    }

    /**
     * 處理忘記密碼表單送出（寄送重設信）
     */
    public function sendResetLink(Request $request)
    {
        // 驗證 email 欄位
        $request->validate(['email' => 'required|email']);
        // 寄送密碼重設信件
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * 顯示密碼重設表單（帶 token）
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset_password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * 處理密碼重設表單送出
     */
    public function reset(Request $request)
    {
        // 驗證表單欄位
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        // 執行密碼重設
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
                // 可選：自動登入
                // Auth::login($user);
            }
        );
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
} 