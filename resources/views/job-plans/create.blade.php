{{-- Buat Rencana Kerja — form bersama di job-plans/partials/form --}}
<x-app-layout title="Buat Rencana Kerja" :back="route('job-plans.index')">
    @include('job-plans.partials.form', ['plan' => null])
</x-app-layout>
