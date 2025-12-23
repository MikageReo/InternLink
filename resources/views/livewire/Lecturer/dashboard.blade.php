<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Role-Based Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Course Verifications Card (All Lecturers) -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <i class="fa fa-file-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Course Verifications</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                        Pending: {{ $stats['courseVerifications']['pending'] }}
                                    </span>
                                    <span class="text-xs text-gray-700 dark:text-gray-300">
                                        Total: {{ $stats['courseVerifications']['total'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                    Approval Rate: {{ $stats['courseVerifications']['approval_rate'] }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supervised Students Card (If Supervisor) -->
                @if (isset($stats['supervisedStudents']))
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="fa fa-users text-green-600 dark:text-green-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Supervised Students
                                    </p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        {{ $stats['supervisedStudents']['current'] }} /
                                        {{ $stats['supervisedStudents']['quota'] }}
                                    </p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                        Available: {{ $stats['supervisedStudents']['available'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Committee Card (If Committee) -->
                @if (isset($stats['committee']))
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                    <i class="fa fa-tasks text-purple-600 dark:text-purple-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Committee Tasks</p>
                                    <div class="flex flex-col space-y-1 mt-1">
                                        <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                            Placements: {{ $stats['committee']['pendingPlacementApplications'] }}
                                        </span>
                                        <span class="text-xs text-orange-600 dark:text-orange-400">
                                            Defers: {{ $stats['committee']['pendingDeferRequests'] }}
                                        </span>
                                        <span class="text-xs text-red-600 dark:text-red-400">
                                            Changes: {{ $stats['committee']['pendingChangeRequests'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Coordinator Card (If Coordinator) -->
                @if (isset($stats['coordinator']))
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                                    <i class="fa fa-user-shield text-indigo-600 dark:text-indigo-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Coordinator Tasks
                                    </p>
                                    <div class="flex flex-col space-y-1 mt-1">
                                        <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                            Approvals:
                                            {{ $stats['coordinator']['pendingPlacementApprovals'] + $stats['coordinator']['pendingDeferApprovals'] + $stats['coordinator']['pendingChangeRequests'] }}
                                        </span>
                                        <span class="text-xs text-red-600 dark:text-red-400">
                                            Unassigned: {{ $stats['coordinator']['unassignedStudents'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Academic Advisor Card (If Academic Advisor) -->
                @if (isset($stats['academicAdvisor']))
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-teal-100 dark:bg-teal-900">
                                    <i class="fa fa-graduation-cap text-teal-600 dark:text-teal-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Academic Advisor</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        {{ $stats['academicAdvisor']['adviseeCount'] }} Students
                                    </p>
                                    @if(isset($stats['academicAdvisor']['pendingCourseVerifications']))
                                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                            Pending Reviews: {{ $stats['academicAdvisor']['pendingCourseVerifications'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Pending Tasks & Supervised Students -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Pending Tasks/To-Do List -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pending Tasks</h3>

                        @if ($pendingTasks->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                No pending tasks
                            </p>
                        @else
                            <div class="space-y-3">
                                @foreach ($pendingTasks as $task)
                                    <div
                                        class="border-l-4 {{ $task['priority'] === 'high' ? 'border-red-500' : 'border-yellow-500' }} pl-4 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-r">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $task['title'] }}
                                                </h4>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    {{ $task['description'] }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                    {{ is_string($task['date']) ? \Carbon\Carbon::parse($task['date'])->format('M d, Y') : $task['date']->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <a href="{{ $task['link'] }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-700 rounded-lg transition-colors shadow-sm hover:shadow-md whitespace-nowrap">
                                                <span>View</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if ($pendingTasks->hasPages())
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            Showing {{ $pendingTasks->firstItem() }} to {{ $pendingTasks->lastItem() }} of {{ $pendingTasks->total() }} tasks
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($pendingTasks->onFirstPage())
                                                <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">Previous</span>
                                            @else
                                                <button wire:click="goToPage({{ $pendingTasks->currentPage() - 1 }}, 'tasks')"
                                                    class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                    Previous
                                                </button>
                                            @endif

                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                Page {{ $pendingTasks->currentPage() }} of {{ $pendingTasks->lastPage() }}
                                            </span>

                                            @if ($pendingTasks->hasMorePages())
                                                <button wire:click="goToPage({{ $pendingTasks->currentPage() + 1 }}, 'tasks')"
                                                    class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                    Next
                                                </button>
                                            @else
                                                <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">Next</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- My Advisees (If Academic Advisor) -->
                    @if (isset($advisees) && $advisees->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">My Advisees</h3>

                            <div class="space-y-4">
                                @foreach ($advisees as $item)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $item['student']->user->name }}
                                                </h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $item['student']->studentID }}
                                                </p>
                                                @if ($item['student']->program)
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                        Program: {{ $item['student']->program }}
                                                    </p>
                                                @endif
                                                @if ($item['latestVerification'])
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                        Latest Verification:
                                                        <span class="font-semibold {{ $item['latestVerification']->academicAdvisorStatus === 'approved' ? 'text-green-600 dark:text-green-400' : ($item['latestVerification']->academicAdvisorStatus === 'rejected' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400') }}">
                                                            {{ $item['latestVerification']->academicAdvisorStatus ? ucfirst($item['latestVerification']->academicAdvisorStatus) : 'Pending' }}
                                                        </span>
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <button wire:click="viewStudentDetail('{{ $item['student']->studentID }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-700 rounded-lg transition-colors shadow-sm hover:shadow-md">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                    <span>View Details</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if ($advisees->hasPages())
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            Showing {{ $advisees->firstItem() }} to {{ $advisees->lastItem() }} of {{ $advisees->total() }} advisees
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($advisees->onFirstPage())
                                                <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">Previous</span>
                                            @else
                                                <button wire:click="goToPage({{ $advisees->currentPage() - 1 }}, 'advisees')"
                                                    class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                    Previous
                                                </button>
                                            @endif

                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                Page {{ $advisees->currentPage() }} of {{ $advisees->lastPage() }}
                                            </span>

                                            @if ($advisees->hasMorePages())
                                                <button wire:click="goToPage({{ $advisees->currentPage() + 1 }}, 'advisees')"
                                                    class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                    Next
                                                </button>
                                            @else
                                                <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">Next</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- My Supervised Students (If Supervisor) -->
                    @if ($supervisedStudents->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">My Supervised
                                Students</h3>

                            <div class="space-y-4">
                                @foreach ($supervisedStudents as $item)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $item['student']->user->name }}
                                                </h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $item['student']->studentID }}
                                                </p>
                                                @if ($item['placement'])
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        <span class="font-medium">Company:</span>
                                                        {{ $item['placement']->companyName }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                        Status: <span
                                                            class="font-semibold {{ $item['status'] === 'Active' ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                                            {{ $item['status'] }}
                                                        </span>
                                                    </p>
                                                @else
                                                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                                        Pending Placement
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <button wire:click="viewStudentDetail('{{ $item['student']->studentID }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-700 rounded-lg transition-colors shadow-sm hover:shadow-md">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                    <span>View Details</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if ($supervisedStudents->hasPages())
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            Showing {{ $supervisedStudents->firstItem() }} to {{ $supervisedStudents->lastItem() }} of {{ $supervisedStudents->total() }} students
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($supervisedStudents->onFirstPage())
                                                <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">Previous</span>
                                            @else
                                                <button wire:click="goToPage({{ $supervisedStudents->currentPage() - 1 }}, 'supervised')"
                                                    class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                    Previous
                                                </button>
                                            @endif

                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                Page {{ $supervisedStudents->currentPage() }} of {{ $supervisedStudents->lastPage() }}
                                            </span>

                                            @if ($supervisedStudents->hasMorePages())
                                                <button wire:click="goToPage({{ $supervisedStudents->currentPage() + 1 }}, 'supervised')"
                                                    class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                    Next
                                                </button>
                                            @else
                                                <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">Next</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Right Column: Recent Activity -->
                <div class="space-y-6">
                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>

                        @if ($recentActivities->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                No recent activity
                            </p>
                        @else
                            <div class="space-y-4">
                                @foreach ($recentActivities as $activity)
                                    <div
                                        class="border-l-4 {{ $activity['status'] === 'approved' || $activity['status'] === 'Approved' ? 'border-green-500' : ($activity['status'] === 'pending' || $activity['status'] === 'Pending' ? 'border-yellow-500' : 'border-red-500') }} pl-4 py-2">
                                        <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                            {{ $activity['title'] }}
                                        </h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $activity['description'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            {{ is_string($activity['date']) ? \Carbon\Carbon::parse($activity['date'])->diffForHumans() : $activity['date']->diffForHumans() }}
                                        </p>
                                        @if ($activity['link'])
                                            <a href="{{ $activity['link'] }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-700 rounded-lg transition-colors shadow-sm hover:shadow-md mt-2">
                                                <span>View</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if ($recentActivities->hasPages())
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                            Showing <span class="font-medium text-gray-900 dark:text-gray-100">{{ $recentActivities->firstItem() }}</span> to <span class="font-medium text-gray-900 dark:text-gray-100">{{ $recentActivities->lastItem() }}</span> of <span class="font-medium text-gray-900 dark:text-gray-100">{{ $recentActivities->total() }}</span> activities
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($recentActivities->onFirstPage())
                                                <button disabled
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-lg cursor-not-allowed">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                                    </svg>
                                                    <span>Previous</span>
                                                </button>
                                            @else
                                                <button wire:click="goToPage({{ $recentActivities->currentPage() - 1 }}, 'activities')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="goToPage({{ $recentActivities->currentPage() - 1 }}, 'activities')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow">
                                                    <span wire:loading.remove wire:target="goToPage({{ $recentActivities->currentPage() - 1 }}, 'activities')" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                                        </svg>
                                                        <span>Previous</span>
                                                    </span>
                                                    <span wire:loading wire:target="goToPage({{ $recentActivities->currentPage() - 1 }}, 'activities')">
                                                        <x-loading-spinner size="h-4 w-4" color="text-gray-600 dark:text-gray-400" />
                                                    </span>
                                                </button>
                                            @endif

                                            <div class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <span class="text-gray-900 dark:text-gray-100">{{ $recentActivities->currentPage() }}</span>
                                                <span class="text-gray-500 dark:text-gray-500">/</span>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $recentActivities->lastPage() }}</span>
                                            </div>

                                            @if ($recentActivities->hasMorePages())
                                                <button wire:click="goToPage({{ $recentActivities->currentPage() + 1 }}, 'activities')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="goToPage({{ $recentActivities->currentPage() + 1 }}, 'activities')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow">
                                                    <span wire:loading.remove wire:target="goToPage({{ $recentActivities->currentPage() + 1 }}, 'activities')" class="flex items-center gap-1.5">
                                                        <span>Next</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                        </svg>
                                                    </span>
                                                    <span wire:loading wire:target="goToPage({{ $recentActivities->currentPage() + 1 }}, 'activities')">
                                                        <x-loading-spinner size="h-4 w-4" color="text-gray-600 dark:text-gray-400" />
                                                    </span>
                                                </button>
                                            @else
                                                <button disabled
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-lg cursor-not-allowed">
                                                    <span>Next</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Detail Modal -->
    @if ($showStudentModal && $selectedStudent)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeStudentModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Student Details</h3>
                    <button wire:click="closeStudentModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fa fa-times text-xl"></i>
                    </button>
                </div>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Basic Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Student ID</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedStudent->studentID }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Name</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedStudent->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedStudent->user->email }}</p>
                            </div>
                            @if($selectedStudent->program)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Program</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedStudent->program }}</p>
                                </div>
                            @endif
                            @if($selectedStudent->semester)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Semester</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedStudent->semester }}</p>
                                </div>
                            @endif
                            @if($selectedStudent->year)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Year</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedStudent->year }}</p>
                                </div>
                            @endif
                            @if($selectedStudent->phone)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Phone</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedStudent->phone }}</p>
                                </div>
                            @endif
                            @if($selectedStudent->status)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($selectedStudent->status) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Academic Advisor Information -->
                    @if($selectedStudent->academicAdvisor)
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Academic Advisor</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $selectedStudent->academicAdvisor->user->name ?? $selectedStudent->academicAdvisorID }}
                            </p>
                        </div>
                    @endif

                    <!-- Supervisor Information -->
                    @if($selectedStudent->supervisorAssignments->isNotEmpty())
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Supervisor</h4>
                            @foreach($selectedStudent->supervisorAssignments->where('status', 'Assigned') as $assignment)
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $assignment->supervisor->user->name ?? $assignment->supervisorID }}
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <!-- Course Verifications -->
                    @if($selectedStudent->courseVerifications->isNotEmpty())
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Recent Course Verifications</h4>
                            <div class="space-y-2">
                                @foreach($selectedStudent->courseVerifications as $verification)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ $verification->applicationDate->format('M d, Y') }} -
                                            {{ $verification->currentCredit }} credits
                                        </span>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $verification->academicAdvisorStatus === 'approved' ? 'bg-green-100 text-green-800' :
                                               ($verification->academicAdvisorStatus === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $verification->academicAdvisorStatus ? ucfirst($verification->academicAdvisorStatus) : 'Pending' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Placement Applications -->
                    @if($selectedStudent->placementApplications->isNotEmpty())
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Recent Placement Applications</h4>
                            <div class="space-y-2">
                                @foreach($selectedStudent->placementApplications as $application)
                                    <div class="text-sm">
                                        <p class="text-gray-700 dark:text-gray-300 font-medium">
                                            {{ $application->companyName }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ $application->applicationDate->format('M d, Y') }} -
                                            Status: {{ ucfirst($application->overall_status ?? 'Pending') }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex justify-end">
                    <button wire:click="closeStudentModal"
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
