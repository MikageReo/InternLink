<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Directory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Header -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">User Directory</h3>
                        <p class="text-gray-600 dark:text-gray-400">Comprehensive user management with bulk registration, individual registration, search, sorting, and export capabilities</p>
                    </div>

                    <!-- Livewire User Directory Table -->
                    @livewire('user-directory-table')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
