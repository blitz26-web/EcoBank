<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class InsufficientPointsException extends RuntimeException
{
    public function __construct(string $message = 'Saldo poin tidak cukup untuk penarikan ini.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'insufficient_points',
            ], 422);
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['points_amount' => $this->getMessage()]);
    }
}
