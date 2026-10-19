@include('errors.shell', [
    'code' => $exception->getStatusCode(),
    'title' => 'Permintaan tidak dapat diproses',
    'message' => 'Permintaan Anda tidak dapat diproses. Kembali ke halaman sebelumnya lalu coba lagi.',
    'icon' => 'bi-exclamation-circle',
    'tone' => 'pending',
])
