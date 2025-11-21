<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lecturer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600 dark:text-gray-400">You are logged in as a Lecturer.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <!-- User Management -->
                        <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-green-800 dark:text-green-200 mb-2">User Directory & Registration</h4>
                            <p class="text-green-600 dark:text-green-300 text-sm">Comprehensive user management
                            </p>
                            <div class="mt-3">
                                <a href="{{ route('lecturer.userDirectory') }}"
                                    class="mt-3 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm inline-block">
                                    <i class="fas fa-users mr-2"></i>User Directory
                                </a>
                            </div>
                        </div>

                        <!-- Course Verification Management -->
                        <div class="bg-teal-50 dark:bg-teal-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-teal-800 dark:text-teal-200 mb-2">Course Verification</h4>
                            <p class="text-teal-600 dark:text-teal-300 text-sm">Review and approve student course
                                verification applications</p>
                            <div class="mt-3">
                                <a href="{{ route('lecturer.courseVerificationManagement') }}"
                                    class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded text-sm inline-block">
                                    <i class="fas fa-check-circle mr-2"></i>Manage Applications
                                </a>
                            </div>
                        </div>

                        <!-- Internship Placement Management (Committee & Coordinator Only) -->
                        @if(Auth::user()->lecturer && (Auth::user()->lecturer->isCommittee || Auth::user()->lecturer->isCoordinator))
                            <div class="bg-cyan-50 dark:bg-cyan-900/20 p-6 rounded-lg">
                                <h4 class="font-semibold text-cyan-800 dark:text-cyan-200 mb-2">Internship Placement</h4>
                                <p class="text-cyan-600 dark:text-cyan-300 text-sm">Review and approve student internship placement applications</p>
                                <div class="mt-3">
                                    <a href="{{ route('lecturer.placementApplications') }}"
                                        class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded text-sm inline-block">
                                        🏢 Manage Applications
                                    </a>
                                </div>
                            </div>

                            <!-- Request Defer Management (Committee & Coordinator Only) -->
                            <div class="bg-amber-50 dark:bg-amber-900/20 p-6 rounded-lg">
                                <h4 class="font-semibold text-amber-800 dark:text-amber-200 mb-2">Request Defer</h4>
                                <p class="text-amber-600 dark:text-amber-300 text-sm">Review and approve student defer requests</p>
                                <div class="mt-3">
                                    <a href="{{ route('lecturer.requestDefer') }}"
                                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded text-sm inline-block">
                                        📝 Manage Defer Requests
                                    </a>
                                </div>
                            </div>

                            <!-- Change Request Management (Committee & Coordinator Only) -->
                            <div class="bg-orange-50 dark:bg-orange-900/20 p-6 rounded-lg">
                                <h4 class="font-semibold text-orange-800 dark:text-orange-200 mb-2">Change Requests</h4>
                                <p class="text-orange-600 dark:text-orange-300 text-sm">Review and approve student placement change requests</p>
                                <div class="mt-3">
                                    <a href="{{ route('lecturer.changeRequests') }}"
                                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm inline-block">
                                        🔄 Manage Change Requests
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Supervisor Assignment Management (Coordinator Only) -->
                        @if(Auth::user()->lecturer && Auth::user()->lecturer->isCoordinator)
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg">
                                <h4 class="font-semibold text-purple-800 dark:text-purple-200 mb-2">Supervisor Assignments</h4>
                                <p class="text-purple-600 dark:text-purple-300 text-sm">Assign supervisors to students with accepted placement applications</p>
                                <div class="mt-3">
                                    <a href="{{ route('lecturer.supervisorAssignments') }}"
                                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm inline-block">
                                        👨‍🏫 Manage Assignments
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- AHP Weight Calculator (Admin & Coordinator Only) -->
                        @if(Auth::user()->lecturer && (Auth::user()->lecturer->isAdmin || Auth::user()->lecturer->isCoordinator))
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-lg">
                                <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">AHP Weight Calculator</h4>
                                <p class="text-indigo-600 dark:text-indigo-300 text-sm">Configure weights for supervisor assignment criteria using Analytic Hierarchy Process</p>
                                <div class="mt-3">
                                    <a href="{{ route('lecturer.ahpCalculator') }}"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded text-sm inline-block">
                                        ⚖️ Configure Weights
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
