<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WasteCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form kategori sampah (dipakai untuk tambah & ubah).
 *
 * Deteksi operasi: POST -> create, PUT/PATCH -> update (untuk authorize
 * policy dan aturan unique yang mengabaikan baris yang sedang diedit).
 */
class StoreWasteCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('POST') ? 'create' : 'update';

        // WasteCategoryPolicy — hanya admin; petugas/warga ditolak.
        return $this->user()->can($ability, WasteCategory::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        /** @var WasteCategory|null $kategori */
        $kategori = $this->route('kategori');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('waste_categories', 'name')->ignore($kategori?->getKey()),
            ],
            'unit' => ['required', 'string', 'max:20'],
            'point_per_unit' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori sudah dipakai.',
            'unit.required' => 'Satuan takaran wajib diisi.',
            'unit.max' => 'Satuan takaran maksimal 20 karakter.',
            'point_per_unit.required' => 'Poin per satuan wajib diisi.',
            'point_per_unit.integer' => 'Poin per satuan harus bilangan bulat.',
            'point_per_unit.min' => 'Poin per satuan tidak boleh negatif.',
            'point_per_unit.max' => 'Poin per satuan terlalu besar.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'unit' => strtolower(trim((string) $this->input('unit', WasteCategory::DEFAULT_UNIT))),
        ]);
    }
}
