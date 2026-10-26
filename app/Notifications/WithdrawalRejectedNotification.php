<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRejectedNotification extends Notification
{
    public function __construct(private readonly Withdrawal $withdrawal)
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
            ->subject('Penarikan Ditolak - Kas Hijau')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line(sprintf('Maaf, pengajuan penarikan %s ditolak petugas.', $this->withdrawal->withdrawal_code))
            ->line(sprintf('Alasan: %s', $this->withdrawal->rejected_reason))
            ->line(sprintf('Poin sebesar %s telah dikembalikan ke saldo Anda.', number_format((int) $this->withdrawal->points_amount, 0, ',', '.')))
            ->line(sprintf('Saldo poin terkini: %s poin.', number_format($saldo, 0, ',', '.')))
            ->action('Ajukan Penarikan Baru', route('withdrawals.create'))
            ->salutation('Salam hangat, Tim Kas Hijau');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Penarikan Ditolak',
            'message' => sprintf(
                'Pengajuan %s ditolak. %s poin telah kembali ke saldo Anda.',
                $this->withdrawal->withdrawal_code,
                number_format((int) $this->withdrawal->points_amount, 0, ',', '.'),
            ),
            'url' => route('withdrawals.index'),
            'icon' => 'bi-x-circle-fill',
            'tone' => 'danger',
        ];
    }
}
