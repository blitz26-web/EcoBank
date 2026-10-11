<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Withdrawal;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi form penolakan penarikan poin oleh petugas.
 *
 * Alasan penolakan WAJIB diisi karena akan tersimpan pada kolom
 * withdrawals.rejected_reason dan ikut tertulis pada keterangan entri
 * ledger pengembalian poin (audit trail).
 */
class RejectWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Withdrawal|null $withdrawal */
        $withdrawal = $this->route('withdrawal');

        // WithdrawalPolicy::reject() — hanya petugas & masih requested; admin lolos via Gate::before.
        return $withdrawal !== null && $this->user()->can('reject', $withdrawal);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 5 karakter agar jelas untuk audit.',
            'reason.max' => 'Alasan penolakan maksimal 255 karakter.',
        ];
    }
}
