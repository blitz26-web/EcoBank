<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_code',
        'user_id',
        'officer_id',
        'total_points',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'integer',
            'status' => DepositStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DepositItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === DepositStatus::Pending;
    }

    public function isCompleted(): bool
    {
        return $this->status === DepositStatus::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === DepositStatus::Cancelled;
    }
}
