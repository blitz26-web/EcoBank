<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi form pengajuan penarikan poin.
 *
 * Pemisahan tanggung jawab validasi:
 *  - FormRequest (HTTP layer) : bentuk data, daftar metode pembayaran,
 *    batas min/max dari config, nomor rekening wajib untuk non-tunai.
 *  - WithdrawalService (bisnis layer, di dalam DB::transaction):
 *    kecukupan saldo dengan row lock, konversi poin ke Rupiah,
 *    dan anti proses ganda.
 *
 * Pengecekan saldo sengaja TIDAK dilakukan di sini agar selalu dieksekusi
 * di dalam transaksi database bersama debit ledger — bebas race condition.
 */
class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        // WithdrawalPolicy::create() — hanya warga.
        return $this->user()->can('create', Withdrawal::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $methods = implode(',', app(WithdrawalService::class)->allowedPaymentMethods());

        $min = max(1, (int) config('kas-hijau.min_withdrawal_points', 1));
        $max = (int) config('kas-hijau.max_withdrawal_points', 0);

        $pointsRules = ['required', 'integer', 'min:' . $min];

        if ($max > 0) {
            $pointsRules[] = 'max:' . $max;
        }

        return [
            'points_amount' => $pointsRules,
            'payment_method' => ['required', 'string', 'in:' . $methods],
            'account_number' => [
                'nullable',
                'string',
                'max:50',
                'required_unless:payment_method,' . Withdrawal::PAYMENT_CASH,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'points_amount.required' => 'Jumlah poin yang ditarik wajib diisi.',
            'points_amount.integer' => 'Jumlah poin harus berupa bilangan bulat.',
            'points_amount.min' => 'Jumlah poin di bawah batas minimal penarikan.',
            'points_amount.max' => 'Jumlah poin melebihi batas maksimal penarikan.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'account_number.required_unless' => 'Nomor rekening/e-wallet wajib diisi untuk pembayaran non-tunai.',
            'account_number.max' => 'Nomor rekening/e-wallet maksimal 50 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'points_amount' => (int) $this->input('points_amount'),
        ]);
    }
}
