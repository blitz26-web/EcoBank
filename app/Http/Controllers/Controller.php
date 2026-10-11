<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Controller dasar EcoBank.
 *
 * Trait AuthorizesRequests menyediakan $this->authorize('ability', $model)
 * untuk memanggil policy; ValidatesRequests menyediakan $this->validate().
 * Semua controller aplikasi mewarisi kelas ini.
 */
abstract class Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
