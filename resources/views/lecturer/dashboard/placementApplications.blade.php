<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Internship Placement Applications') }}
        </h2>
    </x-slot>

    <livewire:lecturer-placement-application-table />
</x-app-layout>
