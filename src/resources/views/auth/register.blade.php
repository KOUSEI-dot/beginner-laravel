<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>会員登録</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <header class="header">
        <img class="logo" src="{{ asset('storage/logo.svg') }}" alt="COACHTECHのロゴ">
    </header>

    <main class="register-container">
        <h1 class="register-title">会員登録</h1>

        <form method="POST" action="{{ route('register') }}" class="register-form">
            @csrf

            <label for="name">ユーザー名</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"  autofocus>
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" >
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" >
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password_confirmation">確認用パスワード</label>
            <input id="password_confirmation" type="password" name="password_confirmation" >
            {{-- password_confirmation は confirmed ルールと連携する --}}


            <button type="submit" class="register-button">登録する</button>
        </form>

        <div class="login-link-wrapper">
            <a href="{{ route('login') }}" class="login-link">ログインはこちら</a>
        </div>
    </main>
</body>
</html>
