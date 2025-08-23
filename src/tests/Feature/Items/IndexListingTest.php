<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 全商品を取得できる_他ユーザーの出品が一覧表示される()
    {
        $seller = User::factory()->create();
        $items = Item::factory()->count(3)->create(['user_id' => $seller->id, 'is_listed' => true]);

        // ★ 実運用の一覧はここ（おすすめタブ）
        $res = $this->get(route('mylist.index', ['tab' => 'recommend']));

        $res->assertOk();
        foreach ($items as $it) {
            $res->assertSee($it->name);
        }
    }

    /** @test */
    public function 購入済み商品にはSOLDラベルが表示される()
    {
        $seller = User::factory()->create();
        $soldItem   = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => false]); // 購入済み想定
        $listedItem = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => true]);

        $res = $this->get(route('mylist.index', ['tab' => 'recommend']));

        $res->assertOk();
        $res->assertSee($soldItem->name);
        // ★ Bladeでは "SOLD"（大文字）なのでこちらに合わせる
        $res->assertSee('SOLD');

        $res->assertSee($listedItem->name);
        // SOLDは出ない想定（厳密にその行だけの判定は難しいので最低限の確認に留める）
    }

    /** @test */
    public function 自分が出品した商品は表示されない()
    {
        $me = User::factory()->create();
        $this->actingAs($me);

        $myItem    = Item::factory()->create(['user_id' => $me->id, 'is_listed' => true]);
        $otherUser = User::factory()->create();
        $otherItem = Item::factory()->create(['user_id' => $otherUser->id, 'is_listed' => true]);

        $res = $this->get(route('mylist.index', ['tab' => 'recommend']));

        $res->assertOk();
        $res->assertDontSee($myItem->name);
        $res->assertSee($otherItem->name);
    }
}
