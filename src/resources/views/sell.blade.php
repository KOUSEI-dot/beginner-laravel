@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">

<div class="container">
<h1 class="title">商品の出品</h1>

<form method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data" novalidate>
    @csrf

    {{-- 商品画像 --}}
    <div class="form-section">
    <label class="label">商品画像</label>
    <div class="image-upload-area">
        <label for="image" class="upload-label">画像を選択する</label>
        <input type="file" name="image" id="image" accept=".jpeg,.jpg,.png,image/*"
            class="@error('image') invalid @enderror">
    </div>
    @error('image') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- 商品詳細 --}}
    <div class="form-section">
    <h2 class="section-title">商品の詳細</h2>

    {{-- カテゴリー（複数選択） --}}
    <label class="label">カテゴリー</label>
    <div class="category-group @error('categories') invalid @enderror @error('categories.*') invalid @enderror">
        @php $preset = ['ファッション','家電','インテリア','レディース','メンズ','コスメ','本','ゲーム','スポーツ','キッチン','ハンドメイド','アクセサリー','おもちゃ','ベビー・キッズ']; @endphp
        @foreach ($preset as $index => $category)
        <input
            type="checkbox"
            id="category-{{ $index }}"
            name="categories[]"
            value="{{ $category }}"
            class="category-checkbox"
            {{ in_array($category, old('categories', []), true) ? 'checked' : '' }}
        >
        <label for="category-{{ $index }}" class="category-button">{{ $category }}</label>
        @endforeach
    </div>
    @error('categories')   <p class="form-error">{{ $message }}</p> @enderror
    @error('categories.*') <p class="form-error">{{ $message }}</p> @enderror

    {{-- 商品の状態 --}}
    <label class="label">商品の状態</label>
    <select name="condition" class="input @error('condition') invalid @enderror">
        <option value="">選択してください</option>
        <option value="良好"                 {{ old('condition')==='良好' ? 'selected' : '' }}>良好</option>
        <option value="目立った傷や汚れなし" {{ old('condition')==='目立った傷や汚れなし' ? 'selected' : '' }}>目立った傷や汚れなし</option>
        <option value="やや傷や汚れあり"     {{ old('condition')==='やや傷や汚れあり' ? 'selected' : '' }}>やや傷や汚れあり</option>
        <option value="状態が悪い"           {{ old('condition')==='状態が悪い' ? 'selected' : '' }}>状態が悪い</option>
    </select>
    @error('condition') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    {{-- 商品名など --}}
    <div class="form-section">
    <label class="label">商品名</label>
    <input type="text" name="name" class="input @error('name') invalid @enderror" value="{{ old('name') }}">
    @error('name') <p class="form-error">{{ $message }}</p> @enderror

    <label class="label">ブランド名</label>
    <input type="text" name="brand" class="input @error('brand') invalid @enderror" value="{{ old('brand') }}">
    @error('brand') <p class="form-error">{{ $message }}</p> @enderror

    <label class="label">商品の説明</label>
    <textarea name="description" class="input @error('description') invalid @enderror">{{ old('description') }}</textarea>
    @error('description') <p class="form-error">{{ $message }}</p> @enderror

    <label class="label">販売価格</label>
    <div class="price-input-wrapper">
        <span class="yen">¥</span>
        <input type="number" name="price" class="input price-input @error('price') invalid @enderror"
            step="1" min="0" value="{{ old('price') }}">
    </div>
    @error('price') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="button-wrapper">
    <button type="submit" class="submit-button">出品する</button>
    </div>
</form>
</div>
@endsection
