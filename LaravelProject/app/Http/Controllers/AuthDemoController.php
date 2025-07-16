<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthDemoController extends Controller
{
    /**
     * 顯示登入表單（範例）
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * 嘗試登入：驗證帳號密碼，若成功才建立登入狀態
     * 這裡的『嘗試登入』是指 Auth::attempt()，會回傳布林值，
     * 成功才會真的登入（建立 session/cookie），失敗則不會登入。
     * 『登入』是指已經通過驗證，系統已記住用戶（可用 Auth::check() 判斷）。
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        // 嘗試登入：驗證帳密，成功才建立登入狀態
        if (Auth::attempt($credentials)) {
            // 嘗試登入成功，使用者已登入
            return redirect()->intended('dashboard');
        }
        // 嘗試登入失敗，回傳錯誤訊息
        return back()->withErrors([
            'email' => '帳號或密碼錯誤，請重新輸入。',
        ]);
    }

    /**
     * 登出
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * 取得目前登入用戶
     */
    public function currentUser()
    {
        return Auth::user();
    }

    /**
     * 密碼確認（敏感操作前）
     */
    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);
        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors([
                'password' => ['密碼錯誤']
            ]);
        }
        $request->session()->passwordConfirmed();
        return redirect()->intended();
    }

    /**
     * 登出其他裝置
     */
    public function logoutOtherDevices(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);
        Auth::logoutOtherDevices($request->password);
        return back()->with('status', '已登出其他裝置');
    }

    /**
     * HTTP Basic Auth 範例
     */
    public function basicAuthDemo()
    {
        // 路由需加 middleware('auth.basic')
        return '通過 HTTP Basic 認證';
    }
} 