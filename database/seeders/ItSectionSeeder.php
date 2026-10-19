<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Section;
use Illuminate\Database\Seeder;

/**
 * Section departemen IT (ruang lingkup IT di site pertambangan).
 * Aman dijalankan berulang di produksi: updateOrCreate per kode, tidak menghapus section lain.
 *   php artisan db:seed --class=ItSectionSeeder --force
 */
class ItSectionSeeder extends Seeder
{
    public const SECTIONS = [
        ['code' => 'IT-NET', 'name' => 'Jaringan', 'description' => 'LAN/WAN, Wi-Fi area tambang & mess, fiber optik, link radio data (PtP/PtMP), koneksi ISP & VSAT'],
        ['code' => 'IT-RAD', 'name' => 'Radio Komunikasi', 'description' => 'HT, radio rig unit, repeater, base station, perizinan frekuensi & channel komunikasi operasional'],
        ['code' => 'IT-HW', 'name' => 'Hardware', 'description' => 'PC, laptop, printer, scanner, tablet unit, UPS & perangkat pendukung: instalasi, perbaikan, penggantian'],
        ['code' => 'IT-SW', 'name' => 'Software', 'description' => 'Sistem operasi, Office, antivirus, aplikasi pendukung, serta manajemen lisensi'],
        ['code' => 'IT-SYS', 'name' => 'Aplikasi & Sistem', 'description' => 'Aplikasi bisnis (ERP, HRIS, aplikasi internal), integrasi, pengembangan & pemeliharaan sistem'],
        ['code' => 'IT-DB', 'name' => 'Database', 'description' => 'Administrasi database, backup & restore, kinerja, serta integritas data'],
        ['code' => 'IT-SUP', 'name' => 'IT Support', 'description' => 'Helpdesk, penanganan tiket, akun & akses pengguna (email, VPN, reset sandi), onboarding karyawan'],
        ['code' => 'IT-INF', 'name' => 'Server & Data Center', 'description' => 'Server, virtualisasi, storage, ruang server, kelistrikan & pendingin, pemantauan infrastruktur'],
        ['code' => 'IT-FMS', 'name' => 'Fleet Management System', 'description' => 'Sistem dispatch & monitoring unit tambang, GPS tracking, perangkat on-board, payload & produktivitas'],
        ['code' => 'IT-CCTV', 'name' => 'CCTV & Akses Kontrol', 'description' => 'CCTV area tambang/pit/gudang, NVR, fingerprint & akses pintu, absensi'],
        ['code' => 'IT-TEL', 'name' => 'Telepon & VoIP', 'description' => 'PABX, telepon IP, ekstensi kantor/site, telepon satelit untuk area terpencil'],
        ['code' => 'IT-SEC', 'name' => 'Keamanan Siber', 'description' => 'Firewall, kebijakan keamanan, pengelolaan hak akses, pemantauan ancaman & insiden keamanan'],
    ];

    public function run(): void
    {
        $it = Department::handlesTickets()->orderBy('id')->first()
            ?? Department::whereRaw('UPPER(TRIM(code)) = ?', [strtoupper(config('tickets.department_code'))])->first();

        if (! $it) {
            $this->command?->warn('Departemen IT tidak ditemukan (kode '.config('tickets.department_code').'). Section IT tidak dibuat.');

            return;
        }

        foreach (self::SECTIONS as $section) {
            Section::updateOrCreate(
                ['code' => $section['code']],
                $section + ['department_id' => $it->id, 'is_active' => true],
            );
        }

        $this->command?->info(count(self::SECTIONS).' section IT tersimpan di departemen '.$it->name.'.');
    }
}
