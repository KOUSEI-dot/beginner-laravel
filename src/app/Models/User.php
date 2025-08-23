<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 更新可能な属性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'postal_code',
        'address',
        'building',
        'profile_completed',
    ];

    /**
     * シリアライズ時に隠す属性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * キャスト
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'profile_completed' => 'bool',
    ];

    /**
     * リレーション例
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * プロフィール完成判定（共通化）
     * - フラグが true なら完成
     * - そうでなければ必須項目で簡易判定（必要に応じて増やしてOK）
     */
    public function isProfileCompleted(): bool
    {
        if (isset($this->profile_completed) && $this->profile_completed === true) {
            return true;
        }

        $required = ['name']; // 必要なら 'address', 'postal_code' など追加
        foreach ($required as $col) {
            if (empty($this->{$col})) {
                return false;
            }
        }
        return true;
    }
}
