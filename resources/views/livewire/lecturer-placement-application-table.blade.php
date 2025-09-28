<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Internship Placement Applications</h1>
                        <p class="text-gray-600">Review and approve student internship placement applications</p>
                    </div>
                    <button wire:click="toggleAnalytics"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ $showAnalytics ? 'Hide Analytics' : 'Show Analytics' }}
                    </button>
                </div>
            </div>

            <!-- Analytics Dashboard -->
            @if ($showAnalytics && $analytics)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100">
                                <span class="text-blue-600 text-xl">📊</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Applications</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['total_applications'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100">
                                <span class="text-yellow-600 text-xl">⏳</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pending Review</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['pending_applications'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100">
                                <span class="text-green-600 text-xl">✅</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Approved</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['approved_applications'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100">
                                <span class="text-red-600 text-xl">❌</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Rejected</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['rejected_applications'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Flash Messages -->
            @if (session()->has('message'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700">{{ session('message') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Main Content Card -->
            <div class="bg-white shadow rounded-lg">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">Placement Applications</h2>
                        <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
                            Clear All Filters
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div class="lg:col-span-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                placeholder="Search applications, students, companies...">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select wire:model.live="statusFilter"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <select wire:model.live="roleFilter"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">All Applications</option>
                                <option value="committee_pending">Committee Pending</option>
                                <option value="coordinator_pending">Coordinator Pending</option>
                            </select>
                        </div>

                        <!-- Per Page -->
                        <div>
                            <select wire:model.live="perPage"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Position</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Application Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Remarks</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($applications as $application)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $application->applicationID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $application->student->studentID }}</div>
                                        <div class="text-sm text-gray-500">{{ $application->student->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->companyName }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->position }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs space-y-1">
                                            <div>
                                                <span class="font-medium">Committee:</span>
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->committeeStatus === 'Approved'
                                                        ? 'bg-green-100 text-green-800'
                                                        : ($application->committeeStatus === 'Rejected'
                                                            ? 'bg-red-100 text-red-800'
                                                            : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $application->committeeStatus }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Coordinator:</span>
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->coordinatorStatus === 'Approved'
                                                        ? 'bg-green-100 text-green-800'
                                                        : ($application->coordinatorStatus === 'Rejected'
                                                            ? 'bg-red-100 text-red-800'
                                                            : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $application->coordinatorStatus }}
                                                    @if (
                                                        $application->committeeStatus === 'Rejected' &&
                                                            $application->coordinatorStatus === 'Rejected' &&
                                                            !$application->coordinatorID)
                                                        <span class="ml-1"
                                                            title="Auto-rejected due to committee rejection">*</span>
                                                    @endif
                                                </span>
                                            </div>
                                            @if ($application->studentAcceptance)
                                                <div>
                                                    <span class="font-medium">Student:</span>
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs rounded-full
                                                        {{ $application->studentAcceptance === 'Accepted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ $application->studentAcceptance }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if ($application->remarks)
                                            <div class="max-w-xs truncate" title="{{ $application->remarks }}">
                                                {{ $application->remarks }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- View Details -->
                                            <button wire:click="viewApplication({{ $application->applicationID }})"
                                                class="text-blue-600 hover:text-blue-900" title="View details">
                                                👁️
                                            </button>

                                            <!-- Committee Actions (Only for Committee Members) -->
                                            @if ($application->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                                                <button
                                                    wire:click="approveAsCommittee({{ $application->applicationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCommittee({{ $application->applicationID }})"
                                                    class="text-green-600 hover:text-green-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Committee">
                                                    <span wire:loading.remove wire:target="approveAsCommittee({{ $application->applicationID }})">✅</span>
                                                    <span wire:loading wire:target="approveAsCommittee({{ $application->applicationID }})">
                                                        <svg class="animate-spin h-4 w-4 text-green-600 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                                <button
                                                    wire:click="rejectAsCommittee({{ $application->applicationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCommittee({{ $application->applicationID }})"
                                                    class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Committee">
                                                    <span wire:loading.remove wire:target="rejectAsCommittee({{ $application->applicationID }})">❌</span>
                                                    <span wire:loading wire:target="rejectAsCommittee({{ $application->applicationID }})">
                                                        <svg class="animate-spin h-4 w-4 text-red-600 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @endif

                                            <!-- Coordinator Actions (Only for Coordinators) -->
                                            @if (
                                                $application->coordinatorStatus === 'Pending' &&
                                                    $application->committeeStatus === 'Approved' &&
                                                    Auth::user()->lecturer->isCoordinator)
                                                <button
                                                    wire:click="approveAsCoordinator({{ $application->applicationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCoordinator({{ $application->applicationID }})"
                                                    class="text-green-600 hover:text-green-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Coordinator">
                                                    <span wire:loading.remove wire:target="approveAsCoordinator({{ $application->applicationID }})">✅</span>
                                                    <span wire:loading wire:target="approveAsCoordinator({{ $application->applicationID }})">
                                                        <svg class="animate-spin h-4 w-4 text-green-600 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                                <button
                                                    wire:click="rejectAsCoordinator({{ $application->applicationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCoordinator({{ $application->applicationID }})"
                                                    class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Coordinator">
                                                    <span wire:loading.remove wire:target="rejectAsCoordinator({{ $application->applicationID }})">❌</span>
                                                    <span wire:loading wire:target="rejectAsCoordinator({{ $application->applicationID }})">
                                                        <svg class="animate-spin h-4 w-4 text-red-600 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <p class="text-lg font-medium mb-2">No applications found</p>
                                        <p class="text-sm">No placement applications match your current filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($applications->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Application Detail Modal -->
    @if ($showDetailModal && $selectedApplication)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="closeDetailModal"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Application #{{ $selectedApplication->applicationID }} Details
                        </h3>
                        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">✖</button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Student Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Student Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Student ID:</strong> {{ $selectedApplication->student->studentID }}</p>
                                <p><strong>Name:</strong> {{ $selectedApplication->student->user->name }}</p>
                                <p><strong>Email:</strong> {{ $selectedApplication->student->user->email }}</p>
                            </div>
                        </div>

                        <!-- Company Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Company Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Company:</strong> {{ $selectedApplication->companyName }}</p>
                                <p><strong>Email:</strong> {{ $selectedApplication->companyEmail }}</p>
                                <p><strong>Phone:</strong> {{ $selectedApplication->companyNumber }}</p>
                                <p><strong>Address:</strong> {{ $selectedApplication->companyAddress }}</p>
                            </div>
                        </div>

                        <!-- Position Details -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Position Details</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Position:</strong> {{ $selectedApplication->position }}</p>
                                <p><strong>Method of Work:</strong> {{ $selectedApplication->methodOfWorkDisplay }}</p>
                                <p><strong>Allowance:</strong>
                                    {{ $selectedApplication->allowance ? 'RM ' . number_format($selectedApplication->allowance, 2) : 'Not specified' }}
                                </p>
                                <p><strong>Duration:</strong> {{ $selectedApplication->startDate->format('M d, Y') }} -
                                    {{ $selectedApplication->endDate->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Status Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Status Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Application Date:</strong>
                                    {{ $selectedApplication->applicationDate->format('M d, Y') }}</p>
                                <p><strong>Committee Status:</strong>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $selectedApplication->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($selectedApplication->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $selectedApplication->committeeStatus }}
                                    </span>
                                </p>
                                <p><strong>Coordinator Status:</strong>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $selectedApplication->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($selectedApplication->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $selectedApplication->coordinatorStatus }}
                                    </span>
                                </p>
                                @if ($selectedApplication->studentAcceptance)
                                    <p><strong>Student Response:</strong>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $selectedApplication->studentAcceptance === 'Accepted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $selectedApplication->studentAcceptance }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Job Scope -->
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-3">Job Scope</h4>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $selectedApplication->jobscope }}
                            </p>
                        </div>

                        <!-- Files -->
                        @if ($selectedApplication->files->count() > 0)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Submitted Files</h4>
                                <div class="space-y-2">
                                    @foreach ($selectedApplication->files as $file)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                            <div class="flex items-center space-x-3">
                                                <span>📄</span>
                                                <div>
                                                    <p class="text-sm font-medium">{{ $file->original_name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $file->mime_type }} •
                                                        {{ number_format($file->file_size / 1024, 1) }} KB</p>
                                                </div>
                                            </div>
                                            <button wire:click="downloadFile({{ $file->id }})"
                                                class="text-blue-600 hover:text-blue-900 text-sm">
                                                Download
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Remarks -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                            <textarea wire:model="remarks" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                placeholder="Add your remarks here..."></textarea>
                        </div>

                        <!-- Role Information -->
                        <div class="md:col-span-2">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <h5 class="text-sm font-medium text-blue-800 mb-2">Your Approval Permissions</h5>
                                <div class="text-xs text-blue-700 space-y-1">
                                    @if (Auth::user()->lecturer->isCommittee && Auth::user()->lecturer->isCoordinator)
                                        <p>✅ You can approve/reject as <strong>Committee Member</strong></p>
                                        <p>✅ You can approve/reject as <strong>Coordinator</strong> (after committee
                                            approval)</p>
                                    @elseif(Auth::user()->lecturer->isCommittee)
                                        <p>✅ You can approve/reject as <strong>Committee Member</strong></p>
                                        <p>❌ You cannot approve as Coordinator (coordinator role required)</p>
                                    @elseif(Auth::user()->lecturer->isCoordinator)
                                        <p>❌ You cannot approve as Committee (committee role required)</p>
                                        <p>✅ You can approve/reject as <strong>Coordinator</strong> (after committee
                                            approval)</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                    <button wire:click="closeDetailModal"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>

                    <div class="flex space-x-2">
                        @if ($selectedApplication->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                            <button wire:click="rejectAsCommittee({{ $selectedApplication->applicationID }})"
                                wire:loading.attr="disabled"
                                wire:target="rejectAsCommittee({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="rejectAsCommittee({{ $selectedApplication->applicationID }})">Reject as Committee</span>
                                <span wire:loading wire:target="rejectAsCommittee({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Rejecting...
                                </span>
                            </button>
                            <button wire:click="approveAsCommittee({{ $selectedApplication->applicationID }})"
                                wire:loading.attr="disabled"
                                wire:target="approveAsCommittee({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="approveAsCommittee({{ $selectedApplication->applicationID }})">Approve as Committee</span>
                                <span wire:loading wire:target="approveAsCommittee({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Approving...
                                </span>
                            </button>
                        @endif

                        @if (
                            $selectedApplication->coordinatorStatus === 'Pending' &&
                                $selectedApplication->committeeStatus === 'Approved' &&
                                Auth::user()->lecturer->isCoordinator)
                            <button wire:click="rejectAsCoordinator({{ $selectedApplication->applicationID }})"
                                wire:loading.attr="disabled"
                                wire:target="rejectAsCoordinator({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="rejectAsCoordinator({{ $selectedApplication->applicationID }})">Reject as Coordinator</span>
                                <span wire:loading wire:target="rejectAsCoordinator({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Rejecting...
                                </span>
                            </button>
                            <button wire:click="approveAsCoordinator({{ $selectedApplication->applicationID }})"
                                wire:loading.attr="disabled"
                                wire:target="approveAsCoordinator({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="approveAsCoordinator({{ $selectedApplication->applicationID }})">Approve as Coordinator</span>
                                <span wire:loading wire:target="approveAsCoordinator({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Approving...
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
