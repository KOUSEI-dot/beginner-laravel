<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'comment' => '商品コメント',
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => ':attributeは必ず入力してください。',
            'comment.string'   => ':attributeは文字列で入力してください。',
            'comment.max'      => ':attributeは:max文字以内で入力してください。',
        ];
    }
}