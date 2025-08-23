<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Purchase;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /** 商品一覧 */
    public function index(Request $request)
    {
        $items = Item::query()
            ->when(Auth::check(), fn ($q) => $q->where('user_id', '!=', Auth::id()))
            ->latest('id')
            ->get();

        return view('items.index', compact('items'));
    }

    /** 商品詳細（配列VMで渡す） */
    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);

        // 売却判定
        $isPurchased = (!$item->is_listed) || Purchase::where('item_id', $item->id)->exists();

        // カテゴリ正規化
        $categories = $item->categories;
        if (!is_array($categories)) {
            $decoded = json_decode((string) $categories, true);
            if (is_array($decoded)) {
                $categories = $decoded;
            } else {
                $categories = array_values(array_filter(array_map('trim', explode(',', (string) $categories))));
            }
        }

        // いいね・コメント
        $likesCount = Like::where('item_id', $item->id)->count();
        $likedByMe  = Auth::check() && Like::where('user_id', Auth::id())->where('item_id', $item->id)->exists();

        $comments   = Comment::where('item_id', $item->id)
                        ->with('user:id,name')
                        ->latest()
                        ->get();

        $commentsVm = $comments->map(fn ($c) => [
            'user' => $c->user->name ?? '－',
            'text' => $c->text ?? '',
        ])->toArray();

        // 画像URL（外部URL/ストレージ両対応）
        $raw = (string) $item->img_url;
        if (preg_match('#^https?://#i', $raw)) {
            $finalImgUrl = $raw; // 既に絶対URL
        } else {
            $raw = ltrim($raw, '/');
            if (strpos($raw, 'storage/') === 0) {
                $finalImgUrl = '/'.$raw; // 公開パス
            } else {
                $finalImgUrl = Storage::url($raw); // "item_images/xxx.jpg" -> "/storage/item_images/xxx.jpg"
            }
        }

        // Blade 用VM
        $vm = [
            'id'             => $item->id,
            'img_url'        => $finalImgUrl,
            'name'           => $item->name,
            'brand'          => $item->brand,
            'price'          => $item->price,
            'description'    => $item->description,
            'categories'     => $categories,
            'condition'      => $item->condition,
            'likes_count'    => $likesCount,
            'liked_by_me'    => $likedByMe,
            'comments_count' => $comments->count(),
            'comments'       => $commentsVm,
            'is_purchased'   => $isPurchased,
        ];

        // レイアウト側で参照されても安全なように空配列も渡す
        return view('items.show', [
            'item'            => $vm,
            'user'            => Auth::user(),
            'mylistItems'     => [],
            'recommendItems'  => [],
        ]);
    }

    /** コメント投稿 */
    public function postComment(CommentRequest $request, $item_id)
    {
        $v = $request->validated();

        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
            'text'    => $v['comment'],
        ]);

        return back()->with('success', 'コメントを投稿しました。');
    }

    /** いいね：トグル（未→登録 / 済→解除） */
    public function toggleLike($item_id)
    {
        $user = Auth::user();

        $like = Like::where('user_id', $user->id)
                    ->where('item_id', $item_id)
                    ->first();

        if ($like) {
            $like->delete();
            $msg = 'いいねを解除しました。';
        } else {
            Like::create([
                'user_id' => $user->id,
                'item_id' => $item_id,
            ]);
            $msg = 'いいねしました。';
        }

        return back()->with('success', $msg);
    }

    // 互換のための薄いラッパ（不要なら削除OK）
    public function like($item_id)   { return $this->toggleLike($item_id); }
    public function unlike($item_id) { return $this->toggleLike($item_id); }
}
