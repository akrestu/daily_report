@include('errors.shell', [
    'code' => 405,
    'title' => 'Metode tidak diizinkan',
    'message' => 'Cara membuka halaman ini tidak didukung. Kembali ke halaman sebelumnya lalu ulangi dari tombol yang tersedia.',
    'icon' => 'bi-sign-stop',
    'tone' => 'pending',
    'retry' => false,
])
