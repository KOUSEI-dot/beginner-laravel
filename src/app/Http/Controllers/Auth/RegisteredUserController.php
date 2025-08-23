<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            // 初期状態は未完了
            'profile_completed'=> false,
        ]);

        // 認証メール送信用イベント
        event(new Registered($user));

        // verify画面は auth 必須のためログインさせる
        Auth::login($user);

        // 登録直後は必ず認証案内へ
        return redirect()->route('verification.notice');
    }
}
