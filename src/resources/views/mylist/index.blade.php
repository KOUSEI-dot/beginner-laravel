@extends('layouts.app')

@section('content')

{{-- タブ切り替えリンク --}}
<div class="tab-container">
    <a href="{{ route('mylist.index', ['tab' => 'recommend', 'keyword' => request('keyword')]) }}"
        class="tab {{ request('tab', 'recommend') === 'recommend' ? 'active' : '' }}">おすすめ</a>

    <a href="{{ route('mylist.index', ['tab' => 'mylist', 'keyword' => request('keyword')]) }}"
        class="tab {{ request('tab') === 'mylist' ? 'active' : '' }}">マイリスト</a>
</div>

<hr class="tab-underline">

{{-- 商品表示 --}}
@php
    $tab = request('tab', 'recommend');
    $itemsToShow = $tab === 'mylist' ? $mylistItems : $recommendItems;
@endphp

<div class="item-grid">
    @foreach ($itemsToShow as $item)
        <div class="item-card">
            <a href="{{ route('item.show', ['item_id' => $item['id']]) }}">
                <div class="image-wrapper">
                    <img src="{{ asset($item['img_url']) }}" alt="{{ $item['name'] }}" class="item-image">
                    @if (!empty($item['sold']) && $item['sold'])
                        <div class="sold-overlay">SOLD</div>
                    @endif
                </div>
                <div class="item-name">{{ $item['name'] }}</div>
            </a>
        </div>
    @endforeach
</div>
@endsection
