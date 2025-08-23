<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログアウトができる()
    {
        // 1. ユーザーにログインをする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 念のため事前にログイン状態を確認
        $this->assertAuthenticatedAs($user);

        // 2. ログアウトボタンを押す（POST /logout）
        $res = $this->post('/logout');

        // 3. ログアウト処理が実行される
        $res->assertRedirect('/'); // 実装によっては '/login' や他のページの場合あり
        $this->assertGuest();
    }
}
