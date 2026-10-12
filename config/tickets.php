<?php

return [
    /*
    | Kode departemen yang menangani tiket (layanan IT).
    | User di departemen ini dengan level >= triage_min_level dapat memilah & meng-assign tiket.
    */
    'department_code' => env('TICKET_DEPARTMENT_CODE', 'IT'),

    'triage_min_level' => 4,

    /*
    | Hari kerja layanan IT (ISO: 1 = Senin ... 7 = Minggu) dan jam kerja.
    */
    'working_days' => [1, 2, 3, 4, 5, 6],
    'working_hours' => ['start' => '06:00', 'end' => '17:00'],

    // Tiket berstatus "resolved" ditutup otomatis jika pemohon tidak konfirmasi dalam N hari kerja
    'auto_close_working_days' => 3,
];
