@include('errors.shell', [
    'code' => 503,
    'title' => 'Sedang pemeliharaan',
    'message' => 'SiGAP sedang dalam pemeliharaan atau pembaruan. Silakan kembali beberapa menit lagi.',
    'icon' => 'bi-tools',
    'tone' => 'progress',
    'retry' => true,
])
