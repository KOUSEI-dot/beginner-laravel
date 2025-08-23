<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_listed',
        'name',
        'brand',
        'price',
        'description',
        'img_url',
        'condition',
        'categories',
    ];

    // JSON保存でも自動で配列として扱えるように
    protected $casts = [
        'id'        => 'integer',
        'user_id'   => 'integer',
        'price'     => 'integer',
        'is_listed' => 'boolean',
        'categories' => 'array',
    ];

    // リレーション（最低限）
    public function likes()
    {
        return $this->hasMany(\App\Models\Like::class);
    }

    public function comments()
    {
        return $this->hasMany(\App\Models\Comment::class);
    }

    public function purchases()
    {
        return $this->hasMany(\App\Models\Purchase::class);
    }

}
