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
                                                class="ml-4 text-xs text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap">
                                                View →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- My Supervised Students (If Supervisor) -->
                    @if ($supervisedStudents->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">My Supervised
                                Students</h3>

                            <div class="space-y-4">
                                @foreach ($supervisedStudents as $item)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
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
                                                @if ($item['student']->user->email)
                                                    <a href="mailto:{{ $item['student']->user->email }}"
                                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                        <i class="fa fa-envelope mr-1"></i> Contact
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block">
                                                View →
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
