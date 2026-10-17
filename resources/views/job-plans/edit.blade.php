{{-- Edit Rencana Kerja — form bersama di job-plans/partials/form --}}
<x-app-layout title="Edit Rencana Kerja" :back="route('job-plans.show', $plan)">
    @include('job-plans.partials.form', ['plan' => $plan])
</x-app-layout>
