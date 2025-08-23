<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Item;

class DummyItemSeeder extends Seeder
{
    public function run(): void
    {
        // 出品者（なければ作る）
        $seller = User::first() ?? User::factory()->create([
            'name'  => 'ダミー出品者',
            'email' => 'dummy-seller@example.com',
        ]);

        $rows = config('dummy_items', []);

        foreach ($rows as $row) {
            // 画像パス（config は /storage/xxx.jpg 想定）
            $srcUrl = ltrim($row['img_url'] ?? '', '/'); // storage/xxx.jpg
            $srcAbs = public_path($srcUrl);              // public/storage/xxx.jpg

            // 保存先（DBには相対パスで 'item_images/xxx.jpg' を保存）
            $filename = basename($srcUrl);
            $destRel  = 'item_images/'.$filename;               // ← DBに入れる相対パス
            $destAbs  = Storage::disk('public')->path($destRel); // storage/app/public/item_images/xxx.jpg

            // public/storage から storage/app/public/item_images へコピー（なければスキップ可）
            if (is_readable($srcAbs) && !file_exists($destAbs)) {
                // ディレクトリ作成
                if (!is_dir(dirname($destAbs))) {
                    mkdir(dirname($destAbs), 0775, true);
                }
                copy($srcAbs, $destAbs);
            }

            // カテゴリはCSVに正規化
            $categories = $row['categories'] ?? [];
            if (is_array($categories)) {
                $categoriesCsv = implode(',', $categories);
            } else {
                $categoriesCsv = (string) $categories;
            }

            // 既存なら更新・なければ作成（名前＋価格＋出品者でユニークとみなす）
            Item::updateOrCreate(
                [
                    'user_id' => $seller->id,
                    'name'    => $row['name'] ?? '未設定',
                    'price'   => $row['price'] ?? 0,
                ],
                [
                    'is_listed'   => true,
                    'brand'       => $row['brand'] ?? null,
                    'description' => $row['description'] ?? '',
                    'condition'   => $row['condition'] ?? '良好',
                    'img_url'     => $destRel,         // ★ 相対パスを保存
                    'categories'  => $categoriesCsv,   // CSV
                ]
            );
        }
    }
}
