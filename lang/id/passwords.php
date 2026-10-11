<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Baris bahasa reset kata sandi (id)
|--------------------------------------------------------------------------
| Kunci-kunci ini dikembalikan Password::sendResetLink()/reset() dan
| dirender lewat __(session('status')) / __($error) di views.
*/

return [
    'reset' => 'Kata sandi Anda telah diatur ulang.',
    'sent' => 'Kami telah mengirim tautan atur ulang kata sandi ke email Anda.',
    'throttled' => 'Mohon tunggu sebelum mencoba lagi.',
    'token' => 'Token atur ulang kata sandi ini tidak valid.',
    'user' => 'Kami tidak menemukan pengguna dengan alamat email tersebut.',
];
