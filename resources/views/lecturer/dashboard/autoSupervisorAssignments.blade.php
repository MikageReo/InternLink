<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Auto Supervisor Assignment') }}
        </h2>
    </x-slot>

    <div>
        @livewire('auto-supervisor-assignment')
    </div>
</x-app-layout>

