<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_to_guest_recommend(): void
    {
        // 現在の仕様に合わせる
        $this->get('/')->assertRedirect('/guest/recommend');

        // 追従して最終200を確認したい場合は次でもOK
        // $this->followingRedirects()->get('/')->assertOk();
    }
}
