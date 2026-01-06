<div>
    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Custom Styles for Responsive Table -->
    <style>
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 1024px) {
            .table-container table {
                min-width: 1000px;
            }
        }
    </style>

            <!-- Statistics Cards -->
            @if ($analytics)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <i class="fa fa-file text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Requests</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <i class="fa fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Review</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['pending'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <i class="fa fa-check text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approved</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['approved'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                        <i class="fa fa-times text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rejected</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['rejected'] }}</p>
                    </div>
                </div>
            </div>
            </div>
            @endif

            <!-- Advanced Filters -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200"
                    placeholder="Search requests...">
            </div>

            <!-- Status Filter -->
            <div>
                <select wire:model.live="statusFilter"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>

            <!-- Per Page -->
            <div>
                <select wire:model.live="perPage"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
        </div>
    </div>

            <!-- Table Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto table-container">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 1000px;">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('justificationID')"
                                class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                <span>ID</span>
                                <span class="ml-1">{{ $sortField === 'justificationID' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('applicationID')"
                                class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                <span>APPLICATION</span>
                                <span class="ml-1">{{ $sortField === 'applicationID' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('companyName')"
                                class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                <span>COMPANY & POSITION</span>
                                <span class="ml-1">{{ $sortField === 'companyName' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            REASON
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('requestDate')"
                                class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                <span>REQUEST DATE</span>
                                <span class="ml-1">{{ $sortField === 'requestDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            STATUS
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ACTIONS
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($requests as $request)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                #{{ $request->justificationID }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                #{{ $request->applicationID }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div>
                                    <div class="font-medium dark:text-gray-100">{{ $request->placementApplication->companyName }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ $request->placementApplication->position }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div class="max-w-xs truncate" title="{{ $request->reason }}">
                                    {{ $request->reason }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $request->requestDate->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                <div class="text-xs space-y-1">
                                    <div>
                                        <span class="font-medium dark:text-gray-300">Committee:</span>
                                        <span
                                            class="inline-flex px-2 py-1 text-xs rounded-full
                                            {{ $request->committeeStatus === 'Approved'
                                                ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                : ($request->committeeStatus === 'Rejected'
                                                    ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                                                    : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200') }}">
                                            {{ $request->committeeStatus }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium dark:text-gray-300">Coordinator:</span>
                                        <span
                                            class="inline-flex px-2 py-1 text-xs rounded-full
                                            {{ $request->coordinatorStatus === 'Approved'
                                                ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                : ($request->coordinatorStatus === 'Rejected'
                                                    ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                                                    : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200') }}">
                                            {{ $request->coordinatorStatus }}
                                            @if($request->committeeStatus === 'Rejected' && $request->coordinatorStatus === 'Rejected' && !$request->coordinatorID)
                                                <span class="ml-1" title="Auto-rejected due to committee rejection">*</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- View Actions -->
                                    <button wire:click="viewRequest({{ $request->justificationID }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                        title="View details">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <span>View</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fa fa-file text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                    <p class="text-lg font-medium mb-2">No change requests found</p>
                                    <p class="text-sm">You haven't submitted any change requests yet.</p>
                                    <a href="{{ route('student.placementApplications') }}"
                                       class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-700">
                                        Go to Placement Applications
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 sm:px-6 bg-white dark:bg-gray-800 rounded-b-lg">
            {{ $requests->links() }}
        </div>
    </div>

            <!-- Change Request Detail Modal -->
            @if ($showDetailModal && $selectedRequest)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border border-gray-300 dark:border-gray-700 w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Change Request Details #{{ $selectedRequest->justificationID }}</h3>
                    <button wire:click="closeDetailModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Current Placement Application -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">📋 Current Placement Application</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-blue-700 dark:text-blue-400">Application ID:</span>
                                <span class="text-blue-900 dark:text-blue-200">#{{ $selectedRequest->placementApplication->applicationID }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700 dark:text-blue-400">Company:</span>
                                <span class="text-blue-900 dark:text-blue-200">{{ $selectedRequest->placementApplication->companyName }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700 dark:text-blue-400">Position:</span>
                                <span class="text-blue-900 dark:text-blue-200">{{ $selectedRequest->placementApplication->position }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700 dark:text-blue-400">Work Method:</span>
                                <span class="text-blue-900 dark:text-blue-200">{{ $selectedRequest->placementApplication->method_of_work_display }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700 dark:text-blue-400">Duration:</span>
                                <span class="text-blue-900 dark:text-blue-200">
                                    {{ $selectedRequest->placementApplication->startDate->format('M d, Y') }} -
                                    {{ $selectedRequest->placementApplication->endDate->format('M d, Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700 dark:text-blue-400">Application Status:</span>
                                <span class="text-blue-900 dark:text-blue-200">{{ $selectedRequest->placementApplication->overall_status }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Change Request Information -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">🔄 Change Request Information</h4>
                        <div class="space-y-3">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Request Date:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedRequest->requestDate->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Reason for Change:</span>
                                <div class="text-gray-900 dark:text-gray-100 mt-1 p-3 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                    {{ $selectedRequest->reason }}
                                </div>
                            </div>
                            @if($selectedRequest->decisionDate)
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Decision Date:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $selectedRequest->decisionDate->format('M d, Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Approval Status -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">📊 Approval Status</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Committee Status:</span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($selectedRequest->committeeStatus === 'Approved') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                    @elseif($selectedRequest->committeeStatus === 'Rejected') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                    @else bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 @endif">
                                    {{ $selectedRequest->committeeStatus }}
                                </span>
                                @if($selectedRequest->committee)
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Reviewed by: {{ $selectedRequest->committee->user->name }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Coordinator Status:</span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($selectedRequest->coordinatorStatus === 'Approved') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                    @elseif($selectedRequest->coordinatorStatus === 'Rejected') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                    @else bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 @endif">
                                    {{ $selectedRequest->coordinatorStatus }}
                                    @if($selectedRequest->committeeStatus === 'Rejected' && $selectedRequest->coordinatorStatus === 'Rejected' && !$selectedRequest->coordinatorID)
                                        <span class="ml-1" title="Auto-rejected due to committee rejection">*</span>
                                    @endif
                                </span>
                                @if($selectedRequest->coordinator)
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Reviewed by: {{ $selectedRequest->coordinator->user->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Supporting Documents -->
                    @if($selectedRequest->files->count() > 0)
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800 dark:text-green-300 mb-2">📎 Supporting Documents</h4>
                            <div class="space-y-2">
                                @foreach($selectedRequest->files as $file)
                                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center">
                                            <span class="text-green-600 dark:text-green-400 mr-2">📄</span>
                                            <div>
                                                <div class="font-medium text-green-800 dark:text-green-300">{{ $file->original_name }}</div>
                                                <div class="text-sm text-green-600 dark:text-green-400">
                                                    {{ number_format($file->file_size / 1024, 1) }} KB
                                                </div>
                                            </div>
                                        </div>
                                        <button wire:click="downloadFile({{ $file->id }})"
                                            class="px-3 py-1 bg-green-600 dark:bg-green-700 text-white rounded text-sm hover:bg-green-700 dark:hover:bg-green-800">
                                            Download
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Lecturer Remarks -->
                    @if($selectedRequest->remarks)
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-800 dark:text-purple-300 mb-2">💬 Lecturer Remarks</h4>
                            <div class="text-purple-900 dark:text-purple-200 p-3 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                {{ $selectedRequest->remarks }}
                            </div>
                        </div>
                    @endif

                    <!-- Next Steps -->
                    @if($selectedRequest->overall_status === 'Approved')
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <span class="text-blue-500 dark:text-blue-400 text-xl mr-3">ℹ️</span>
                                <div>
                                    <h4 class="font-semibold text-blue-800 dark:text-blue-300">Change Request Approved!</h4>
                                    <p class="text-blue-700 dark:text-blue-300 mt-1">
                                        Your change request has been approved by both committee and coordinator.
                                        You can now submit a new placement application from the
                                        <a href="{{ route('student.placementApplications') }}" class="underline font-medium">
                                            Placement Applications page
                                        </a>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($selectedRequest->overall_status === 'Rejected')
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <span class="text-red-500 dark:text-red-400 text-xl mr-3">❌</span>
                                <div>
                                    <h4 class="font-semibold text-red-800 dark:text-red-300">Change Request Rejected</h4>
                                    <p class="text-red-700 dark:text-red-300 mt-1">
                                        Your change request has been rejected. Please review the lecturer remarks above for feedback.
                                        Your current placement application remains active.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <span class="text-yellow-500 dark:text-yellow-400 text-xl mr-3">⏳</span>
                                <div>
                                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-300">Change Request Under Review</h4>
                                    <p class="text-yellow-700 dark:text-yellow-300 mt-1">
                                        Your change request is currently being reviewed by the committee and coordinator.
                                        You will receive an email notification once a decision is made.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end">
                    <button wire:click="closeDetailModal"
                        class="px-4 py-2 bg-gray-500 dark:bg-gray-600 text-white rounded-lg hover:bg-gray-600 dark:hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
            </div>
            @endif
        </div>
    </div>
</div>
