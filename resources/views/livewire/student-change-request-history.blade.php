<div class="space-y-6">

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <span class="text-green-400 text-xl mr-3">✅</span>
                <span class="text-green-800">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <span class="text-red-400 text-xl mr-3">❌</span>
                <span class="text-red-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Analytics Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <span class="text-blue-600 text-xl">📊</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Requests</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['total'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['pending'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['approved'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['rejected'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b rounded-lg border-gray-200 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                    placeholder="Search requests...">
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

    <!-- Change Requests Table -->
    <div class="bg-white rounded-lg shadow border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('justificationID')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1">
                                <span>ID</span>
                                @if($sortField === 'justificationID')
                                    <span class="text-gray-500">
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('applicationID')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1">
                                <span>Application</span>
                                @if($sortField === 'applicationID')
                                    <span class="text-gray-500">
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('companyName')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1">
                                <span>Company & Position</span>
                                @if($sortField === 'companyName')
                                    <span class="text-gray-500">
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reason
                        </th>
                        <th wire:click="sortBy('requestDate')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1">
                                <span>Request Date</span>
                                @if($sortField === 'requestDate')
                                    <span class="text-gray-500">
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('overallStatus')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1">
                                <span>Status</span>
                                @if($sortField === 'overallStatus')
                                    <span class="text-gray-500">
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $request->justificationID }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span class="font-medium">#{{ $request->applicationID }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div>
                                    <div class="font-medium">{{ $request->placementApplication->companyName }}</div>
                                    <div class="text-gray-500">{{ $request->placementApplication->position }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs">
                                    @if(strlen($request->reason) > 50)
                                        <span title="{{ $request->reason }}">
                                            {{ substr($request->reason, 0, 50) }}...
                                        </span>
                                    @else
                                        {{ $request->reason }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $request->requestDate->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <!-- Committee Status -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($request->committeeStatus === 'Approved') bg-green-100 text-green-800
                                        @elseif($request->committeeStatus === 'Rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        Committee: {{ $request->committeeStatus }}
                                    </span>

                                    <!-- Coordinator Status -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($request->coordinatorStatus === 'Approved') bg-green-100 text-green-800
                                        @elseif($request->coordinatorStatus === 'Rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        Coordinator: {{ $request->coordinatorStatus }}
                                        @if($request->committeeStatus === 'Rejected' && $request->coordinatorStatus === 'Rejected' && !$request->coordinatorID)
                                            <span class="ml-1" title="Auto-rejected due to committee rejection">*</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- View Details -->
                                    <button wire:click="viewRequest({{ $request->justificationID }})"
                                        class="text-gray-600 hover:text-gray-900" title="View details">
                                        👁️ View
                                    </button>

                                    <!-- Files Dropdown -->
                                    @if($request->files->count() > 0)
                                        <div class="relative group">
                                            <button class="text-gray-600 hover:text-gray-900" title="{{ $request->files->count() }} file(s)">
                                                📎 {{ $request->files->count() }}
                                            </button>
                                            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                                                <div class="py-1">
                                                    @foreach($request->files as $file)
                                                        <button wire:click="downloadFile({{ $file->id }})"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            📄 {{ $file->original_name }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <span class="text-4xl mb-4">📭</span>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Change Requests Found</h3>
                                    <p class="text-gray-500">You haven't submitted any change requests yet.</p>
                                    <a href="{{ route('student.placementApplications') }}"
                                       class="mt-4 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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
        @if($requests->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Change Request Detail Modal -->
    @if ($showDetailModal && $selectedRequest)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Change Request Details #{{ $selectedRequest->justificationID }}</h3>
                    <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Current Placement Application -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-800 mb-2">📋 Current Placement Application</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-blue-700">Application ID:</span>
                                <span class="text-blue-900">#{{ $selectedRequest->placementApplication->applicationID }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Company:</span>
                                <span class="text-blue-900">{{ $selectedRequest->placementApplication->companyName }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Position:</span>
                                <span class="text-blue-900">{{ $selectedRequest->placementApplication->position }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Work Method:</span>
                                <span class="text-blue-900">{{ $selectedRequest->placementApplication->method_of_work_display }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Duration:</span>
                                <span class="text-blue-900">
                                    {{ $selectedRequest->placementApplication->startDate->format('M d, Y') }} -
                                    {{ $selectedRequest->placementApplication->endDate->format('M d, Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Application Status:</span>
                                <span class="text-blue-900">{{ $selectedRequest->placementApplication->overall_status }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Change Request Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">🔄 Change Request Information</h4>
                        <div class="space-y-3">
                            <div>
                                <span class="font-medium text-gray-700">Request Date:</span>
                                <span class="text-gray-900">{{ $selectedRequest->requestDate->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Reason for Change:</span>
                                <div class="text-gray-900 mt-1 p-3 bg-white rounded border">
                                    {{ $selectedRequest->reason }}
                                </div>
                            </div>
                            @if($selectedRequest->decisionDate)
                                <div>
                                    <span class="font-medium text-gray-700">Decision Date:</span>
                                    <span class="text-gray-900">{{ $selectedRequest->decisionDate->format('M d, Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Approval Status -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">📊 Approval Status</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="font-medium text-gray-700">Committee Status:</span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($selectedRequest->committeeStatus === 'Approved') bg-green-100 text-green-800
                                    @elseif($selectedRequest->committeeStatus === 'Rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $selectedRequest->committeeStatus }}
                                </span>
                                @if($selectedRequest->committee)
                                    <div class="text-sm text-gray-600 mt-1">
                                        Reviewed by: {{ $selectedRequest->committee->user->name }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Coordinator Status:</span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($selectedRequest->coordinatorStatus === 'Approved') bg-green-100 text-green-800
                                    @elseif($selectedRequest->coordinatorStatus === 'Rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $selectedRequest->coordinatorStatus }}
                                    @if($selectedRequest->committeeStatus === 'Rejected' && $selectedRequest->coordinatorStatus === 'Rejected' && !$selectedRequest->coordinatorID)
                                        <span class="ml-1" title="Auto-rejected due to committee rejection">*</span>
                                    @endif
                                </span>
                                @if($selectedRequest->coordinator)
                                    <div class="text-sm text-gray-600 mt-1">
                                        Reviewed by: {{ $selectedRequest->coordinator->user->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Supporting Documents -->
                    @if($selectedRequest->files->count() > 0)
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800 mb-2">📎 Supporting Documents</h4>
                            <div class="space-y-2">
                                @foreach($selectedRequest->files as $file)
                                    <div class="flex items-center justify-between bg-white p-3 rounded border">
                                        <div class="flex items-center">
                                            <span class="text-green-600 mr-2">📄</span>
                                            <div>
                                                <div class="font-medium text-green-800">{{ $file->original_name }}</div>
                                                <div class="text-sm text-green-600">
                                                    {{ number_format($file->file_size / 1024, 1) }} KB
                                                </div>
                                            </div>
                                        </div>
                                        <button wire:click="downloadFile({{ $file->id }})"
                                            class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                                            Download
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Lecturer Remarks -->
                    @if($selectedRequest->remarks)
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-800 mb-2">💬 Lecturer Remarks</h4>
                            <div class="text-purple-900 p-3 bg-white rounded border">
                                {{ $selectedRequest->remarks }}
                            </div>
                        </div>
                    @endif

                    <!-- Next Steps -->
                    @if($selectedRequest->overall_status === 'Approved')
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <span class="text-blue-500 text-xl mr-3">ℹ️</span>
                                <div>
                                    <h4 class="font-semibold text-blue-800">Change Request Approved!</h4>
                                    <p class="text-blue-700 mt-1">
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
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <span class="text-red-500 text-xl mr-3">❌</span>
                                <div>
                                    <h4 class="font-semibold text-red-800">Change Request Rejected</h4>
                                    <p class="text-red-700 mt-1">
                                        Your change request has been rejected. Please review the lecturer remarks above for feedback.
                                        Your current placement application remains active.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <span class="text-yellow-500 text-xl mr-3">⏳</span>
                                <div>
                                    <h4 class="font-semibold text-yellow-800">Change Request Under Review</h4>
                                    <p class="text-yellow-700 mt-1">
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
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
