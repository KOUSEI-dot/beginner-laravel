<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Laravel\Fortify\Fortify;

// ↓ 追加：ログイン/登録後のレスポンスを上書きするための契約クラス
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ルートは自前（web.php など）で定義する想定ならそのまま
        Fortify::ignoreRoutes();

        // ログイン試行のレート制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . '|' . $request->ip());
        });

        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    $user = $request->user();

                    // --- プロフィール完了判定 ---
                    // プロジェクトに合わせてここを調整してください。
                    // 例1: profile_completed_at を使う場合
                    $completed = !is_null($user->profile_completed_at ?? null);

                    // 例2: 必須項目で簡易判定（name/address など）
                    if (!$completed) {
                        $required = [
                            // ここはあなたのDB項目に合わせて調整
                            'name',
                            // 'address',
                            // 'postal_code',
                            // 'avatar_path',
                        ];
                        $filledAll = true;
                        foreach ($required as $col) {
                            if (!isset($user->{$col}) || $user->{$col} === null || $user->{$col} === '') {
                                $filledAll = false;
                                break;
                            }
                        }
                        $completed = $filledAll;
                    }

                    // 未完了なら初回同様にプロフィール入力へ
                    if (!$completed) {
                        return redirect('/mypage/profile');
                    }

                    // 完了済みはマイリストへ
                    return redirect()->intended('/?page=mylist');
                }
            };
        });

        // 新規登録直後の遷移先を上書き（必ずプロフィールへ）
        $this->app->singleton(RegisterResponseContract::class, function () {
            return new class implements RegisterResponseContract {
                public function toResponse($request)
                {
                    return redirect()->route('verification.notice');
                }
            };
        });
    }
}
