<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'balance_points',
        'is_active',
        'savings_goal_title',
        'savings_goal_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'balance_points' => 'integer',
            'is_active' => 'boolean',
            'savings_goal_points' => 'integer',
        ];
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'user_id');
    }

    public function processedDeposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'officer_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function pointLedgers(): HasMany
    {
        return $this->hasMany(PointLedger::class);
    }

    public function isWarga(): bool
    {
        return $this->role === UserRole::Warga;
    }

    public function isPetugas(): bool
    {
        return $this->role === UserRole::Petugas;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function hasSufficientPoints(int $amount): bool
    {
        return $this->balance_points >= $amount;
    }

    public function hasSavingsGoal(): bool
    {
        return $this->savings_goal_points !== null && $this->savings_goal_points > 0;
    }

    public function savingsGoalPercent(): int
    {
        if (! $this->hasSavingsGoal()) {
            return 0;
        }

        return min(100, (int) floor(($this->balance_points / $this->savings_goal_points) * 100));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
