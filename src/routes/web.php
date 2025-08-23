<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MylistController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// 認証関連
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// 認証メール確認ページ
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// 認証リンクアクセス時
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了
    session()->flash('status', 'email-verified');
    return redirect()->route('profile.edit'); // ←固定でプロフィールへ
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ログイン後トップ
Route::get('/', function () {
    if (Auth::check()) {
        if (! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        return redirect()->route('mylist.index', ['tab' => 'mylist']);
    }
    return redirect()->route('guest.recommend');
})->name('home');

// ログアウト
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ゲストおすすめ
Route::get('/guest/recommend', [MylistController::class, 'guestRecommend'])->name('guest.recommend');

// 検索・マイリスト
Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
Route::get('/mylist', [MylistController::class, 'index'])->name('mylist.index');

// プロフィール
Route::get('/mypage/profile', [ProfileController::class, 'edit'])->middleware(['auth', 'verified'])->name('profile.edit');
Route::post('/mypage/profile', [ProfileController::class, 'update'])->middleware(['auth', 'verified'])->name('profile.update');

// マイページ
Route::get('/mypage', [MypageController::class, 'index'])->middleware(['auth', 'verified'])->name('mypage');

// 出品
Route::get('/sell', [ProductController::class, 'create'])->middleware(['auth', 'verified'])->name('sell');
Route::post('/products', [ProductController::class, 'store'])->middleware(['auth', 'verified'])->name('product.store');

// 商品一覧・詳細
Route::get('/items', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');

// いいね（トグル。POST/DELETE/旧unlike名も全部この1本に集約）
Route::post('/item/{item_id}/like',   [ItemController::class, 'toggleLike'])->middleware(['auth', 'verified'])->name('item.like');
Route::post('/item/{item_id}/unlike', [ItemController::class, 'toggleLike'])->middleware(['auth', 'verified'])->name('item.unlike'); // 互換用
Route::delete('/item/{item_id}/like', [ItemController::class, 'toggleLike'])->middleware(['auth', 'verified']); // 互換用（名前なし）

// コメント
Route::post('/item/{item_id}/comment', [ItemController::class, 'postComment'])->middleware(['auth', 'verified'])->name('items.comment');

// 購入
Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->middleware(['auth', 'verified'])->name('purchase.show');
Route::post('/purchase/{item_id}', [PurchaseController::class, 'confirm'])->middleware(['auth', 'verified'])->name('purchase.confirm');
Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->middleware(['auth', 'verified'])->name('purchase.address.edit');
Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->middleware(['auth', 'verified'])->name('purchase.address.update');
Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->middleware(['auth', 'verified'])->name('purchase.success');
Route::post('/purchase/{item_id}/method', [PurchaseController::class, 'updatePaymentMethod'])->middleware(['auth'])->name('purchase.method.update');

// Stripe Webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
