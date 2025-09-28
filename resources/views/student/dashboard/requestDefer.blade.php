<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request Defer') }}
        </h2>
    </x-slot>

    <livewire:student-request-defer-table />
</x-app-layout>
