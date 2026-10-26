<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Withdrawal;
use Illuminate\Foundation\Http\FormRequest;

class RejectWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $withdrawal = $this->route('withdrawal');

        return $withdrawal !== null && $this->user()->can('reject', $withdrawal);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 5 karakter agar jelas untuk audit.',
            'reason.max' => 'Alasan penolakan maksimal 255 karakter.',
        ];
    }
}
