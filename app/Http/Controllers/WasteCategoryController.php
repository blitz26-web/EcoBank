<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWasteCategoryRequest;
use App\Models\WasteCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class WasteCategoryController extends Controller
{
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

    public function store(StoreWasteCategoryRequest $request): RedirectResponse
    {
        WasteCategory::query()->create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-created');
    }

    public function update(StoreWasteCategoryRequest $request, WasteCategory $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-updated');
    }

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
