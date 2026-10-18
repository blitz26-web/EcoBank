<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notifikasi verifikasi email berbahasa Indonesia.
 *
 * Mewarisi VerifyEmail bawaan framework agar URL bertanda tangan (signed)
 * dan aturan kedaluwarsa tetap dikelola framework; hanya pesan email
 * yang dilokalkan.
 */
class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Bangun pesan email verifikasi.
     *
     * @param  mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Verifikasi Alamat Email Anda - Kas Hijau')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih telah mendaftar di Kas Hijau. Silakan verifikasi alamat email Anda untuk mengaktifkan akun dan mulai mengumpulkan poin dari sampah terpilah.')
            ->action('Verifikasi Email Sekarang', $verificationUrl)
            ->line('Tautan ini berlaku terbatas sesuai keamanan sistem. Jika tombol tidak berfungsi, salin dan tempel URL berikut ke peramban Anda:')
            ->line($verificationUrl)
            ->line('Jika Anda tidak merasa membuat akun Kas Hijau, abaikan atau hapus email ini.')
            ->salutation('Salam hangat, Tim Kas Hijau');
    }
}
