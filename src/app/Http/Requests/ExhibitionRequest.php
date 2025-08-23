<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string'],                 // 商品名（必須）
            'brand'       => ['nullable', 'string', 'max:255'],      // 任意
            'description' => ['required', 'string', 'max:255'],      // 商品説明（255文字以内）
            'image'       => ['required', 'image', 'mimes:jpeg,png'],// 画像（必須・jpeg/png）

            // カテゴリー（複数選択・1件以上）
            'categories'   => ['required','array','min:1'],
            'categories.*' => ['string','max:50'],

            'condition'   => ['required', 'string'],                 // 状態（必須）
            'price'       => ['required', 'integer', 'min:0'],       // 価格（整数・0円以上）
        ];
    }

    public function attributes(): array
    {
        return [
            'name'           => '商品名',
            'brand'          => 'ブランド名',
            'description'    => '商品説明',
            'image'          => '商品画像',
            'categories'     => '商品のカテゴリー',
            'categories.*'   => 'カテゴリー項目',
            'condition'      => '商品の状態',
            'price'          => '商品価格',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => ':attributeは必ず入力してください。',

            'description.required' => ':attributeは必ず入力してください。',
            'description.max'      => ':attributeは:max文字以内で入力してください。',

            'image.required' => ':attributeは必ずアップロードしてください。',
            'image.image'    => ':attributeは画像ファイルを指定してください。',
            'image.mimes'    => ':attributeはJPEGまたはPNGを指定してください。',

            'categories.required' => ':attributeは必ず選択してください。',
            'categories.array'    => ':attributeの形式が不正です。',
            'categories.min'      => ':attributeは少なくとも1つ選択してください。',
            'categories.*.string' => ':attributeは文字列で入力してください。',
            'categories.*.max'    => ':attributeは:max文字以内で入力してください。',

            'condition.required' => ':attributeは必ず選択してください。',

            'price.required' => ':attributeは必ず入力してください。',
            'price.integer'  => ':attributeは整数で入力してください。',
            'price.min'      => ':attributeは:min以上で入力してください。',
        ];
    }
}
