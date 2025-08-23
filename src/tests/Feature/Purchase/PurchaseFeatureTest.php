<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function showRoute($id)     { return route('purchase.show', ['item_id' => $id]); }
    private function confirmRoute($id)  { return route('purchase.confirm', ['item_id' => $id]); }
    private function successRoute($id)  { return route('purchase.success', ['item_id' => $id]); }
    private function listingRoute()     { return route('mylist.index', ['tab' => 'recommend']); }
    private function profileRoute()     { return route('mypage'); }

    /** @test */
    public function 購入ボタンで購入が完了する()
    {
        $buyer  = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();
        $item   = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => true]);

        $this->actingAs($buyer);

        // 購入画面に入れる
        $this->get($this->showRoute($item->id))->assertOk();

        // 購入実行
        $res = $this->post($this->confirmRoute($item->id));
        $res->assertStatus(302); // success等へ
        // 成功ページへ飛ぶ実装なら以下もOK
        // $res->assertRedirect($this->successRoute($item->id));

        // 購入レコードが作られている
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);
    }

    /** @test */
    public function 購入済み商品は一覧でSOLD表示される()
    {
        $buyer  = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();
        $item   = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => true]);

        $this->actingAs($buyer);
        $this->post($this->confirmRoute($item->id))->assertStatus(302);

        // 商品一覧（recommendタブ）で名前と SOLD が見える
        $res = $this->get($this->listingRoute());
        $res->assertOk();
        $res->assertSee($item->name);
        $res->assertSee('SOLD'); // Bladeの表記に合わせて大文字
    }

    /** @test */
    public function 購入品がプロフィール_購入した商品一覧に表示される()
    {
        $buyer  = User::factory()->create(['email_verified_at' => now()]);
        $seller = User::factory()->create();
        $item   = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => true]);

        $this->actingAs($buyer);
        $this->post($this->confirmRoute($item->id))->assertStatus(302);

        // プロフィール画面で購入済み商品が見える前提
        $res = $this->get($this->profileRoute());
        $res->assertOk();
        $res->assertSee($item->name);
    }
}
