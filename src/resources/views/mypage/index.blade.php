@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-icon">
        @if (!empty($user->profile_image))
            <img src="{{ asset($user->profile_image) }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
        @endif
        </div>

        <div class="profile-info">
            <h2 class="username">{{ $user->name }}</h2>
            <a href="{{ route('profile.edit') }}" class="edit-profile-btn">プロフィールを編集</a>
        </div>
    </div>

    <div class="tab-container">
        <div class="tab active" data-tab="listed">出品した商品</div>
        <div class="tab" data-tab="purchased">購入した商品</div>
    </div>
    <hr class="tab-underline">

    {{-- 出品商品 --}}
    <div class="item-grid tab-content" id="listed" style="display: flex; flex-wrap: wrap;">
        @forelse ($mylistItems as $item)
            @php
                $src = Str::startsWith($item->img_url, ['http://','https://'])
                    ? $item->img_url
                    : Storage::url(ltrim($item->img_url, '/'));
            @endphp
            <div class="item-card">
                <a href="{{ route('item.show', ['item_id' => $item->id]) }}">
                    <img src="{{ $src }}" alt="{{ $item->name }}" class="item-image">
                    <div class="item-name">{{ $item->name }}</div>
                </a>
            </div>
        @empty
        @endforelse
    </div>

    {{-- 購入商品 --}}
    <div class="item-grid tab-content" id="purchased" style="display: none; flex-wrap: wrap;">
        @forelse ($recommendItems as $item)
            @php
                $src = Str::startsWith($item->img_url, ['http://','https://'])
                    ? $item->img_url
                    : Storage::url(ltrim($item->img_url, '/'));
            @endphp
            <div class="item-card">
                <a href="{{ route('item.show', ['item_id' => $item->id]) }}">
                    <img src="{{ $src }}" alt="{{ $item->name }}" class="item-image">
                    <div class="item-name">{{ $item->name }}</div>
                </a>
            </div>
        @empty
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const target = tab.dataset.tab;
                contents.forEach(content => {
                    content.style.display = content.id === target ? 'flex' : 'none';
                });
            });
        });
    });
</script>
@endsection
