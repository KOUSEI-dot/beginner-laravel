<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function postCommentRoute($itemId): string
    {
        return route('items.comment', ['item_id' => $itemId]);
    }

    private function showRoute($itemId): string
    {
        return route('item.show', ['item_id' => $itemId]);
    }

    /** @test */
    public function ログイン済みのユーザーはコメントを送信でき_コメント数が増える()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create(['is_listed' => true]);

        $this->actingAs($user);

        $this->assertDatabaseCount('comments', 0);

        $res = $this->post($this->postCommentRoute($item->id), [
            'comment' => 'とても良い商品でした！',
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'text'    => 'とても良い商品でした！',
        ]);

        // 画面でもコメント数が増えていることを確認（どこかに「コメント (1)」と出る想定）
        $res = $this->get($this->showRoute($item->id));
        $res->assertOk();
        $res->assertSee('コメント (1)');
        $res->assertSee('とても良い商品でした！');
    }

    /** @test */
    public function 未ログインユーザーはコメントを送信できない()
    {
        $item = Item::factory()->create(['is_listed' => true]);

        $res = $this->post($this->postCommentRoute($item->id), [
            'comment' => 'ゲストの投稿は不可',
        ]);

        // authミドルウェアによりログインへリダイレクト
        $res->assertStatus(302);
        $res->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'text'    => 'ゲストの投稿は不可',
        ]);
    }

    /** @test */
    public function コメント未入力はバリデーションエラー()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create(['is_listed' => true]);

        $this->actingAs($user);

        $res = $this->from($this->showRoute($item->id))
                    ->post($this->postCommentRoute($item->id), [
                        'comment' => '',
                    ]);

        $res->assertStatus(302);
        $res->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('comments', 0);
    }

    /** @test */
    public function comment_over_255_chars_is_invalid()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create(['is_listed' => true]);

        $this->actingAs($user);

        $tooLong = str_repeat('あ', 256);

        $res = $this->from($this->showRoute($item->id))
                    ->post($this->postCommentRoute($item->id), [
                        'comment' => $tooLong,
                    ]);

        $res->assertStatus(302);
        $res->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('comments', 0);
    }
}
