{{-- Admin · Tambah Departemen — form bersama admin/departments/partials/form --}}
<x-app-layout title="Tambah Departemen" :back="route('admin.departments.index')">
    @include('admin.departments.partials.form', ['department' => null])
</x-app-layout>
