@include('errors.shell', [
    'code' => $exception->getStatusCode(),
    'title' => 'Layanan sedang bermasalah',
    'message' => 'Server sedang mengalami gangguan. Coba lagi beberapa saat lagi; bila berlanjut, hubungi tim IT.',
    'icon' => 'bi-cloud-slash',
    'tone' => 'danger',
    'retry' => true,
])
