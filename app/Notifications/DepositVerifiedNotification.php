<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Deposit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositVerifiedNotification extends Notification
{
    public function __construct(private readonly Deposit $deposit)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $saldo = (int) $notifiable->refresh()->balance_points;

        return (new MailMessage())
            ->subject('Setoran Diverifikasi - Kas Hijau')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line(sprintf('Setoran %s sudah diverifikasi petugas.', $this->deposit->deposit_code))
            ->line(sprintf('Poin masuk: %s poin.', number_format((int) $this->deposit->total_points, 0, ',', '.')))
            ->line(sprintf('Saldo poin terkini: %s poin.', number_format($saldo, 0, ',', '.')))
            ->action('Lihat Riwayat Setoran', route('deposits.index'))
            ->salutation('Salam hangat, Tim Kas Hijau');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Setoran Diverifikasi',
            'message' => sprintf(
                'Setoran %s disetujui. %s poin masuk ke saldo Anda.',
                $this->deposit->deposit_code,
                number_format((int) $this->deposit->total_points, 0, ',', '.'),
            ),
            'url' => route('deposits.index'),
            'icon' => 'bi-check-circle-fill',
            'tone' => 'success',
        ];
    }
}
