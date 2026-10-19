@include('errors.shell', [
    'code' => 413,
    'title' => 'File terlalu besar',
    'message' => 'Data atau lampiran yang dikirim melebihi batas ukuran. Perkecil ukuran file (maks. 5 MB per lampiran) lalu coba lagi.',
    'icon' => 'bi-file-earmark-x',
    'tone' => 'pending',
    'retry' => false,
])
