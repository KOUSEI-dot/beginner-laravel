<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForceFirstLandingToMylist
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('MIDDLEWARE', ['flag'=>session('just_logged_in'), 'url'=>$request->fullUrl()]);

        // 1) 認証済み かつ just_logged_in=true の間は ② に固定
        if (\Auth::check() && session('just_logged_in') === true) {
            // ②（/mylist?tab=mylist）に到達したら、ここでフラグを消して以後は通常遷移
            if ($request->routeIs('mylist.index') && $request->query('tab') === 'mylist') {
                session()->forget('just_logged_in');
                return $next($request);
            }

            // ② 以外に向かっているなら必ず ② へ
            return redirect()->route('mylist.index', ['tab' => 'mylist']);
        }

        return $next($request);
    }

}
