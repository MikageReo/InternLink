<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-center">
                    @if (auth()->user()->isStudent())
                        <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')" wire:navigate>
                            {{ __('Student Dashboard') }}
                        </x-nav-link>

                        <a href="{{ route('student.courseVerification') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('student.courseVerification') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            Course Verification
                        </a>

                        <a href="{{ route('student.placementApplications') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('student.placementApplications') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            Internship Placement
                        </a>

                        <a href="{{ route('student.requestDefer') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('student.requestDefer') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            Request Defer
                        </a>

                        <a href="{{ route('student.changeRequestHistory') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('student.changeRequestHistory') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            Change Request
                        </a>

                        <a href="{{ route('student.companyRankings') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('student.companyRankings') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            Company Rankings
                        </a>
                    @elseif(auth()->user()->isLecturer())
                        @php
                            $lecturer = auth()->user()->lecturer;
                        @endphp

                        <x-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard')" wire:navigate>
                            {{ __('Lecturer Dashboard') }}
                        </x-nav-link>

                        @if ($lecturer && $lecturer->isAdmin)
                            <a href="{{ route('lecturer.userDirectory') }}"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('lecturer.userDirectory') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                User Directory
                            </a>
                        @endif

                        @if ($lecturer && ($lecturer->isAcademicAdvisor || $lecturer->isCommittee || $lecturer->isCoordinator))
                            <a href="{{ route('lecturer.courseVerificationManagement') }}"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('lecturer.courseVerificationManagement') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Course Verification
                            </a>
                        @endif

                        @if ($lecturer && ($lecturer->isCommittee || $lecturer->isCoordinator))
                            <!-- Internship Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('lecturer.placementApplications') || request()->routeIs('lecturer.requestDefer') || request()->routeIs('lecturer.changeRequests') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                    Application
                                    <x-heroicon name="arrow-down" class="ml-1 h-4 w-4" />
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition
                                    class="absolute top-full left-0 mt-1 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('lecturer.placementApplications') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('lecturer.placementApplications') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                        Placement Application
                                    </a>
                                    <a href="{{ route('lecturer.requestDefer') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('lecturer.requestDefer') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                        Defer Request
                                    </a>
                                    <a href="{{ route('lecturer.changeRequests') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('lecturer.changeRequests') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                        Change Request
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if ($lecturer && $lecturer->isCoordinator)
                            <!-- Supervisor Assignment Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('lecturer.supervisorAssignments') || request()->routeIs('lecturer.autoSupervisorAssignments') || request()->routeIs('lecturer.ahpCalculator') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                    Supervisor Assignment
                                    <x-heroicon name="arrow-down" class="ml-1 h-4 w-4" />
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition
                                    class="absolute top-full left-0 mt-1 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('lecturer.supervisorAssignments') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('lecturer.supervisorAssignments') || request()->routeIs('lecturer.autoSupervisorAssignments') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                        Supervisor Assignment
                                    </a>
                                    <a href="{{ route('lecturer.ahpCalculator') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('lecturer.ahpCalculator') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                        Assign Settings
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if ($lecturer && ($lecturer->isAcademicAdvisor || $lecturer->isSupervisorFaculty || $lecturer->isCommittee || $lecturer->isCoordinator || $lecturer->isAdmin))
                            <a href="{{ route('lecturer.companyRankings') }}"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('lecturer.companyRankings') ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Company Rankings
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <x-heroicon name="arrow-down" class="h-4 w-4" />
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ ucfirst(auth()->user()->role) }}
                        </div>

                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('password')" wire:navigate>
                            {{ __('Change Password') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <span x-show="!open">
                        <x-heroicon name="bars-3" class="h-6 w-6" />
                    </span>
                    <span x-show="open" style="display: none;">
                        <x-heroicon name="x-mark" class="h-6 w-6" />
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if (auth()->user()->isStudent())
                <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')" wire:navigate>
                    {{ __('Student Dashboard') }}
                </x-responsive-nav-link>
            @elseif(auth()->user()->isLecturer())
                @php
                    $lecturer = auth()->user()->lecturer;
                @endphp

                <x-responsive-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard')" wire:navigate>
                    {{ __('Lecturer Dashboard') }}
                </x-responsive-nav-link>

                @if ($lecturer && $lecturer->isAdmin)
                    <x-responsive-nav-link :href="route('lecturer.userDirectory')" :active="request()->routeIs('lecturer.userDirectory')" wire:navigate>
                        User Directory
                    </x-responsive-nav-link>
                @endif

                @if ($lecturer && ($lecturer->isAcademicAdvisor || $lecturer->isCommittee || $lecturer->isCoordinator))
                    <x-responsive-nav-link :href="route('lecturer.courseVerificationManagement')" :active="request()->routeIs('lecturer.courseVerificationManagement')" wire:navigate>
                        Course Verification
                    </x-responsive-nav-link>
                @endif

                @if ($lecturer && ($lecturer->isCommittee || $lecturer->isCoordinator))
                    <x-responsive-nav-link :href="route('lecturer.placementApplications')" :active="request()->routeIs('lecturer.placementApplications')" wire:navigate>
                        Placement Application
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('lecturer.requestDefer')" :active="request()->routeIs('lecturer.requestDefer')" wire:navigate>
                        Defer Request
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('lecturer.changeRequests')" :active="request()->routeIs('lecturer.changeRequests')" wire:navigate>
                        Change Request
                    </x-responsive-nav-link>
                @endif

                @if ($lecturer && $lecturer->isCoordinator)
                    <x-responsive-nav-link :href="route('lecturer.supervisorAssignments')" :active="request()->routeIs('lecturer.supervisorAssignments') || request()->routeIs('lecturer.autoSupervisorAssignments')" wire:navigate>
                        Supervisor Assignment
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('lecturer.ahpCalculator')" :active="request()->routeIs('lecturer.ahpCalculator')" wire:navigate>
                        Assign Settings
                    </x-responsive-nav-link>
                @endif

                @if ($lecturer && ($lecturer->isAcademicAdvisor || $lecturer->isSupervisorFaculty || $lecturer->isCommittee || $lecturer->isCoordinator || $lecturer->isAdmin))
                    <x-responsive-nav-link :href="route('lecturer.companyRankings')" :active="request()->routeIs('lecturer.companyRankings')" wire:navigate>
                        Company Rankings
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                    x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                <div class="font-medium text-xs text-gray-400">{{ ucfirst(auth()->user()->role) }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('password')" wire:navigate>
                    {{ __('Change Password') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
