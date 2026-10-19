@include('errors.shell', [
    'code' => 500,
    'title' => 'Terjadi kesalahan sistem',
    'message' => 'Maaf, terjadi kesalahan di server. Tim IT sudah tercatat otomatis; coba lagi beberapa saat lagi.',
    'icon' => 'bi-bug',
    'tone' => 'danger',
    'retry' => true,
])
