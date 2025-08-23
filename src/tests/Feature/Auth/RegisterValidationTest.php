<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',           // 8文字以上
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    /** @test */
    public function 名前が入力されていない場合_バリデーションメッセージが表示される()
    {
        $res = $this->from('/register')
            ->followingRedirects()
            ->post('/register', $this->validPayload(['name' => '']));

        $res->assertOk()
            ->assertSee('お名前を入力してください');
    }

    /** @test */
    public function メールアドレスが入力されていない場合_バリデーションメッセージが表示される()
    {
        $res = $this->from('/register')
            ->followingRedirects()
            ->post('/register', $this->validPayload(['email' => '']));

        $res->assertOk()
            ->assertSee('メールアドレスを入力してください');
    }

    /** @test */
    public function パスワードが入力されていない場合_バリデーションメッセージが表示される()
    {
        $res = $this->from('/register')
            ->followingRedirects()
            ->post('/register', $this->validPayload([
                'password' => '',
                'password_confirmation' => '',
            ]));

        $res->assertOk()
            ->assertSee('パスワードを入力してください');
    }

    /** @test */
    public function パスワードが7文字以下の場合_バリデーションメッセージが表示される()
    {
        $res = $this->from('/register')
            ->followingRedirects()
            ->post('/register', $this->validPayload([
                'password' => 'short7',
                'password_confirmation' => 'short7',
            ]));

        $res->assertOk()
            ->assertSee('パスワードは8文字以上で入力してください');
    }

    /** @test */
    public function 確認用パスワードと一致しない場合_バリデーションメッセージが表示される()
    {
        $res = $this->from('/register')
            ->followingRedirects()
            ->post('/register', $this->validPayload([
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ]));

        $res->assertOk()
            ->assertSee('パスワードと一致しません');
    }

    /** @test */
    public function すべて正しく入力された場合_会員情報が登録され_メール確認画面に遷移する()
    {
        $res = $this->post('/register', $this->validPayload());

        // メール認証フロー: verify notice にリダイレクト
        $res->assertRedirect('/email/verify');

        // DBに登録されていること
        $this->assertDatabaseHas('users', [
            'email' => 'taro@example.com',
        ]);

        // 追加の妥当性（任意だがおすすめ）
        $user = \App\Models\User::where('email', 'taro@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at, '登録直後は未認証のはず');

        // Laravel標準では登録直後にログイン状態になる
        $this->assertAuthenticatedAs($user);
    }

}
