<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['auth' => 'ログイン情報が登録されていません。'])->withInput();
        }

        // ログイン確定
        $request->session()->regenerate();

        // just_logged_in フラグ（任意で継続）
        $request->session()->put('just_logged_in', true);
        \Log::info('LOGIN OK: set just_logged_in', ['flag' => session('just_logged_in')]);

        // 未認証なら verify ページへ
        if (! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // 認証済みなら intended へ（無ければ mylist）
        return redirect()->intended(route('mylist.index', ['tab' => 'mylist']));
    }
}
