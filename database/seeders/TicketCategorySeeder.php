<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

/**
 * Kategori tiket helpdesk IT site pertambangan (selaras section IT, lihat ItSectionSeeder).
 * Aman dijalankan berulang di produksi: updateOrCreate per nama; kategori lama yang bukan ranah IT
 * dinonaktifkan (tidak dihapus, karena masih dipakai riwayat tiket).
 *   php artisan db:seed --class=TicketCategorySeeder --force
 *
 * requires_approval = permintaan aset/akses/perubahan yang perlu bukti persetujuan atasan.
 */
class TicketCategorySeeder extends Seeder
{
    public const CATEGORIES = [
        // Gangguan & bantuan
        ['name' => 'IT Support',               'description' => 'Bantuan umum IT yang tidak masuk kategori lain'],
        ['name' => 'Hardware & Perangkat',     'description' => 'PC, laptop, printer, scanner, tablet unit, atau UPS rusak / bermasalah'],
        ['name' => 'Jaringan & Infrastruktur', 'description' => 'Internet / Wi-Fi putus atau lambat, LAN, link radio data, VSAT'],
        ['name' => 'Radio Komunikasi',         'description' => 'HT, radio rig unit, atau repeater tidak berfungsi, suara putus, channel bermasalah'],
        ['name' => 'Email & Akun',             'description' => 'Tidak bisa login, lupa / reset kata sandi, akun terkunci, masalah email'],
        ['name' => 'Software & Lisensi',       'description' => 'Instalasi atau error software (Office, antivirus, OS), lisensi habis'],
        ['name' => 'Aplikasi Bisnis',          'description' => 'Error atau kendala aplikasi ERP, HRIS, SiGAP, dan aplikasi internal lainnya'],
        ['name' => 'Fleet Management System',  'description' => 'Gangguan dispatch, GPS / perangkat on-board unit, data payload & produktivitas'],
        ['name' => 'CCTV & Akses Kontrol',     'description' => 'Kamera CCTV mati / rekaman hilang, fingerprint, akses pintu, mesin absensi'],
        ['name' => 'Telepon & VoIP',           'description' => 'Telepon kantor / IP phone mati, ekstensi, telepon satelit'],
        ['name' => 'Server & Database',        'description' => 'Layanan / server tidak bisa diakses, data hilang, permintaan backup / restore'],
        ['name' => 'Keamanan Siber',           'description' => 'Email phishing, virus / malware, aktivitas mencurigakan, laporan insiden keamanan'],

        // Permintaan layanan (perlu persetujuan atasan)
        ['name' => 'Perangkat Baru',                    'description' => 'Permintaan laptop, PC, printer, atau perangkat IT baru', 'requires_approval' => true],
        ['name' => 'Akses Sistem / Aplikasi',           'description' => 'Permintaan akses / akun sistem, aplikasi, folder, atau email', 'requires_approval' => true],
        ['name' => 'Pemasangan Jaringan Baru',          'description' => 'Titik LAN / Wi-Fi baru, jaringan untuk lokasi / kontainer / pos baru', 'requires_approval' => true],
        ['name' => 'Radio Baru / Program Channel',      'description' => 'Permintaan HT / radio unit baru, pemrograman atau penambahan channel', 'requires_approval' => true],
        ['name' => 'Pengembangan / Perubahan Aplikasi', 'description' => 'Fitur baru, perubahan alur, atau laporan baru di aplikasi', 'requires_approval' => true],
        ['name' => 'Peminjaman Perangkat',              'description' => 'Pinjam proyektor, laptop, HT, atau perangkat IT untuk kegiatan sementara', 'requires_approval' => true],

        ['name' => 'Lainnya', 'description' => 'Permintaan IT di luar kategori yang tersedia'],
    ];

    /** Kategori lama di luar ranah IT: dinonaktifkan */
    public const RETIRED = ['HR & Kepegawaian', 'Fasilitas & Umum'];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            TicketCategory::updateOrCreate(
                ['name' => $category['name']],
                array_merge(['requires_approval' => false], $category, ['is_active' => true])
            );
        }

        TicketCategory::whereIn('name', self::RETIRED)->update(['is_active' => false]);

        $this->command?->info(count(self::CATEGORIES).' kategori tiket IT aktif.');
    }
}
