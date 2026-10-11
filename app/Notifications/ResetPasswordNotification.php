<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notifikasi atur ulang kata sandi berbahasa Indonesia.
 *
 * Mewarisi ResetPassword bawaan framework; token dan kedaluwarsa tetap
 * dikelola framework, hanya pesan email yang dilokalkan.
 */
class ResetPasswordNotification extends ResetPassword
{
    /**
     * Bangun pesan email atur ulang kata sandi.
     *
     * @param  mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60,
        );

        return (new MailMessage())
            ->subject('Atur Ulang Kata Sandi - EcoBank')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Anda menerima email ini karena kami menerima permintaan atur ulang kata sandi untuk akun EcoBank Anda.')
            ->action('Atur Ulang Kata Sandi', $resetUrl)
            ->line(sprintf(
                'Tautan ini kedaluwarsa dalam %d menit. Jika tombol tidak berfungsi, salin dan tempel URL berikut ke peramban Anda:',
                $expireMinutes,
            ))
            ->line($resetUrl)
            ->line('Jika Anda tidak meminta atur ulang kata sandi, tidak perlu melakukan apa pun - kata sandi Anda tetap aman.')
            ->salutation('Salam hangat, Tim EcoBank');
    }
}
