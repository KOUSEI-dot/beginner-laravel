@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $imgSrc = Str::startsWith($item->img_url, ['http://','https://'])
        ? $item->img_url
        : Storage::url(ltrim($item->img_url, '/'));
@endphp

<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">

<div class="purchase-container">
    {{-- 左側 --}}
    <div class="purchase-left">
        {{-- 商品情報 --}}
        <div class="purchase-item">
            <img src="{{ $imgSrc }}" alt="商品画像" class="item-image">
            <div class="item-info">
                <h2 class="purchase-name">{{ $item->name }}</h2>
                <p class="purchase-price">¥ {{ number_format($item->price) }}</p>
            </div>
        </div>

        {{-- 支払い方法（JSで即時表示 + セッション保存） --}}
        <div class="section">
            <h4>支払い方法</h4>
            <div class="section-divider"></div>

            {{-- 非表示：セッション更新用フォーム --}}
            <form id="pm-form" action="{{ route('purchase.method.update', ['item_id' => $item->id]) }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" id="pm-hidden" name="payment_method" value="{{ $selected ?? 'card' }}">
            </form>

            {{-- セレクト（変更で自動送信） --}}
            <select id="pm-select" class="payment-select" onchange="updateSummary(this.value)">
                <option value="konbini" {{ ($selected ?? 'card') === 'konbini' ? 'selected' : '' }}>コンビニ払い</option>
                <option value="card"    {{ ($selected ?? 'card') === 'card' ? 'selected' : '' }}>カード支払い</option>
            </select>
        </div>

        {{-- 配送先（フォーム外） --}}
        <div class="section">
            <h4>配送先</h4>
            <div class="section-divider"></div>
            <div class="purchase-address-row">
                <div class="purchase-address">
                    <p>〒 {{ $user->postal_code ?? 'XXX-YYYY' }}</p>
                    <p>{{ ($user->address ?? 'ここには住所が入ります') . ($user->building ?? '') }}</p>
                </div>
                <a href="{{ route('purchase.address.edit', ['item_id' => $item->id]) }}" class="address-edit">変更する</a>
            </div>
        </div>
    </div>

    {{-- 右側：支払い情報とボタン --}}
    <div class="purchase-right">
        <div class="purchase-summary-card"
             aria-label="お支払い方法：{{ $displayNames[$selected ?? 'card'] }}">
            <div class="summary-row">
                <span>商品代金</span><span>¥ {{ number_format($item->price) }}</span>
            </div>
            <div class="summary-row">
                <span>支払い方法</span>
                <span id="selected-method">{{ $displayNames[$selected ?? 'card'] }}</span>
            </div>
        </div>

        {{-- 購入確定（confirmへPOST） --}}
        <form action="{{ route('purchase.confirm', ['item_id' => $item->id]) }}" method="POST">
            @csrf
            <input type="hidden" name="payment_method" value="{{ $selected ?? 'card' }}">
            <button type="submit" class="purchase-button">購入する</button>
        </form>
    </div>
</div>

<script>
function updateSummary(v){
    const map = { card: 'クレジットカード', konbini: 'コンビニ払い' };
    document.getElementById('selected-method').innerText = map[v] || 'クレジットカード';
    document.getElementById('pm-hidden').value = v;
    document.getElementById('pm-form').submit(); // セッション更新→再描画
}
</script>
@endsection
