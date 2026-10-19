@include('errors.shell', [
    'code' => 419,
    'title' => 'Sesi kedaluwarsa',
    'message' => 'Halaman terlalu lama dibiarkan terbuka sehingga sesinya berakhir. Muat ulang halaman lalu kirim kembali.',
    'icon' => 'bi-hourglass-bottom',
    'tone' => 'pending',
    'retry' => true,
])
