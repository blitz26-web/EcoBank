<?php

declare(strict_types=1);

/*
 * Konfigurasi Kas Hijau.
 * Salin ke config/kas-hijau.php pada project Anda; Laravel otomatis memuat
 * seluruh file di folder config/, tanpa perlu vendor:publish.
 * Semua nilai dapat dioverride lewat .env.
 */

return [

    /*
    |----------------------------------------------------------------------
    | Nilai Tukar Poin
    |----------------------------------------------------------------------
    |
    | Nilai Rupiah untuk 1 (satu) poin. Dipakai WithdrawalService saat
    | menghitung cash_amount: cash_amount = points_amount x point_value.
    | Rate ini TIDAK pernah diterima dari sisi klien agar tidak bisa
    | dimanipulasi.
    |
    | Contoh: 1 kg Botol PET = 10 poin x Rp 100 = Rp 1.000.
    */
    'point_value' => (int) env('KAS_HIJAU_POINT_VALUE', 100),

    /*
    |----------------------------------------------------------------------
    | Batas Penarikan
    |----------------------------------------------------------------------
    |
    | Batas jumlah poin per pengajuan penarikan. Isi 0 untuk menonaktifkan
    | batas (min 0 = tanpa minimum selain harus > 0; max 0 = tanpa maksimum).
    */
    'min_withdrawal_points' => (int) env('KAS_HIJAU_MIN_WITHDRAWAL_POINTS', 100),
    'max_withdrawal_points' => (int) env('KAS_HIJAU_MAX_WITHDRAWAL_POINTS', 0),

];
