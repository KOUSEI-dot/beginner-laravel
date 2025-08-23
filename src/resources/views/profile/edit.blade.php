@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

<div class="container">
<h2>プロフィール設定</h2>

{{-- ブラウザ検証を止めて Laravel のエラーだけ出す --}}
<form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="image-wrapper">
        @if (!empty($user->profile_image))
            <img src="{{ asset($user->profile_image) }}" class="profile-icon">
        @else
            <img src="{{ asset('images/default.png') }}" class="profile-icon">
        @endif

        <label for="profile_image" class="image-upload-label">画像を選択する</label>
        <input type="file" name="profile_image" id="profile_image" accept=".jpeg,.jpg,.png" style="display:none;">
        @error('profile_image') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <label for="name">ユーザー名</label>
    <input type="text" name="name" id="name"
            value="{{ old('name', $user->name) }}"
            class="@error('name') invalid @enderror">
    @error('name') <p class="form-error">{{ $message }}</p> @enderror

    <label for="postal_code">郵便番号</label>
    <input type="text" name="postal_code" id="postal_code"
            value="{{ old('postal_code', $user->postal_code) }}"
            placeholder="123-4567" inputmode="numeric" autocomplete="postal-code"
            class="@error('postal_code') invalid @enderror"
            aria-invalid="@error('postal_code') true @enderror"
            aria-describedby="postal_code_error">
    @error('postal_code') <p id="postal_code_error" class="form-error">{{ $message }}</p> @enderror

    <label for="address">住所</label>
    <input type="text" name="address" id="address"
            value="{{ old('address', $user->address) }}"
            class="@error('address') invalid @enderror">
    @error('address') <p class="form-error">{{ $message }}</p> @enderror

    <label for="building">建物名</label>
    <input type="text" name="building" id="building"
            value="{{ old('building', $user->building) }}"
            class="@error('building') invalid @enderror">
    @error('building') <p class="form-error">{{ $message }}</p> @enderror

    <button type="submit" class="submit-btn">更新する</button>
</form>
</div>
@endsection
