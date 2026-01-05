<div>
    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Course Verification Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <i class="fa fa-file-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Course Verification</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                        Pending: {{ $stats['courseVerification']['pending'] }}
                                    </span>
                                    <span class="text-xs text-green-600 dark:text-green-400">
                                        Approved: {{ $stats['courseVerification']['approved'] }}
                                    </span>
                                    @if($stats['courseVerification']['rejected'] > 0)
                                        <span class="text-xs text-red-600 dark:text-red-400">
                                            Rejected: {{ $stats['courseVerification']['rejected'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placement Applications Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                <i class="fa fa-briefcase text-purple-600 dark:text-purple-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Placement Applications</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">
                                        Total: {{ $placementApplicationStats['total'] }}
                                    </span>
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                        Pending: {{ $placementApplicationStats['pending'] }}
                                    </span>
                                    <span class="text-xs text-green-600 dark:text-green-400">
                                        Accepted: {{ $placementApplicationStats['accepted'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Defer Requests Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900">
                                <i class="fa fa-calendar-times text-orange-600 dark:text-orange-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Defer Requests</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                        Pending: {{ $deferRequestStats['pending'] }}
                                    </span>
                                    <span class="text-xs text-green-600 dark:text-green-400">
                                        Approved: {{ $deferRequestStats['approved'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Requests Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                                <i class="fa fa-exchange-alt text-red-600 dark:text-red-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Change Requests</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                        Pending: {{ $changeRequestStats['pending'] }}
                                    </span>
                                    <span class="text-xs text-green-600 dark:text-green-400">
                                        Approved: {{ $changeRequestStats['approved'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Current Status Overview -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Current Status Overview -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Status Overview</h3>

                        <div class="space-y-4">
                            <!-- Course Verification Status -->
                            <div class="border-l-4 {{ $latestCourseVerification ? ($latestCourseVerification->status === 'approved' ? 'border-green-500' : ($latestCourseVerification->status === 'pending' ? 'border-yellow-500' : 'border-red-500')) : 'border-gray-300' }} pl-4 py-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Course Verification</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            @if($latestCourseVerification)
                                                Status: <span class="font-semibold capitalize">{{ $latestCourseVerification->status }}</span>
                                                @if($latestCourseVerification->remarks)
                                                    <br>Remarks: {{ \Illuminate\Support\Str::limit($latestCourseVerification->remarks, 50) }}
                                                @endif
                                            @else
                                                No verification submitted yet
                                            @endif
                                        </p>
                                    </div>
                                    <a href="{{ route('student.courseVerification') }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-700 rounded-lg transition-colors shadow-sm hover:shadow-md">
                                        <span>View Details</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Placement Application Status -->
                            <div class="border-l-4 {{ $latestPlacementApplication ? ($latestPlacementApplication->studentAcceptance === 'Accepted' ? 'border-green-500' : ($latestPlacementApplication->overall_status === 'Approved' ? 'border-green-500' : ($latestPlacementApplication->overall_status === 'Pending' ? 'border-yellow-500' : 'border-red-500'))) : 'border-gray-300' }} pl-4 py-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Placement Application</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            @if($latestPlacementApplication)
                                                Company: <span class="font-semibold">{{ $latestPlacementApplication->companyName }}</span>
                                                <br>Status: <span class="font-semibold">
                                                    @if($latestPlacementApplication->studentAcceptance === 'Accepted')
                                                        Accepted
                                                    @elseif($latestPlacementApplication->studentAcceptance === 'Declined')
                                                        Declined
                                                    @else
                                                        {{ $latestPlacementApplication->overall_status }}
                                                    @endif
                                                </span>
                                                @if($latestPlacementApplication->studentAcceptance && $latestPlacementApplication->studentAcceptance !== 'Accepted' && $latestPlacementApplication->studentAcceptance !== 'Declined')
                                                    <br>Your Response: <span class="font-semibold capitalize">{{ $latestPlacementApplication->studentAcceptance }}</span>
                                                @endif
                                            @else
                                                No application submitted yet
                                            @endif
                                        </p>
                                    </div>
                                    <a href="{{ route('student.placementApplications') }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-700 rounded-lg transition-colors shadow-sm hover:shadow-md">
                                        <span>View Details</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Academic Advisor Info -->
                            <div class="border-l-4 {{ $academicAdvisor ? 'border-blue-500' : 'border-gray-300' }} pl-4 py-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Academic Advisor</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            @if($academicAdvisor && $academicAdvisor->user)
                                                Advisor: <span class="font-semibold">{{ $academicAdvisor->user->name }}</span>
                                                @if($academicAdvisor->user->email)
                                                    <br>Email: {{ $academicAdvisor->user->email }}
                                                @endif
                                            @else
                                                No academic advisor assigned
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Timeline -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Progress Timeline</h3>

                        <div class="relative">
                            <!-- Timeline Line -->
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-300 dark:bg-gray-600"></div>

                            <div class="space-y-6">
                                <!-- Step 1: Course Verification -->
                                <div class="relative flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $latestCourseVerification && $latestCourseVerification->status === 'approved' ? 'bg-green-500' : ($latestCourseVerification && $latestCourseVerification->status === 'pending' ? 'bg-yellow-500' : 'bg-gray-300 dark:bg-gray-600') }}">
                                        @if($latestCourseVerification && $latestCourseVerification->status === 'approved')
                                            <i class="fa fa-check text-white text-sm"></i>
                                        @elseif($latestCourseVerification && $latestCourseVerification->status === 'pending')
                                            <i class="fa fa-clock text-white text-sm"></i>
                                        @else
                                            <i class="fa fa-circle text-white text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="ml-6 flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Course Verification</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            @if($latestCourseVerification)
                                                {{ ucfirst($latestCourseVerification->status) }}
                                                @if($latestCourseVerification->status === 'approved')
                                                    - Ready for placement applications
                                                @endif
                                            @else
                                                Not started
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 2: Placement Application -->
                                <div class="relative flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $latestPlacementApplication && $latestPlacementApplication->overall_status === 'Approved' ? 'bg-green-500' : ($latestPlacementApplication && $latestPlacementApplication->overall_status === 'Pending' ? 'bg-yellow-500' : 'bg-gray-300 dark:bg-gray-600') }}">
                                        @if($latestPlacementApplication && $latestPlacementApplication->overall_status === 'Approved')
                                            <i class="fa fa-check text-white text-sm"></i>
                                        @elseif($latestPlacementApplication && $latestPlacementApplication->overall_status === 'Pending')
                                            <i class="fa fa-clock text-white text-sm"></i>
                                        @else
                                            <i class="fa fa-circle text-white text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="ml-6 flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Placement Application</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            @if($latestPlacementApplication)
                                                {{ $latestPlacementApplication->overall_status }}
                                                @if($latestPlacementApplication->overall_status === 'Approved' && $latestPlacementApplication->studentAcceptance === 'Accepted')
                                                    - Ready for supervisor assignment
                                                @endif
                                            @else
                                                Not started
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 3: Supervisor Assignment -->
                                <div class="relative flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $supervisorAssignment ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                                        @if($supervisorAssignment)
                                            <i class="fa fa-check text-white text-sm"></i>
                                        @else
                                            <i class="fa fa-circle text-white text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="ml-6 flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Assigned Supervisor</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            @if($supervisorAssignment)
                                                Assigned - {{ $supervisorAssignment->supervisor->user->name ?? 'N/A' }}
                                            @else
                                                Pending assignment
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 4: Internship -->
                                <div class="relative flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $supervisorAssignment && $latestPlacementApplication && $latestPlacementApplication->studentAcceptance === 'Accepted' ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                                        @if($supervisorAssignment && $latestPlacementApplication && $latestPlacementApplication->studentAcceptance === 'Accepted')
                                            <i class="fa fa-check text-white text-sm"></i>
                                        @else
                                            <i class="fa fa-circle text-white text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="ml-6 flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Internship</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            @if($supervisorAssignment && $latestPlacementApplication && $latestPlacementApplication->studentAcceptance === 'Accepted')
                                                Ready to begin
                                            @else
                                                Not ready
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Recent Activity -->
                <div class="space-y-6">
                    <!-- Recent Activity/Notifications -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>

                        @if($recentActivities->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                No recent activity
                            </p>
                        @else
                            <div class="space-y-4">
                                @foreach($recentActivities as $activity)
                                    <div class="border-l-4 {{ $activity['status'] === 'approved' || $activity['status'] === 'Approved' ? 'border-green-500' : ($activity['status'] === 'pending' || $activity['status'] === 'Pending' ? 'border-yellow-500' : 'border-red-500') }} pl-4 py-2">
                                        <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                            {{ $activity['title'] }}
                                        </h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $activity['description'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            {{ $activity['date']->diffForHumans() }}
                                        </p>
                                        @if($activity['link'])
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
                        @endif
                    </div>

                    <!-- Supervisor Assignment -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Assigned Supervisor</h3>

                        @if($supervisorAssignment && $supervisorAssignment->supervisor)
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Supervisor Name</p>
                                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $supervisorAssignment->supervisor->user->name }}
                                    </p>
                                </div>

                                @if($supervisorAssignment->supervisor->lecturerID)
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Lecturer ID</p>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supervisorAssignment->supervisor->lecturerID }}
                                        </p>
                                    </div>
                                @endif

                                @if($supervisorAssignment->supervisor->user->email)
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</p>
                                        <a href="mailto:{{ $supervisorAssignment->supervisor->user->email }}"
                                           class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $supervisorAssignment->supervisor->user->email }}
                                        </a>
                                    </div>
                                @endif

                                @if($supervisorAssignment->assignment_notes)
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Assignment Notes</p>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supervisorAssignment->assignment_notes }}
                                        </p>
                                    </div>
                                @endif

                                @if($supervisorAssignment->assigned_at)
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Assigned Date</p>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supervisorAssignment->assigned_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                No supervisor assigned yet
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

