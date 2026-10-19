@include('errors.shell', [
    'code' => 429,
    'title' => 'Terlalu banyak permintaan',
    'message' => 'Anda melakukan terlalu banyak percobaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.',
    'icon' => 'bi-speedometer2',
    'tone' => 'pending',
    'retry' => true,
])
