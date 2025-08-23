<?php

namespace Tests\Feature\Products;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Item;

class ProductStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function sellRoute(): string
    {
        return route('sell'); // GET /sell
    }

    private function storeRoute(): string
    {
        return route('product.store'); // POST /products
    }

    /** @test */
    public function 出品画面から必要情報を保存できる_カテゴリ_状態_名前_説明_価格()
    {
        // 認証・メール認証済みユーザー
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 出品画面が開ける
        $this->get($this->sellRoute())->assertOk();

        // 画像アップロードをフェイク
        Storage::fake('public');
        $file = UploadedFile::fake()->create('item.jpg', 100, 'image/jpeg');

        // 入力データ（プロジェクトのフォーム名に合わせて適宜変更可）
        $payload = [
            'name'        => '出品テスト商品',
            'brand'       => 'BrandY',
            'description' => 'テスト説明文です。',
            'price'       => 9999,
            'condition'   => 'like-new',
            // フォームが複数選択なら name="categories[]" のことが多いですが、
            // コントローラ側で取り出すキーが "categories" でも動くよう配列で送ります。
            'categories'  => ['シューズ', 'スポーツ'],
            'image'       => $file, // name="image" を想定
        ];

        // 登録
        $res = $this->post($this->storeRoute(), $payload);
        $res->assertStatus(302); // 遷移先はプロジェクト都合のため汎用に

        // 登録レコード取得
        $item = Item::latest('id')->first();
        $this->assertNotNull($item, 'Item が作成されていません');

        // 基本項目
        $this->assertSame($user->id, $item->user_id);
        $this->assertSame('出品テスト商品', $item->name);
        $this->assertSame('BrandY', $item->brand);
        $this->assertSame('テスト説明文です。', $item->description);
        $this->assertSame(9999, (int) $item->price);
        $this->assertSame('like-new', $item->condition);

        // カテゴリは JSON or CSV どちらでも通す
        $storedCats = $item->categories;
        if (is_string($storedCats)) {
            $decoded = json_decode($storedCats, true);
            if (is_array($decoded)) {
                $storedCats = $decoded;
            } else {
                $storedCats = array_values(array_filter(array_map('trim', explode(',', $storedCats))));
            }
        }
        $this->assertIsArray($storedCats);
        $this->assertContains('シューズ', $storedCats);
        $this->assertContains('スポーツ', $storedCats);

        // 出品状態（あれば true 想定。スキップ可）
        if ($item->getAttribute('is_listed') !== null) {
            $this->assertTrue((bool) $item->is_listed);
        }

        // 画像が保存されていること（img_url が 'storage/...' 前提のことが多い）
        $this->assertNotEmpty($item->img_url ?? '', 'img_url が未設定です');

        // 'storage/xxx' → 'public/xxx' に置換して存在確認（パスの差異を吸収）
        $publicPath = preg_replace('#^storage/#', 'public/', $item->img_url);
        Storage::disk('public')->assertExists(preg_replace('#^public/#', '', $publicPath));
    }
}
