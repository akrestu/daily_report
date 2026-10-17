{{-- Impor Laporan dari Excel --}}
<x-app-layout title="Impor Laporan" :back="route('daily-reports.user-jobs')">
    <x-sg.import-form title="Impor Laporan" :action="route('daily-reports.import')" :template="route('daily-reports.export-template')"
                      :back="route('daily-reports.user-jobs')" button="Impor laporan" :columns="[
        ['job_name', true, 'Nama pekerjaan'],
        ['department', true, 'Nama departemen (harus sudah ada)'],
        ['job_site', false, 'Nama job site aktif'],
        ['section', false, 'Nama section aktif di departemen tersebut'],
        ['status', true, 'pending, in_progress, atau completed'],
        ['report_date', true, 'Tanggal laporan, format DD/MM/YYYY'],
        ['due_date', true, 'Due date, format DD/MM/YYYY'],
        ['description', true, 'Deskripsi pekerjaan'],
        ['remark', false, 'Catatan tambahan'],
        ['user_id', true, 'User ID PIC (atasan yang menyetujui)'],
    ]" />
</x-app-layout>
