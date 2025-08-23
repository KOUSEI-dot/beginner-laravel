<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Http\Requests\AddressRequest;

class PurchaseController extends Controller
{
    public function show($item_id)
    {
        if (!Auth::check()) {
            abort(403, 'ログインが必要です');
        }

        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // セレクトの現在値（デフォルト: card）
        $selected = session("purchase.payment_method.$item_id", 'card');
        $displayNames = [
            'card'    => 'クレジットカード',
            'konbini' => 'コンビニ払い',
        ];

        return view('purchase.purchase', compact('item', 'user', 'selected', 'displayNames'));
    }

    public function editAddress($item_id)
    {
        $user = Auth::user();

        return view('purchase.edit_address', [
            'item_id' => $item_id,
            'user'    => $user,
        ]);
    }

    // AddressRequest を使用して郵便番号の形式などを厳密にチェック
    public function updateAddress(AddressRequest $request, $item_id)
    {
        $v = $request->validated();

        $user = Auth::user();
        // お名前も購入配送先として保持（仕様に合わせて調整OK）
        $user->name        = $v['name'];
        $user->postal_code = $v['postal_code'];
        $user->address     = $v['address'];
        $user->building    = $v['building'] ?? null;
        $user->save();

        return redirect()->route('purchase.show', ['item_id' => $item_id])
            ->with('success', '住所を更新しました');
    }

    /**
     * 支払い方法の選択をセッションに保存（プルダウンの「変更」）
     */
    public function updatePaymentMethod(Request $request, $item_id)
    {
        $request->validate([
            'payment_method' => 'required|in:card,konbini',
        ]);

        session(["purchase.payment_method.$item_id" => $request->input('payment_method')]);

        return redirect()->route('purchase.show', ['item_id' => $item_id]);
    }

    public function confirm(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // リクエスト優先、なければセッション、最終デフォルトは card
        $paymentMethod = $request->input(
            'payment_method',
            session("purchase.payment_method.$item_id", 'card')
        );

        // ★ テスト環境 or Stripe未設定 → 外部通信スキップして即購入成立
        if (app()->environment('testing') || empty(config('services.stripe.secret'))) {
            Purchase::firstOrCreate(
                ['user_id' => $user->id, 'item_id' => $item->id],
                ['payment_method' => $paymentMethod] // 選択を保存
            );

            // SOLD 表示のために出品停止
            $item->update(['is_listed' => false]);

            return redirect()->route('purchase.success', ['item_id' => $item->id]);
        }

        // 本番/開発用: Stripe Checkout
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            // Stripe の支払い方法タイプに合わせる
            'payment_method_types' => $paymentMethod === 'konbini' ? ['konbini'] : ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount'  => $item->price, // 税込額(整数)を想定
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', ['item_id' => $item_id]),
            'cancel_url'  => route('purchase.show',   ['item_id' => $item_id]),
            'metadata' => [
                'user_id'        => $user->id,
                'item_id'        => (string)$item_id,
                'payment_method' => $paymentMethod,
            ],
        ]);

        return redirect($session->url);
    }

    public function success($item_id)
    {
        // 二重登録防止
        Purchase::firstOrCreate(
            ['user_id' => Auth::id(), 'item_id' => $item_id],
            ['payment_method' => 'stripe'] // 本番Stripe成功時の印
        );

        // SOLD 表示に寄せる
        Item::whereKey($item_id)->update(['is_listed' => false]);

        return redirect()->route('mypage')->with('success', '購入が完了しました');
    }
}
