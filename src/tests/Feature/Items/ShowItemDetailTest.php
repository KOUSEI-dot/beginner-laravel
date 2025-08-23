<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowItemDetailTest extends TestCase
{
    use RefreshDatabase;

    private function makeItemForDetail(): array
    {
        $seller = User::factory()->create();

        $item = Item::factory()->create([
            'user_id'     => $seller->id,
            'is_listed'   => true,
            'name'        => 'テストスニーカー',
            'brand'       => 'BrandX',
            'price'       => 999, // フォーマット差異の影響を避けるため 999
            'description' => '軽くて履きやすい。',
            'img_url'     => 'https://example.com/test.jpg',
            // Blade 側が array でも json でも扱える想定。モデルに casts が無くても安全なように JSON を入れる
            'categories'  => json_encode(['シューズ', 'スポーツ']),
            'condition'   => 'like-new',
        ]);

        // いいね2件
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        Like::query()->create(['user_id' => $u1->id, 'item_id' => $item->id]);
        Like::query()->create(['user_id' => $u2->id, 'item_id' => $item->id]);

        // コメント2件
        $c1user = User::factory()->create(['name' => '山田太郎']);
        $c2user = User::factory()->create(['name' => '佐藤花子']);
        Comment::query()->create([
            'user_id' => $c1user->id,
            'item_id' => $item->id,
            'text'    => 'サイズ感ちょうど良かったです！',
        ]);
        Comment::query()->create([
            'user_id' => $c2user->id,
            'item_id' => $item->id,
            'text'    => 'カラーが最高。',
        ]);

        return compact('item', 'c1user', 'c2user');
    }

    /** @test */
    public function 必要な情報が詳細ページに表示される()
    {
        $data = $this->makeItemForDetail();
        $item = $data['item'];

        $res = $this->get(route('item.show', ['item_id' => $item->id]));

        $res->assertOk();

        // 画像URL / 商品名 / ブランド / 価格 / 説明
        $res->assertSee('https://example.com/test.jpg');
        $res->assertSee('テストスニーカー');
        $res->assertSee('BrandX');
        $res->assertSee('999');
        $res->assertSee('軽くて履きやすい。');

        // いいね数 2 / コメント数 2（どこかに数値が表示されている想定）
        $res->assertSee('2');

        // 商品情報の見出し（状態のラベルがあること）とカテゴリ
        $res->assertSee('商品の状態'); // セクションラベルの存在で確認
        $res->assertSee('シューズ');
        $res->assertSee('スポーツ');

        // コメントしたユーザー名と内容
        $res->assertSee('山田太郎');
        $res->assertSee('佐藤花子');
        $res->assertSee('サイズ感ちょうど良かったです！');
        $res->assertSee('カラーが最高。');
    }

    /** @test */
    public function 複数選択されたカテゴリが表示される()
    {
        $data = $this->makeItemForDetail();
        $item = $data['item'];

        $res = $this->get(route('item.show', ['item_id' => $item->id]));

        $res->assertOk();
        $res->assertSee('シューズ');
        $res->assertSee('スポーツ');
    }
}
