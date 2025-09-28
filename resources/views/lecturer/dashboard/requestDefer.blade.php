<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Defer Request Management') }}
        </h2>
    </x-slot>

    <livewire:lecturer-request-defer-table />
</x-app-layout>
