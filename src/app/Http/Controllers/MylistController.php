<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // ★ 追加
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Like;

class MylistController extends Controller
{
    public function index(Request $request)
    {
        $tab     = $request->query('tab', 'recommend');
        $keyword = trim((string) $request->query('keyword', ''));

        $baseQuery = Item::query()
            ->when(Auth::check(), fn ($q) => $q->where('user_id', '!=', Auth::id()))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            })
            ->latest('id');

        $allItems = $baseQuery->get();

        $purchasedIds = Purchase::pluck('item_id')->toArray();

        $toView = function ($items) use ($purchasedIds) {
            return $items->map(function (Item $i) use ($purchasedIds) {

                $path = (string) $i->img_url;

                // すでに http(s) or /storage ならそのまま、相対なら Storage::url に通す
                if ($path && !str_starts_with($path, 'http') && !str_starts_with($path, '/storage')) {
                    $path = Storage::url($path);
                }

                $isSold = (!$i->is_listed) || in_array($i->id, $purchasedIds, true);

                return [
                    'id'      => $i->id,
                    'img_url' => $path,
                    'name'    => $i->name,
                    'sold'    => $isSold,
                ];
            });
        };

        $recommendItems = $toView($allItems);

        if (Auth::check()) {
            $likedIds    = Like::where('user_id', Auth::id())->pluck('item_id')->toArray();
            $mylistBase  = $allItems->whereIn('id', $likedIds);
            $mylistItems = $toView($mylistBase);
        } else {
            $mylistItems = collect();
        }

        return view('mylist.index', compact('tab', 'keyword', 'recommendItems', 'mylistItems'));
    }

    public function guestRecommend()
    {
        return redirect()->route('mylist.index', ['tab' => 'recommend']);
    }
}
