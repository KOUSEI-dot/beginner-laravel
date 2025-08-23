<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'email' => 'taro@example.com',
            'password' => 'password123',
        ], $overrides);
    }

    /** @test */
    public function メールアドレス未入力でバリデーションメッセージが表示される()
    {
        $res = $this->from('/login')
            ->followingRedirects()
            ->post('/login', $this->payload(['email' => '']));

        $res->assertOk()
            ->assertSee('メールアドレスを入力してください');
        $this->assertGuest();
    }

    /** @test */
    public function パスワード未入力でバリデーションメッセージが表示される()
    {
        $res = $this->from('/login')
            ->followingRedirects()
            ->post('/login', $this->payload(['password' => '']));

        $res->assertOk()
            ->assertSee('パスワードを入力してください');
        $this->assertGuest();
    }

    /** @test */
    public function 入力情報が間違っている場合_バリデーションメッセージが表示される()
    {
        // 登録されていない組み合わせで試行
        $res = $this->from('/login')
            ->followingRedirects()
            ->post('/login', $this->payload());

        $res->assertOk()
            ->assertSee('ログイン情報が登録されていません');
        $this->assertGuest();
    }

    /** @test */
    public function 正しい情報ならログイン処理が実行される()
    {
        $user = User::factory()->create([
            'email' => 'taro@example.com',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->post('/login', $this->payload());

        // どこへ遷移するかは実装次第なので「リダイレクトされたこと」だけ確認
        $res->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
