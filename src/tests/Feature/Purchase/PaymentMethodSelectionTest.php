<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function showRoute($id)    { return route('purchase.show', ['item_id' => $id]); }
    private function updateRoute($id)  { return route('purchase.method.update', ['item_id' => $id]); }

    /** @test */
    public function 支払い方法の選択が小計画面に反映される()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create(['is_listed' => true]);

        $this->actingAs($user);

        // 初期表示（デフォルト: クレジットカード）
        $res = $this->get($this->showRoute($item->id));
        $res->assertOk();
        $res->assertSee('お支払い方法：クレジットカード');

        // プルダウンで「コンビニ払い」を選択 → 反映される
        $res = $this->post($this->updateRoute($item->id), [
            'payment_method' => 'konbini',
        ]);
        $res->assertRedirect($this->showRoute($item->id));

        $res = $this->get($this->showRoute($item->id));
        $res->assertOk();
        $res->assertSee('お支払い方法：コンビニ払い');

        // もう一度クレカに戻す → 反映
        $res = $this->post($this->updateRoute($item->id), [
            'payment_method' => 'card',
        ]);
        $res->assertRedirect($this->showRoute($item->id));

        $res = $this->get($this->showRoute($item->id));
        $res->assertOk();
        $res->assertSee('お支払い方法：クレジットカード');
    }
}
