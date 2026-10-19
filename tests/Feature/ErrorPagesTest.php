<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Halaman error kustom (resources/views/errors) tampil dengan design system & Bahasa Indonesia */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.debug' => false]);

        Route::middleware('web')->group(function () {
            Route::get('/_test/abort/{code}', fn (int $code) => abort($code));
            Route::get('/_test/forbidden-message', fn () => abort(403, 'Anda hanya bisa menyetujui laporan dari job site Anda.'));
            Route::get('/_test/forbidden-default', fn () => abort(403));
            Route::get('/_test/crash', fn () => throw new \RuntimeException('rahasia internal'));
        });
    }

    public function test_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('sg-error__card', false);
    }

    public function test_403_shows_custom_abort_message_but_not_framework_default(): void
    {
        $this->get('/_test/forbidden-message')->assertForbidden()
            ->assertSee('Akses ditolak')
            ->assertSee('Anda hanya bisa menyetujui laporan dari job site Anda.');

        $this->get('/_test/forbidden-default')->assertForbidden()
            ->assertDontSee('sg-error__detail', false);
    }

    public function test_500_hides_exception_details(): void
    {
        $this->get('/_test/crash')
            ->assertStatus(500)
            ->assertSee('Terjadi kesalahan sistem')
            ->assertSee('Muat ulang')
            ->assertDontSee('rahasia internal');
    }

    /** @dataProvider codes */
    public function test_each_code_has_indonesian_page(int $code, string $title): void
    {
        $this->get("/_test/abort/{$code}")->assertStatus($code)->assertSee($title)->assertSee("Kode {$code}");
    }

    public static function codes(): array
    {
        return [
            [400, 'Permintaan tidak valid'],
            [401, 'Perlu masuk'],
            [405, 'Metode tidak diizinkan'],
            [413, 'File terlalu besar'],
            [419, 'Sesi kedaluwarsa'],
            [429, 'Terlalu banyak permintaan'],
            [503, 'Sedang pemeliharaan'],
            [418, 'Permintaan tidak dapat diproses'], // fallback 4xx
            [502, 'Layanan sedang bermasalah'],        // fallback 5xx
        ];
    }
}
