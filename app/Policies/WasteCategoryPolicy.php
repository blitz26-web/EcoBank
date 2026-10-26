<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WasteCategory;

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

    public function delete(User $user, WasteCategory $category): bool
    {
        return $user->isAdmin();
    }
}
