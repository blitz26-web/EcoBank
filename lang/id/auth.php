<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Baris bahasa autentikasi (id)
|--------------------------------------------------------------------------
| Dipakai oleh trans('auth.failed'), trans('auth.throttle'), dan kunci
| terkait. Aktifkan dengan APP_LOCALE=id di file .env.
*/

return [
    'failed' => 'Kredensial ini tidak cocok dengan catatan kami.',
    'password' => 'Kata sandi yang diberikan tidak benar.',
    'throttle' => 'Terlalu banyak percobaan masuk. Coba lagi dalam :seconds detik.',
];
