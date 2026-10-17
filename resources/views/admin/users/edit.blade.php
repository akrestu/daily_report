{{-- Admin · Edit Pengguna — form bersama admin/users/partials/form --}}
<x-app-layout title="Edit Pengguna" :back="route('admin.users.index')">
    @include('admin.users.partials.form', ['user' => $user])
</x-app-layout>
