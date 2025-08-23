<?php

namespace Tests\Feature\Profile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;

class UserInfoFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function mypageRoute(): string
    {
        return route('mypage');
    }

    private function profileEditRoute(): string
    {
        return route('profile.edit');
    }

    /** @test */
    public function プロフィールで必要情報が表示される_画像_ユーザー名_出品一覧_購入一覧()
    {
        // ログインユーザー（プロフィール画像付き）
        $user = User::factory()->create([
            'name'              => 'テスト太郎',
            'email_verified_at' => now(),
            'profile_image'     => 'storage/profile_images/test.png',
        ]);

        // 自分が出品した商品（出品一覧に表示される想定）
        $listedA = Item::factory()->create([
            'user_id'   => $user->id,
            'name'      => '出品A',
            'is_listed' => true,
        ]);
        $listedB = Item::factory()->create([
            'user_id'   => $user->id,
            'name'      => '出品B',
            'is_listed' => true,
        ]);

        // 他ユーザーの商品を購入（購入一覧に表示される想定）
        $seller = User::factory()->create();
        $p1 = Item::factory()->create([
            'user_id'   => $seller->id,
            'name'      => '購入済み1',
            'is_listed' => false,
        ]);
        $p2 = Item::factory()->create([
            'user_id'   => $seller->id,
            'name'      => '購入済み2',
            'is_listed' => false,
        ]);

        Purchase::create(['user_id' => $user->id, 'item_id' => $p1->id, 'payment_method' => 'test']);
        Purchase::create(['user_id' => $user->id, 'item_id' => $p2->id, 'payment_method' => 'test']);

        // 表示確認（/mypage）
        $this->actingAs($user);
        $res = $this->get($this->mypageRoute());
        $res->assertOk();

        // ユーザー名
        $res->assertSee('テスト太郎');

        // プロフィール画像（mypage は相対パス出力なのでパス文字列を確認）
        $res->assertSee('storage/profile_images/test.png');

        // 出品した商品名
        $res->assertSee($listedA->name);
        $res->assertSee($listedB->name);

        // 購入した商品名
        $res->assertSee($p1->name);
        $res->assertSee($p2->name);
    }

    /** @test */
    public function プロフィール編集で過去設定が初期値で表示される_画像_ユーザー名_郵便番号_住所()
    {
        // 事前にプロフィール情報を設定済みのユーザー
        $user = User::factory()->create([
            'name'              => '初期ユーザー名',
            'email_verified_at' => now(),
            'postal_code'       => '123-4567',
            'address'           => '東京都渋谷区1-2-3',
            'building'          => 'テストビル101',
            'profile_image'     => 'storage/profile_images/prev.png',
        ]);

        $this->actingAs($user);
        $res = $this->get($this->profileEditRoute());
        $res->assertOk();

        // 画像は profile.edit では asset() が付くのでフルURLになる
        // 例: http://localhost/storage/profile_images/prev.png
        $res->assertSee('http://localhost/storage/profile_images/prev.png');

        // 入力項目の初期値（HTML属性をそのまま確認するので $escaped=false）
        $res->assertSee('value="初期ユーザー名"', false);
        $res->assertSee('value="123-4567"', false);
        $res->assertSee('value="東京都渋谷区1-2-3"', false);
        $res->assertSee('value="テストビル101"', false);
    }
}
