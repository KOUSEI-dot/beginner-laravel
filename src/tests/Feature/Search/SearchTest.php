<?php

namespace Tests\Feature\Search;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品名で部分一致検索ができる()
    {
        // ゲスト想定（自分の出品除外の影響を避けるためログインしない）
        $seller = User::factory()->create();

        Item::factory()->create(['user_id' => $seller->id, 'name' => 'Red Shoes',      'is_listed' => true]);
        Item::factory()->create(['user_id' => $seller->id, 'name' => 'Blue Shirt',     'is_listed' => true]);
        Item::factory()->create(['user_id' => $seller->id, 'name' => 'Shoe Cleaner',   'is_listed' => true]);

        // 「Shoe」で部分一致: Red Shoes / Shoe Cleaner がヒット、Blue Shirt は除外
        $res = $this->get(route('mylist.index', ['tab' => 'recommend', 'keyword' => 'Shoe']));

        $res->assertOk();
        $res->assertSee('Red Shoes');
        $res->assertSee('Shoe Cleaner');
        $res->assertDontSee('Blue Shirt');
    }

    /** @test */
    public function 検索状態がマイリストでも保持されている()
    {
        // 検索結果ページ（おすすめタブ）にキーワード付きでアクセス
        $keyword = 'ring';
        $res = $this->get(route('mylist.index', ['tab' => 'recommend', 'keyword' => $keyword]));

        $res->assertOk();
        // タブリンクがキーワードを引き回していること（hrefのクエリに含まれる）
        $res->assertSee('tab=mylist');
        $res->assertSee("keyword={$keyword}");
    }
}
