{{-- Admin · Impor Pengguna dari Excel --}}
<x-app-layout title="Impor Pengguna" :back="route('admin.users.index')">
    <x-sg.import-form title="Impor Pengguna" :action="route('admin.users.import')" :template="route('admin.users.export-template')"
                      :back="route('admin.users.index')" button="Impor pengguna" :columns="[
        ['name', true, 'Nama lengkap'],
        ['email', true, 'Alamat email'],
        ['user_id', false, 'User ID untuk masuk'],
        ['role', false, 'Nama role (harus sudah ada)'],
        ['department', false, 'Nama departemen (harus sudah ada)'],
        ['password', false, 'Kosongkan untuk memakai kata sandi bawaan'],
        ['email_verified', false, 'Isi Yes bila email sudah terverifikasi'],
    ]" />
</x-app-layout>
