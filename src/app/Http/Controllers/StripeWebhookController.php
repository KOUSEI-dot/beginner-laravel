<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $event = json_decode($payload, true);

        // イベントタイプをチェック
        if ($event['type'] === 'checkout.session.completed') {
            $session = $event['data']['object'];

            $item_id = $session['metadata']['item_id'];
            $user_id = $session['metadata']['user_id'];

            // 購入レコードを保存
            Purchase::firstOrCreate([
                'user_id' => $user_id,
                'item_id' => $item_id,
            ]);

            Log::info("購入登録済み: user_id=$user_id item_id=$item_id");
        }

        return response()->json(['status' => 'success']);
    }
}

