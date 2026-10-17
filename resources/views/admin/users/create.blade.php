{{-- Admin · Tambah Pengguna — form bersama admin/users/partials/form --}}
<x-app-layout title="Tambah Pengguna" :back="route('admin.users.index')">
    @include('admin.users.partials.form', ['user' => null])
</x-app-layout>
