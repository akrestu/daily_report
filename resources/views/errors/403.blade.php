{{-- Pesan khusus dari abort(403, '...') ditampilkan; pesan bawaan framework (bahasa Inggris) tidak --}}
@include('errors.shell', [
    'code' => 403,
    'title' => 'Akses ditolak',
    'message' => 'Anda tidak memiliki izin untuk membuka halaman atau melakukan tindakan ini. Hubungi atasan atau admin bila Anda merasa seharusnya punya akses.',
    'icon' => 'bi-shield-lock',
    'tone' => 'danger',
    'retry' => false,
    'detail' => ($m = $exception->getMessage()) && $m !== 'This action is unauthorized.' && $m !== 'Forbidden' ? $m : null,
])
