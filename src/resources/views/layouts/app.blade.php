<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- CSSの読み込み --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    {{-- ヘッダー --}}
    <header>
        <div class="logo">
        <img src="{{ asset('storage/logo.svg') }}" alt="COACHTECHロゴ" style="height: 30px; margin-right: 10px;">
        </div>

        <form method="GET" action="{{ route('mylist.index') }}" class="search-form">
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="なにをお探しですか？">
            <input type="hidden" name="tab" value="{{ request('tab', 'recommend') }}"> {{-- ←ここでタブ状態保持 --}}
            <button type="submit">検索</button>
        </form>


        <div class="nav-links">
            <a href="{{ route('mypage') }}">マイページ</a>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            @endauth


            <a href="{{ route('sell') }}" class="sell-btn">出品</a>
        </div>
    </header>

    {{-- 各ページの内容 --}}
    <main>
        @yield('content')
    </main>
</body>
</html>
