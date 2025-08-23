<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'is_listed'   => true,
            'name'        => $this->faker->words(3, true),
            'brand'       => $this->faker->company(),
            'price'       => $this->faker->numberBetween(1000, 50000),
            'description' => $this->faker->sentence(),
            'img_url'     => $this->faker->imageUrl(600, 600, 'fashion', true),
            'condition'   => $this->faker->randomElement(['new','like-new','used']),
            // casts未設定でも安全に入るように文字列JSONにしておく
            'categories'  => json_encode([$this->faker->randomElement(['tops','shoes','bag'])]),
        ];
    }

    /** 売切れ（Sold）表現したいとき用の状態 */
    public function sold(): self
    {
        return $this->state(fn () => ['is_listed' => false]);
    }
}
