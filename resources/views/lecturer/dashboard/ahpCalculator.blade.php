<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('AHP Weight Calculator') }}
        </h2>
    </x-slot>

    <div>
        @livewire('a-h-p-weight-calculator')
    </div>
</x-app-layout>
