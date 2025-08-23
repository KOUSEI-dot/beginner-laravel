<?php

namespace Tests\Feature\Mylist;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MylistIndexTest extends TestCase
{
    use RefreshDatabase;

    private function mylistGet()
    {
        return $this->get(route('mylist.index', ['tab' => 'mylist']));
    }

    /** @test */
    public function いいねした商品だけが表示される()
    {
        $me = User::factory()->create();
        $this->actingAs($me);

        $seller = User::factory()->create();

        // いいねする商品と、しない商品（どちらも他人の出品）
        $liked    = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => true]);
        $notLiked = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => true]);

        // いいね登録
        Like::query()->create(['user_id' => $me->id, 'item_id' => $liked->id]);

        $res = $this->mylistGet();

        $res->assertOk();
        $res->assertSee($liked->name);     // いいね済みは見える
        $res->assertDontSee($notLiked->name); // いいねしてないものは出ない
    }

    /** @test */
    public function 購入済み商品にはSOLDラベルが表示される()
    {
        $me = User::factory()->create();
        $this->actingAs($me);

        $seller = User::factory()->create();

        // 売切（is_listed=false）にして、かつ自分がいいね
        $soldLiked = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => false]);
        Like::query()->create(['user_id' => $me->id, 'item_id' => $soldLiked->id]);

        $res = $this->mylistGet();

        $res->assertOk();
        $res->assertSee($soldLiked->name);
        $res->assertSee('SOLD'); // Bladeでは大文字SOLDを表示
    }

    /** @test */
    public function 自分が出品した商品は表示されない()
    {
        $me = User::factory()->create();
        $this->actingAs($me);

        // 自分が出品（もし自分でいいねしても、Controller側で自分の出品は除外される想定）
        $myItem = Item::factory()->create(['user_id' => $me->id, 'is_listed' => true]);
        Like::query()->create(['user_id' => $me->id, 'item_id' => $myItem->id]);

        // 他ユーザー出品でいいね済み → これは表示される
        $other = User::factory()->create();
        $otherItem = Item::factory()->create(['user_id' => $other->id, 'is_listed' => true]);
        Like::query()->create(['user_id' => $me->id, 'item_id' => $otherItem->id]);

        $res = $this->mylistGet();

        $res->assertOk();
        $res->assertDontSee($myItem->name);     // 自分の出品は出ない
        $res->assertSee($otherItem->name);      // 他人の出品は出る
    }

    /** @test */
    public function 未認証の場合は何も表示されない()
    {
        // 未ログインのまま。他ユーザーの商品をいくつか用意
        $seller = User::factory()->create();
        $i1 = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => true]);
        $i2 = Item::factory()->create(['user_id' => $seller->id, 'is_listed' => false]);

        $res = $this->mylistGet();

        $res->assertOk();
        // マイリストタブは未認証時は空コレクションのはず
        $res->assertDontSee($i1->name);
        $res->assertDontSee($i2->name);
        $res->assertDontSee('SOLD'); // 当然SOLDも出ない
    }
}
