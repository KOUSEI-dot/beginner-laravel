<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // ログイン必須想定（auth ミドルウェア）

        // 出品した商品（Eloquentコレクションで渡す）
        $mylistItems = Item::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        // 購入した商品（Eloquentコレクションで渡す）
        $purchasedIds = Purchase::where('user_id', $user->id)->pluck('item_id');

        $recommendItems = Item::query()
            ->whereIn('id', $purchasedIds)
            ->latest('id')
            ->get();

        return view('mypage.index', compact('user', 'mylistItems', 'recommendItems'));
    }
}
