<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 支払い方法（カード/コンビニ）
            'payment_method' => ['required', 'in:card,konbini'],

            // 配送先（アドレスIDなどを想定）
            // addressesテーブルがある場合は exists を有効化してください
            'shipping_address_id' => ['required', 'integer'/*, 'exists:addresses,id'*/],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_method'      => '支払い方法',
            'shipping_address_id' => '配送先',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => ':attributeは必ず選択してください。',
            'payment_method.in'       => ':attributeの値が不正です。（card / konbini）',

            'shipping_address_id.required' => ':attributeは必ず選択してください。',
            'shipping_address_id.integer'  => ':attributeの指定が不正です。',
            // 'shipping_address_id.exists'   => ':attributeの指定が不正です。',
        ];
    }
}