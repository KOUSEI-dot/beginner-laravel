<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Purchase;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ja_JP');

        // 1pxのPNGを作る（公開用ストレージに保存し、相対パスを返す）
        $makeImage = function (): string {
            $png1x1 = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='
            );
            $name = 'item_images/'.Str::random(32).'.png';
            Storage::disk('public')->put($name, $png1x1);
            return $name; // ← DBには相対パスを保存（ItemControllerで Storage::url() に変換）
        };

        // カテゴリ候補
        $categoriesPool = ['ファッション','家電','インテリア','レディース','メンズ','コスメ','本','ゲーム','スポーツ','キッチン','ハンドメイド','アクセサリー','おもちゃ','ベビー・キッズ'];
        $conditions = ['良好','目立った傷や汚れなし','やや傷や汚れあり','状態が悪い'];

        // 管理用ユーザー（ログイン用に使える）
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => '管理者',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'postal_code'       => '100-0001',
                'address'           => '東京都千代田区千代田1-1',
                'building'          => '管理ビル 101',
                'profile_image'     => null,
            ]
        );

        // 一般ユーザーを複数
        // 一般ユーザーを複数（冪等）
        $users = collect([$admin]);
        for ($i = 0; $i < 10; $i++) {
            $users->push(
            User::updateOrCreate(
                ['email' => "user{$i}@example.com"],
                [
                    'name'              => $faker->name(),
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                    'postal_code'       => $faker->numerify('###-####'),
                    'address'           => $faker->prefecture().$faker->city().$faker->streetAddress(),
                    'building'          => $faker->optional()->secondaryAddress() ?: '',
                    'profile_image'     => null,
                ])
            );
        }

        // 各ユーザー3件ずつ出品
        $items = collect();
        foreach ($users as $u) {
            for ($k = 0; $k < 3; $k++) {
                $picked = $faker->randomElements($categoriesPool, rand(1, 3));
                $items->push(
                    Item::create([
                        'user_id'     => $u->id,
                        'is_listed'   => true,
                        'name'        => $faker->words(rand(2, 4), true),
                        'brand'       => $faker->optional()->company(),
                        'description' => $faker->realText(120),
                        'price'       => $faker->numberBetween(800, 30000),
                        'condition'   => $faker->randomElement($conditions),
                        'img_url'     => $makeImage(),                           // 相対パス
                        'categories'  => implode(',', $picked),                  // CSV保存
                    ])
                );
            }
        }

        // ランダムにコメント
        for ($i = 0; $i < 60; $i++) {
            $user = $users->random();
            $item = $items->random();
            // 自分の出品にコメントしてもOKにするならこのまま。除外するなら if ($item->user_id === $user->id) continue;
            Comment::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'text'    => $faker->realText(rand(20, 80)),
            ]);
        }

        // ランダムにいいね
        for ($i = 0; $i < 120; $i++) {
            $user = $users->random();
            $item = $items->random();
            Like::firstOrCreate([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);
        }

        // 一部を購入済みにする（SOLDにする）
        $purchased = $items->random( min(8, $items->count()) );
        foreach ($purchased as $it) {
            // 出品者以外を購入者に
            $buyer = $users->where('id','!=',$it->user_id)->random();
            Purchase::firstOrCreate(
                ['user_id' => $buyer->id, 'item_id' => $it->id],
                ['payment_method' => 'card']
            );
            $it->update(['is_listed' => false]);
        }
    }
}
