@include('errors.shell', [
    'code' => 404,
    'title' => 'Halaman tidak ditemukan',
    'message' => 'Halaman yang Anda cari tidak ada, sudah dipindahkan, atau datanya telah dihapus. Periksa kembali alamatnya.',
    'icon' => 'bi-signpost-split',
    'tone' => 'closed',
    'retry' => false,
])
