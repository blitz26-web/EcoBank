<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WasteCategory;

/**
 * Aturan akses master data kategori sampah.
 *
 * Katalog kategori boleh dibaca semua peran (dipakai form setoran dan
 * daftar tarif poin), tetapi perubahan data hanya oleh admin.
 */
class WasteCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WasteCategory $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, WasteCategory $category): bool
    {
        return $user->isAdmin();
    }

    /**
     * Kategori yang sudah dipakai deposit_items otomatis diblokir oleh
     * FK restrictOnDelete di level database; policy ini hanya gerbang UI.
     */
    public function delete(User $user, WasteCategory $category): bool
    {
        return $user->isAdmin();
    }
}
