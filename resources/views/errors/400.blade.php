@include('errors.shell', [
    'code' => 400,
    'title' => 'Permintaan tidak valid',
    'message' => 'Permintaan yang dikirim tidak dapat diproses. Periksa kembali isian Anda lalu coba lagi.',
    'icon' => 'bi-exclamation-diamond',
    'tone' => 'pending',
    'retry' => false,
])
