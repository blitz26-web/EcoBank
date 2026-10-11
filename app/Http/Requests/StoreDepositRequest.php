<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Deposit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form setoran sampah (dua mode):
 *
 *  1. Warga mengajukan setoran mandiri  -> tanpa user_id, status Pending.
 *  2. Petugas/admin mencatat di loket   -> user_id wajib (pemilik rekening),
 *     status Completed, poin langsung masuk.
 *
 * Pemisahan tanggung jawab validasi:
 *  - FormRequest (HTTP layer) : bentuk data (array items, kategori ada,
 *    berat > 0, user_id milik warga).
 *  - DepositService (bisnis)  : perhitungan poin ulang di server,
 *    transaksi + row lock, pencegahan status awal tidak sah.
 */
class StoreDepositRequest extends FormRequest
{
    /** Batas jumlah baris item di form (selaras guard JS di view). */
    public const MAX_ITEMS = 10;

    public function authorize(): bool
    {
        // DepositPolicy::create() — warga (mandiri) & petugas (loket); admin lolos via Gate::before.
        return $this->user()->can('create', Deposit::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = [
            'items' => ['required', 'array', 'min:1', 'max:' . self::MAX_ITEMS],
            'items.*.waste_category_id' => ['required', 'integer', Rule::exists('waste_categories', 'id')],
            'items.*.weight_in_kg' => ['required', 'numeric', 'gt:0', 'max:10000'],
        ];

        /*
         * Warga TIDAK boleh mengirim user_id (selalu pemilik akunnya sendiri
         * — anti manipulasi kredit ke akun lain). Petugas/admin WAJIB
         * memilih warga sebagai pemilik rekening setoran.
         */
        $rules['user_id'] = $this->user()->isWarga()
            ? ['prohibited']
            : ['required', 'integer', Rule::exists('users', 'id')->where('role', UserRole::Warga->value)];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Minimal satu jenis sampah harus diisi.',
            'items.array' => 'Format rincian sampah tidak valid.',
            'items.min' => 'Minimal satu jenis sampah harus diisi.',
            'items.max' => 'Maksimal :max jenis sampah per setoran.',
            'items.*.waste_category_id.required' => 'Setiap baris wajib memilih jenis sampah.',
            'items.*.waste_category_id.integer' => 'Jenis sampah tidak valid.',
            'items.*.waste_category_id.exists' => 'Jenis sampah yang dipilih tidak ditemukan.',
            'items.*.weight_in_kg.required' => 'Berat setiap jenis sampah wajib diisi.',
            'items.*.weight_in_kg.numeric' => 'Berat harus berupa angka.',
            'items.*.weight_in_kg.gt' => 'Berat harus lebih besar dari nol.',
            'items.*.weight_in_kg.max' => 'Berat maksimal 10.000 satuan per jenis.',
            'user_id.prohibited' => 'Warga tidak dapat menentukan pemilik rekening setoran.',
            'user_id.required' => 'Warga pemilik setoran wajib dipilih.',
            'user_id.integer' => 'Warga pemilik setoran tidak valid.',
            'user_id.exists' => 'Warga pemilik setoran tidak ditemukan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
         * Buang baris yang sama sekali kosong (pengguna menambah baris
         * tetapi tidak mengisi), normalisasi desimal koma -> titik.
         */
        $cleaned = [];

        foreach ((array) $this->input('items', []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $categoryId = trim((string) ($item['waste_category_id'] ?? ''));
            $weight = trim((string) ($item['weight_in_kg'] ?? ''));

            if ($categoryId === '' && $weight === '') {
                continue;
            }

            $cleaned[] = [
                'waste_category_id' => $categoryId,
                'weight_in_kg' => str_replace(',', '.', $weight),
            ];
        }

        $this->merge(['items' => $cleaned]);
    }
}
