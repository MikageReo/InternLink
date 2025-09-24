<div>
    <!-- Custom Styles for Better Icon Visibility -->
    <style>
        .sort-icon {
            display: inline-block;
            width: 12px;
            text-align: center;
        }

        .sort-icon:before {
            font-weight: 900;
        }

        /* Fallback for missing FontAwesome */
        .fa-sort:before {
            content: "↕";
        }

        .fa-sort-up:before {
            content: "↑";
        }

        .fa-sort-down:before {
            content: "↓";
        }

        .fa-search:before {
            content: "🔍";
        }

        .fa-eye:before {
            content: "👁️";
        }

        .fa-download:before {
            content: "⬇️";
        }

        .fa-check:before {
            content: "✅";
        }

        .fa-times:before {
            content: "❌";
        }

        .fa-filter:before {
            content: "🔍";
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
        }

        .modal-content {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 51;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <p class="text-3xl font-bold text-dark-900 mb-2">Course Verification Management</p>
                <p class="text-gray-600">Review and approve student course verification applications</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100">
                            <i class="fa fa-file text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Applications</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalApplications }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100">
                            <i class="fa fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending Review</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pendingApplications }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <i class="fa fa-check text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Approved</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $approvedApplications }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100">
                            <i class="fa fa-times text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Rejected</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $rejectedApplications }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Controls Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Search -->
                    <div class="flex-1 lg:max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa fa-search text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Search by ID, student name, email...">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex items-center gap-4">
                        <select wire:model.live="statusFilter"
                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>

                        <!-- Clear Filters -->
                        <button wire:click="clearFilters"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('courseVerificationID')"
                                        class="flex items-center hover:text-gray-700">
                                        Application ID
                                        <span class="ml-1 sort-icon">
                                            @if ($sortField === 'courseVerificationID')
                                                @if ($sortDirection === 'asc')
                                                    <i class="fa fa-sort-up"></i>
                                                @else
                                                    <i class="fa fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('studentID')"
                                        class="flex items-center hover:text-gray-700">
                                        Student ID
                                        <span class="ml-1 sort-icon">
                                            @if ($sortField === 'studentID')
                                                @if ($sortDirection === 'asc')
                                                    <i class="fa fa-sort-up"></i>
                                                @else
                                                    <i class="fa fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('studentName')"
                                        class="flex items-center hover:text-gray-700">
                                        Student Name
                                        <span class="ml-1 sort-icon">
                                            @if ($sortField === 'studentName')
                                                @if ($sortDirection === 'asc')
                                                    <i class="fa fa-sort-up"></i>
                                                @else
                                                    <i class="fa fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('currentCredit')"
                                        class="flex items-center hover:text-gray-700">
                                        Current Credit
                                        <span class="ml-1 sort-icon">
                                            @if ($sortField === 'currentCredit')
                                                @if ($sortDirection === 'asc')
                                                    <i class="fa fa-sort-up"></i>
                                                @else
                                                    <i class="fa fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('status')" class="flex items-center hover:text-gray-700">
                                        Status
                                        <span class="ml-1 sort-icon">
                                            @if ($sortField === 'status')
                                                @if ($sortDirection === 'asc')
                                                    <i class="fa fa-sort-up"></i>
                                                @else
                                                    <i class="fa fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('applicationDate')"
                                        class="flex items-center hover:text-gray-700">
                                        Application Date
                                        <span class="ml-1 sort-icon">
                                            @if ($sortField === 'applicationDate')
                                                @if ($sortDirection === 'asc')
                                                    <i class="fa fa-sort-up"></i>
                                                @else
                                                    <i class="fa fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa fa-sort"></i>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($applications as $application)
                                <tr
                                    class="hover:bg-gray-50 {{ $application->status === 'pending' ? 'bg-yellow-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $application->courseVerificationID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->studentID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->student->user->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->currentCredit }} / 130
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$application->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="viewApplication({{ $application->courseVerificationID }})"
                                            class="text-blue-600 hover:text-blue-900 mr-3" title="View details">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @if ($application->submittedFile)
                                            <a href="{{ Storage::url($application->submittedFile) }}" target="_blank"
                                                class="text-green-600 hover:text-green-900" title="Download file">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fa fa-file text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No applications found</p>
                                            <p class="text-sm">Applications will appear here when students submit them.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Application Detail Modal -->
    @if ($showDetailModal && $selectedApplication)
        <div class="modal-overlay">
            <div class="modal-content bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Application Details - ID: {{ $selectedApplication->courseVerificationID }}
                        </h3>
                        <button type="button" wire:click="closeDetailModal"
                            class="text-gray-400 hover:text-gray-600">
                            <i class="fa fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4 space-y-6">
                    <!-- Student Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Student Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Student ID</p>
                                <p class="text-sm text-gray-900">{{ $selectedApplication->studentID }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Name</p>
                                <p class="text-sm text-gray-900">
                                    {{ $selectedApplication->student->user->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Email</p>
                                <p class="text-sm text-gray-900">
                                    {{ $selectedApplication->student->user->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Program</p>
                                <p class="text-sm text-gray-900">{{ $selectedApplication->student->program ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Application Information -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Application Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Current Credit</p>
                                <p class="text-sm text-gray-900">{{ $selectedApplication->currentCredit }} / 130</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Status</p>
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$selectedApplication->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($selectedApplication->status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Application Date</p>
                                <p class="text-sm text-gray-900">
                                    {{ $selectedApplication->applicationDate->format('F d, Y') }}</p>
                            </div>
                            @if ($selectedApplication->lecturerID)
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Reviewed By</p>
                                    <p class="text-sm text-gray-900">{{ $selectedApplication->lecturerID }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Submitted File -->
                    @if ($selectedApplication->submittedFile)
                        <div class="bg-green-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Submitted Document</h4>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fa fa-file text-green-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Course Documentation</p>
                                        <p class="text-xs text-gray-600">Click to download and review</p>
                                    </div>
                                </div>
                                <a href="{{ Storage::url($selectedApplication->submittedFile) }}" target="_blank"
                                    class="inline-flex items-center px-3 py-2 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fa fa-download mr-2"></i>
                                    Download File
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Remarks Section -->
                    @if ($selectedApplication->status === 'pending')
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Review Remarks</h4>
                            <div>
                                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                                    Add remarks for this application (optional):
                                </label>
                                <textarea wire:model="remarks" id="remarks" rows="4"
                                    class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter your comments, feedback, or reasons for your decision..."></textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    These remarks will be visible to the student after you approve or reject their application.
                                </p>
                            </div>
                        </div>
                    @elseif ($selectedApplication->remarks)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Lecturer Remarks</h4>
                            <div class="bg-white border rounded-md p-3">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedApplication->remarks }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Reviewed by: {{ $selectedApplication->lecturerID }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                @if ($selectedApplication->status === 'pending')
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" wire:click="closeDetailModal"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="button"
                            wire:click="rejectApplication({{ $selectedApplication->courseVerificationID }})"
                            wire:confirm="Are you sure you want to reject this application?"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fa fa-times mr-2"></i>
                            Reject
                        </button>
                        <button type="button"
                            wire:click="approveApplication({{ $selectedApplication->courseVerificationID }})"
                            wire:confirm="Are you sure you want to approve this application?"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fa fa-check mr-2"></i>
                            Approve
                        </button>
                    </div>
                @else
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                        <button type="button" wire:click="closeDetailModal"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Close
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
