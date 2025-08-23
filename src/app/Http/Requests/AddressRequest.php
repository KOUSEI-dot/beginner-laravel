<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // ★ 送信値を検証前に正規化
    protected function prepareForValidation(): void
    {
        $pc = (string) ($this->postal_code ?? '');

        // 全角数字 → 半角数字
        $pc = mb_convert_kana($pc, 'n'); // １２３→123

        // いろいろなハイフン類を半角ハイフンに統一
        $pc = preg_replace('/[‐-‒–—―ー−﹣－]/u', '-', $pc);

        // 数字以外を除去して7桁なら 123-4567 形式に整形
        $digits = preg_replace('/\D/', '', $pc);
        if (strlen($digits) === 7) {
            $pc = substr($digits, 0, 3) . '-' . substr($digits, 3);
        }

        $this->merge(['postal_code' => $pc]);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string'],
            // ハイフンは半角「-」、上の正規化で揃えてから検証
            'postal_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address'     => ['required', 'string'],
            'building'    => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'お名前',
            'postal_code' => '郵便番号',
            'address'     => '住所',
            'building'    => '建物名',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => ':attributeは必ず入力してください。',
            'postal_code.required'  => ':attributeは必ず入力してください。',
            'postal_code.regex'     => ':attributeは「123-4567」の形式で入力してください。',
            'address.required'      => ':attributeは必ず入力してください。',
        ];
    }
}
