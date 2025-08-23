@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/item-detail.css') }}">

<div class="item-detail-container">
    {{-- 左：商品画像 --}}
    <div class="detail-left">
        <img src="{{ $item['img_url'] }}" alt="{{ $item['name'] }}" class="detail-image">
    </div>

    {{-- 右：商品詳細 --}}
    <div class="item-info">
        <div class="item-header">
            <h2>{{ $item['name'] }}</h2>
            <p>{{ $item['brand'] ?? '－' }}</p>
            <p class="price">¥{{ number_format($item['price']) }} <span>（税込）</span></p>

            {{-- アイコン表示（いいね・コメント横並び） --}}
            <div class="icons-horizontal">
                {{-- いいね --}}
                <form method="POST" action="{{ route('item.like', ['item_id' => $item['id']]) }}">
                    @csrf
                    <div class="icon-group" data-liked="{{ !empty($item['liked_by_me']) ? 1 : 0 }}">
                        <button type="submit" class="icon-button">
                            <img src="{{ asset('storage/star-icon.jpg') }}" alt="like" class="icon-image">
                        </button>
                        <span class="icon-count">{{ $item['likes_count'] ?? 0 }}</span>
                    </div>
                </form>

                {{-- コメント --}}
                <div class="icon-group">
                    <img src="{{ asset('storage/comment-icon.jpg') }}" alt="comment" class="icon-image">
                    <span class="icon-count">{{ $item['comments_count'] ?? 0 }}</span>
                </div>
            </div>

            {{-- 購入ボタン or SOLD --}}
            @if (!empty($item['is_purchased']) && $item['is_purchased'])
                <div class="sold-label">SOLD</div>
            @else
                <form method="GET" action="{{ route('purchase.show', ['item_id' => $item['id']]) }}">
                    <button type="submit" class="buy-btn full-width">購入手続きへ</button>
                </form>
            @endif
        </div>

        {{-- 商品説明 --}}
        <div class="item-description">
            <h3>商品説明</h3>
            <p>{{ $item['description'] }}</p>
        </div>

        {{-- 商品情報 --}}
        <div class="item-meta">
            <h3>商品の情報</h3>
            <p><strong>カテゴリー：</strong>
                @php
                    $cats = is_array($item['categories'] ?? null)
                        ? $item['categories']
                        : (json_decode($item['categories'] ?? '[]', true) ?: array_filter(array_map('trim', explode(',', (string)($item['categories'] ?? '')))));
                @endphp
                @forelse ($cats as $category)
                    <span class="category-tag gray-bg">{{ $category }}</span>
                @empty
                    <span class="category-tag gray-bg">－</span>
                @endforelse
            </p>
            <p><strong>商品の状態：</strong> {{ $item['condition'] ?? '－' }}</p>
        </div>

        {{-- コメント欄 --}}
        <div class="comment-section">
            <h3>コメント ({{ $item['comments_count'] ?? 0 }})</h3>

            @if (!empty($item['comments']))
                @foreach ($item['comments'] as $comment)
                    <div class="comment">
                        <div class="comment-user-icon">
                            <div class="icon-circle"></div>
                        </div>
                        <div class="comment-body gray-bg">
                            <strong>{{ $comment['user'] }}</strong>
                            <p>{{ $comment['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="comment">
                    <div class="comment-user-icon">
                        <div class="icon-circle"></div>
                    </div>
                    <div class="comment-body gray-bg">
                        <p>こちらにコメントが入ります。</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- コメント投稿フォーム --}}
        <div class="comment-form">
            <form method="POST" action="{{ route('items.comment', ['item_id' => $item['id']]) }}" novalidate>
                @csrf
                <label for="comment-textarea" class="comment-label">商品へのコメント</label>
                <textarea id="comment-textarea"
                        name="comment"
                        rows="4"
                        class="@error('comment') invalid @enderror"
                        aria-invalid="@error('comment') true @enderror"
                        aria-describedby="comment_error">{{ old('comment') }}</textarea>
                @error('comment')
                    <p id="comment_error" class="form-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="comment-btn full-width">コメントを送信する</button>
            </form>
        </div>
    </div>
</div>
@endsection
