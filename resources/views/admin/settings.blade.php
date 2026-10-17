{{-- Pengaturan sistem (belum aktif: hanya informasi) --}}
<x-app-layout title="Pengaturan Sistem" :back="route('admin.dashboard')">
    <x-sg.page-header title="Pengaturan Sistem" subtitle="Pengaturan aplikasi masih dalam pengembangan." />

    <div class="card p-3 p-lg-4" style="max-width:640px">
        <div class="sg-internal-note p-3 mb-3 d-flex gap-2">
            <i class="bi bi-info-circle fs-5" aria-hidden="true"></i>
            <div class="small">Halaman ini belum bisa mengubah pengaturan. Konfigurasi masih diatur lewat file <code>.env</code> di server.</div>
        </div>

        <div class="mb-3">
            <label for="appName" class="form-label fw-semibold">Nama aplikasi</label>
            <input type="text" class="form-control" id="appName" value="SiGAP - Smart Integrated Gateway for Activity &amp; Performance" disabled>
        </div>
        <div class="mb-3">
            <label for="emailNotifications" class="form-label fw-semibold">Notifikasi email</label>
            <select class="form-select" id="emailNotifications" disabled>
                <option value="enabled" selected>Aktif</option>
                <option value="disabled">Nonaktif</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" disabled><i class="bi bi-floppy" aria-hidden="true"></i> Simpan</button>
    </div>
</x-app-layout>
