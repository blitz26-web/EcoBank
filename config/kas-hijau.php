<?php

declare(strict_types=1);

return [

    // Nilai rupiah untuk 1 poin.
    'point_value' => (int) env('KAS_HIJAU_POINT_VALUE', 100),

    // Batas jumlah poin per pengajuan penarikan, 0 berarti tanpa batas.
    'min_withdrawal_points' => (int) env('KAS_HIJAU_MIN_WITHDRAWAL_POINTS', 100),
    'max_withdrawal_points' => (int) env('KAS_HIJAU_MAX_WITHDRAWAL_POINTS', 0),

];
