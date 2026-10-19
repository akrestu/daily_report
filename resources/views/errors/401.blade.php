@include('errors.shell', [
    'code' => 401,
    'title' => 'Perlu masuk',
    'message' => 'Sesi Anda belum masuk atau sudah berakhir. Silakan masuk untuk melanjutkan.',
    'icon' => 'bi-person-lock',
    'tone' => 'progress',
    'retry' => false,
])
