<?php

return [
    /*
    | Kode departemen yang menangani tiket (layanan IT).
    | User di departemen ini dengan level >= triage_min_level dapat memilah & meng-assign tiket.
    */
    'department_code' => env('TICKET_DEPARTMENT_CODE', 'IT'),

    'triage_min_level' => 4,
];
