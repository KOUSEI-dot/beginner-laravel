<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Http\Requests\ExhibitionRequest;

class ProductController extends Controller
{
    public function __construct()
    {

    }

    // 出品画面
    public function create()
    {
        return view('sell');
    }

    // 出品処理
    public function store(ExhibitionRequest $request)
    {
        $v = $request->validated();

        // 画像保存（public ディスク）
        $path = $request->file('image')->store('item_images', 'public');

        // カテゴリCSV化
        $categoriesCsv = '';
        if (isset($v['categories']) && is_array($v['categories'])) {
            $categoriesCsv = implode(',', $v['categories']);
        } elseif (isset($v['category'])) {
            $categoriesCsv = (string) $v['category'];
        }

        Item::create([
            'user_id'     => Auth::id(),
            'is_listed'   => true,
            'name'        => $v['name'],
            'brand'       => $v['brand'] ?? null,
            'description' => $v['description'],
            'price'       => $v['price'],
            'condition'   => $v['condition'],
            'img_url'     => $path,
            'categories'  => $categoriesCsv,
        ]);

        return redirect()->route('mypage')->with('success', '商品を出品しました。');
    }
}
