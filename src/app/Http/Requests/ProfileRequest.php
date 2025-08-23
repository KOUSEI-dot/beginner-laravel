<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // 郵便番号を検証前に正規化（全角→半角、いろいろなハイフン→-、7桁なら 123-4567）
    protected function prepareForValidation(): void
    {
        $pc = (string) ($this->postal_code ?? '');
        $pc = mb_convert_kana($pc, 'n');                              // １２３→123
        $pc = preg_replace('/[‐-‒–—―ー−﹣－]/u', '-', $pc);           // 各種ハイフンを半角-に統一
        $digits = preg_replace('/\D/', '', $pc);
        if (strlen($digits) === 7) {
            $pc = substr($digits, 0, 3) . '-' . substr($digits, 3);
        }
        $this->merge(['postal_code' => $pc]);
    }

    public function rules(): array
    {
        return [
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png'],
            'name'          => ['required', 'string', 'max:255'],
            'postal_code'   => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address'       => ['required', 'string'],
            'building'      => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'profile_image' => 'プロフィール画像',
            'name'          => 'お名前',
            'postal_code'   => '郵便番号',
            'address'       => '住所',
            'building'      => '建物名',
        ];
    }

    public function messages(): array
    {
        return [
            'profile_image.image' => ':attributeは画像ファイルを指定してください。',
            'profile_image.mimes' => ':attributeはJPEGまたはPNGを指定してください。',

            'name.required'        => ':attributeは必ず入力してください。',
            'postal_code.required' => ':attributeは必ず入力してください。',
            'postal_code.regex'    => ':attributeは「123-4567」の形式で入力してください。',
            'address.required'     => ':attributeは必ず入力してください。',
        ];
    }
}
