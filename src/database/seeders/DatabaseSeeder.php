<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Item;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザー（ログイン用）
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => '管理者',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 固定の商品リスト（docs/ に画像がある前提）
        $items = [
            [
                'name'        => '腕時計',
                'price'       => 15000,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'img_file'    => '時計.jpg',
                'condition'   => '良好',
                'categories'  => 'メンズ,アクセサリー',
            ],
            [
                'name'        => 'HDD',
                'price'       => 5000,
                'description' => '高速で信頼性の高いハードディスク',
                'img_file'    => 'HDD.jpg',
                'condition'   => '目立った傷や汚れなし',
                'categories'  => '家電',
            ],
            [
                'name'        => '玉ねぎ3束',
                'price'       => 300,
                'description' => '新鮮な玉ねぎ3束のセット',
                'img_file'    => '玉ねぎ３束.jpg',
                'condition'   => 'やや傷や汚れあり',
                'categories'  => 'キッチン',
            ],
            [
                'name'        => '革靴',
                'price'       => 4000,
                'description' => 'クラシックなデザインの革靴',
                'img_file'    => '革靴.jpg',
                'condition'   => '状態が悪い',
                'categories'  => 'ファッション,メンズ',
            ],
            [
                'name'        => 'ノートPC',
                'price'       => 45000,
                'description' => '高性能なノートパソコン',
                'img_file'    => 'ノートPC.jpg',
                'condition'   => '良好',
                'categories'  => '家電',
            ],
            [
                'name'        => 'マイク',
                'price'       => 8000,
                'description' => '高音質のレコーディング用マイク',
                'img_file'    => 'マイク.jpg',
                'condition'   => '目立った傷や汚れなし',
                'categories'  => '家電,おもちゃ',
            ],
            [
                'name'        => 'ショルダーバッグ',
                'price'       => 3500,
                'description' => 'おしゃれなショルダーバッグ',
                'img_file'    => 'ショルダーバッグ.jpg',
                'condition'   => 'やや傷や汚れあり',
                'categories'  => 'レディース,ハンドメイド',
            ],
            [
                'name'        => 'タンブラー',
                'price'       => 500,
                'description' => '使いやすいタンブラー',
                'img_file'    => 'タンブラー.jpg',
                'condition'   => '状態が悪い',
                'categories'  => 'スポーツ',
            ],
            [
                'name'        => 'コーヒーミル',
                'price'       => 4000,
                'description' => '手動のコーヒーミル',
                'img_file'    => 'コーヒーミル.jpg',
                'condition'   => '良好',
                'categories'  => 'キッチン',
            ],
            [
                'name'        => 'メイクセット',
                'price'       => 2500,
                'description' => '便利なメイクアップセット',
                'img_file'    => 'メイクセット.jpg',
                'condition'   => '目立った傷や汚れなし',
                'categories'  => 'レディース,コスメ',
            ],
        ];

        foreach ($items as $row) {
            // docs/ から storage/app/public にコピー
            $src = base_path('docs/'.$row['img_file']);
            $dest = $row['img_file'];

            if (file_exists($src)) {
                Storage::disk('public')->put($dest, file_get_contents($src));
            }

            // DB 登録（img_url は相対パス）
            Item::updateOrCreate(
                ['user_id' => $admin->id, 'name' => $row['name']],
                [
                    'user_id'     => $admin->id,
                    'is_listed'   => true,
                    'name'        => $row['name'],
                    'price'       => $row['price'],
                    'description' => $row['description'],
                    'condition'   => $row['condition'],
                    'img_url'     => $dest,
                    'categories'  => $row['categories'],
                ]
            );
        }
    }
}
