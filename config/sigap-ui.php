<?php

/*
|--------------------------------------------------------------------------
| SiGAP UI map
|--------------------------------------------------------------------------
| Satu sumber label + tone untuk badge di seluruh UI (lihat design/design-system).
| "tone" dipetakan ke kelas .sg-badge--{tone} di resources/css/tokens.css.
| Kunci mengikuti nilai kolom di database (DailyReport, Ticket).
*/

return [

    // daily_reports.approval_status — badge pill dengan dot
    'report_approval' => [
        'pending' => ['label' => 'Menunggu', 'tone' => 'pending'],
        'approved' => ['label' => 'Disetujui', 'tone' => 'approved'],
        'rejected' => ['label' => 'Ditolak', 'tone' => 'rejected'],
    ],

    // daily_reports.status — label kotak + ikon (bentuk berbeda dari status approval)
    'report_progress' => [
        'pending' => ['label' => 'Belum Mulai', 'icon' => 'bi-square'],
        'in_progress' => ['label' => 'Dalam Proses', 'icon' => 'bi-dash-square'],
        'completed' => ['label' => 'Selesai', 'icon' => 'bi-check-square'],
    ],

    // tickets.status
    'ticket_status' => [
        'open' => ['label' => 'Baru', 'tone' => 'open'],
        'assigned' => ['label' => 'Ditugaskan', 'tone' => 'open'],
        'in_progress' => ['label' => 'Dikerjakan', 'tone' => 'progress'],
        'pending' => ['label' => 'Menunggu Pemohon', 'tone' => 'pending'],
        'resolved' => ['label' => 'Selesai (menunggu konfirmasi)', 'tone' => 'approved'],
        'closed' => ['label' => 'Ditutup', 'tone' => 'closed'],
        'cancelled' => ['label' => 'Dibatalkan', 'tone' => 'rejected'],
    ],

    // tickets.approval_status
    'ticket_approval' => [
        'waiting_upload' => ['label' => 'Menunggu bukti persetujuan atasan', 'tone' => 'pending'],
        'waiting_verification' => ['label' => 'Bukti menunggu verifikasi IT', 'tone' => 'progress'],
        'approved' => ['label' => 'Disetujui atasan', 'tone' => 'approved'],
        'rejected' => ['label' => 'Bukti persetujuan ditolak', 'tone' => 'rejected'],
    ],

    // tickets.priority — badge kotak .sg-priority--p{n}
    'priority' => [
        'urgent' => ['label' => 'Kritis', 'code' => 'P1', 'class' => 'sg-priority--p1'],
        'high' => ['label' => 'Tinggi', 'code' => 'P2', 'class' => 'sg-priority--p2'],
        'medium' => ['label' => 'Sedang', 'code' => 'P3', 'class' => 'sg-priority--p3'],
        'low' => ['label' => 'Rendah', 'code' => 'P4', 'class' => 'sg-priority--p4'],
    ],

    // Sisa waktu SLA (menit) di bawah ambang ini tampil sebagai .sg-sla--warn
    'sla_warn_minutes' => 30,

];
