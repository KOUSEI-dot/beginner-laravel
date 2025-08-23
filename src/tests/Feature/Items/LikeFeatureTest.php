<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function show($itemId)
    {
        return route('item.show', ['item_id' => $itemId]);
    }

    /** @test */
    public function いいねで登録され_カウントが増える()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['is_listed' => true]);

        // ログインして詳細へ
        $this->actingAs($user);
        $res = $this->get($this->show($item->id));
        $res->assertOk();
        $res->assertSee('<span class="icon-count">0</span>', false);

        // いいね実行
        $res = $this->post("/item/{$item->id}/like");
        $res->assertRedirect();

        // DB登録 & カウント増
        $this->assertDatabaseHas('likes', ['user_id' => $user->id, 'item_id' => $item->id]);

        $res = $this->get($this->show($item->id));
        $res->assertOk();
        $res->assertSee('<span class="icon-count">1</span>', false);
    }

    /** @test */
    public function いいね済みアイコンは色が変わる_状態属性で判定()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['is_listed' => true]);

        $this->actingAs($user);
        $this->post("/item/{$item->id}/like")->assertRedirect();

        // いいね後の詳細で data-liked="1" が付与されている前提
        $res = $this->get($this->show($item->id));
        $res->assertOk();
        $res->assertSee('data-liked="1"', false);
    }

    /** @test */
    public function 再度押下でいいね解除でき_カウントが減る()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['is_listed' => true]);

        // 事前にいいね済みにする
        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $this->actingAs($user);

        // 解除実行（DELETE /item/{id}/like を想定）
        $res = $this->delete("/item/{$item->id}/like");
        $res->assertRedirect();

        $this->assertDatabaseMissing('likes', ['user_id' => $user->id, 'item_id' => $item->id]);

        $res = $this->get($this->show($item->id));
        $res->assertOk();
        $res->assertSee('<span class="icon-count">0</span>', false);
        $res->assertDontSee('data-liked="1"', false);
    }
}
