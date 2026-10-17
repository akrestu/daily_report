{{-- Admin · Edit Departemen — form bersama admin/departments/partials/form --}}
<x-app-layout title="Edit Departemen" :back="route('admin.departments.show', $department)">
    @include('admin.departments.partials.form', ['department' => $department])
</x-app-layout>
