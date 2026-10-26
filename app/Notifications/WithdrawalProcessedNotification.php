<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalProcessedNotification extends Notification
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
        return (new MailMessage())
            ->subject('Penarikan Diproses - Kas Hijau')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line(sprintf('Penarikan %s telah diproses dan dibayarkan.', $this->withdrawal->withdrawal_code))
            ->line(sprintf('Jumlah: Rp %s', number_format((float) $this->withdrawal->cash_amount, 0, ',', '.')))
            ->line(sprintf('Metode: %s%s', $this->withdrawal->paymentLabel(), $this->withdrawal->account_number !== null ? ' - '.$this->withdrawal->account_number : ''))
            ->action('Lihat Riwayat Penarikan', route('withdrawals.index'))
            ->salutation('Salam hangat, Tim Kas Hijau');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Penarikan Diproses',
            'message' => sprintf(
                'Penarikan %s dibayarkan: Rp %s via %s.',
                $this->withdrawal->withdrawal_code,
                number_format((float) $this->withdrawal->cash_amount, 0, ',', '.'),
                $this->withdrawal->paymentLabel(),
            ),
            'url' => route('withdrawals.index'),
            'icon' => 'bi-cash-coin',
            'tone' => 'success',
        ];
    }
}
