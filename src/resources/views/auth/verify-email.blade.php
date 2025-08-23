<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証</title>
    <link rel="stylesheet" href="{{ asset('css/verify.css') }}">
</head>
<body>
    {{-- ヘッダー --}}
    <header class="header">
        <img class="logo" src="{{ asset('storage/logo.svg') }}" alt="COACHTECHのロゴ">
    </header>

    {{-- メインコンテンツ --}}
    <div class="verify-wrap">
        @if (session('status') === 'verification-link-sent')
            <div class="verify-alert" role="status">
                認証リンクを再送しました。メールをご確認ください。
            </div>
        @endif

        <div class="verify-card">
            <p class="verify-text">
                登録していただいたメールアドレスに認証メールを送りました。<br>
                メール認証を完了してください。
            </p>

            {{-- 大きいボタン --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-primary">認証はこちらから</button>
            </form>

            {{-- 小リンク --}}
            <form id="resendForm" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resend-link">認証メールを再送する</button>
            </form>
        </div>
    </div>
</body>
</html>
