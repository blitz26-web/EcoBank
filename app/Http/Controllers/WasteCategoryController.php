<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWasteCategoryRequest;
use App\Models\WasteCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * CRUD master data kategori sampah — hanya admin.
 *
 * Katalog kategori menentukan tarif poin per satuan; perubahan tarif
 * HANYA memengaruhi setoran yang dibuat SETELAH perubahan, karena
 * points_earned tersimpan permanen di deposit_items (snapshot historis).
 *
 * Hapus kategori yang masih dipakai deposit_items diblokir oleh FK
 * restrictOnDelete di level database; controller menangkap QueryException
 * dan menerjemahkannya menjadi pesan yang ramah pengguna.
 */
class WasteCategoryController extends Controller
{
    /**
     * Daftar kategori + jumlah pemakaian + form modal tambah/ubah.
     */
    public function index(): View
    {
        $categories = WasteCategory::query()
            ->withCount('depositItems')
            ->orderBy('name')
            ->paginate(15);

        return view('waste-categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Simpan kategori baru.
     */
    public function store(StoreWasteCategoryRequest $request): RedirectResponse
    {
        WasteCategory::query()->create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-created');
    }

    /**
     * Perbarui kategori (nama, satuan, tarif poin).
     */
    public function update(StoreWasteCategoryRequest $request, WasteCategory $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-updated');
    }

    /**
     * Hapus kategori; ditolak bila masih dipakai data setoran.
     */
    public function destroy(WasteCategory $kategori): RedirectResponse
    {
        Gate::authorize('delete', $kategori);

        try {
            $kategori->delete();
        } catch (QueryException) {
            return back()->with('error', sprintf(
                'Kategori "%s" masih dipakai oleh data setoran sehingga tidak dapat dihapus. Nonaktifkan dengan mengubah tarifnya bila tidak lagi digunakan.',
                $kategori->name,
            ));
        }

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-deleted');
    }
}
