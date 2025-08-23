<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <header class="header">
        <img class="logo" src="{{ asset('storage/logo.svg') }}" alt="COACHTECHのロゴ">
    </header>

    <main class="register-container">
        <h1 class="register-title">ログイン</h1>

        <form method="POST" action="{{ route('login') }}" class="register-form">
            @csrf

            @if ($errors->has('auth'))
            <div class="error">{{ $errors->first('auth') }}</div>
            @endif

            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"  autofocus>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" >
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror


            <button type="submit" class="register-button">ログインする</button>
        </form>

        <div class="login-link-wrapper">
            <a href="{{ route('register') }}" class="register-link">会員登録はこちら</a>
        </div>
    </main>
</body>
</html>
