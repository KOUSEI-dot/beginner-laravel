@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">

<div class="address-edit-container">
  <h2 class="address-title">住所の変更</h2>

  <form method="POST" action="{{ route('purchase.address.update', ['item_id' => $item_id]) }}" novalidate>
    @csrf

    <div class="form-group">
      <label>お名前</label>
      <input type="text" name="name" value="{{ old('name', $user->name) }}" class="@error('name') invalid @enderror">
      @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label>郵便番号</label>
      <input type="text" name="postal_code"
            value="{{ old('postal_code', $user->postal_code) }}"
            placeholder="123-4567" inputmode="numeric" autocomplete="postal-code"
            class="@error('postal_code') invalid @enderror"
            aria-invalid="@error('postal_code') true @enderror">
      @error('postal_code') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label>住所</label>
      <input type="text" name="address" value="{{ old('address', $user->address) }}" class="@error('address') invalid @enderror">
      @error('address') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label>建物名</label>
      <input type="text" name="building" value="{{ old('building', $user->building) }}" class="@error('building') invalid @enderror">
      @error('building') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="purchase-button">更新する</button>
  </form>
</div>
@endsection
