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

        /* Responsive table styling for 50% screen display */
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                            <i class="fa fa-file text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                @if($isAcademicAdvisor)
                                    Pending Review
                                @else
                                    Pending Review
                                @endif
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 dark:text-gray-100">{{ $pendingApplications }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                            <i class="fa fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Applications</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 dark:text-gray-100">{{ $totalApplications }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <i class="fa fa-check text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                @if($isAcademicAdvisor)
                                    Eligible
                                @else
                                    Approved
                                @endif
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 dark:text-gray-100">{{ $approvedApplications }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                            <i class="fa fa-times text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                @if($isAcademicAdvisor)
                                    Ineligible
                                @else
                                    Rejected
                                @endif
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 dark:text-gray-100">{{ $rejectedApplications }}</p>
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

    <!-- Settings Button (Only for Coordinators) -->
    @if(Auth::user()->lecturer && (Auth::user()->lecturer->isCoordinator || Auth::user()->lecturer->isCommittee))
        <div class="mb-4 flex justify-end">
            <button wire:click="openSettingsModal"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fa fa-cog mr-2"></i>Edit Credit Hour Requirements
            </button>
        </div>
    @endif

    <!-- Advanced Filters -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200"
                            placeholder="Search by ID, student name, email...">
                    </div>

                    <!-- Program Filter -->
                    <div>
                        <select wire:model.live="program"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All Programs</option>
                            <option value="BCS">Bachelor of Computer Science (Software Engineering) with Honours</option>
                            <option value="BCN">Bachelor of Computer Science (Computer Systems & Networking) with Honours</option>
                            <option value="BCM">Bachelor of Computer Science (Multimedia Software) with Honours</option>
                            <option value="BCY">Bachelor of Computer Science (Cyber Security) with Honours</option>
                            <option value="DRC">Diploma in Computer Science</option>
                        </select>
                    </div>

                    <!-- Semester Filter -->
                    <div>
                        <select wire:model.live="semester"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All Semesters</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div>
                        <select wire:model.live="year"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All Years</option>
                            @for($y = date('Y'); $y >= 2023; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <select wire:model.live="statusFilter"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All Status</option>
                            <option value="pending">
                                @if($isAcademicAdvisor)
                                    Pending Review
                                @else
                                    Pending Coordinator Review
                                @endif
                            </option>
                            <option value="approved">
                                @if($isAcademicAdvisor)
                                    Marked as Eligible
                                @else
                                    Approved
                                @endif
                            </option>
                            <option value="rejected">
                                @if($isAcademicAdvisor)
                                    Marked as Ineligible
                                @else
                                    Rejected
                                @endif
                            </option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <div>
                        <button wire:click="clearFilters"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions Section -->
            @if (count($selectedApplications) > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center">
                            <i class="fa fa-check text-blue-600 text-lg mr-3"></i>
                            <span class="text-sm font-medium text-blue-900">
                                {{ count($selectedApplications) }} application(s) selected
                            </span>
                        </div>

                        <div class="flex flex-col md:flex-row gap-2">
                            <!-- Bulk Remarks Input -->
                            <input type="text" wire:model="remarks"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Remarks (required for reject)">

                            <!-- Bulk Actions Buttons -->
                            <button wire:click="bulkDownload"
                                wire:loading.attr="disabled"
                                wire:target="bulkDownload"
                                class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm hover:shadow-md">
                                <span wire:loading.remove wire:target="bulkDownload" class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>Download All</span>
                                </span>
                                <span wire:loading wire:target="bulkDownload" class="flex items-center gap-2">
                                    <x-loading-spinner size="h-4 w-4" color="text-white" />
                                    <span>Downloading...</span>
                                </span>
                            </button>

                            <button wire:click="bulkReject"
                                wire:confirm="Are you sure you want to reject {{ count($selectedApplications) }} application(s)?"
                                wire:loading.attr="disabled"
                                wire:target="bulkReject"
                                class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="bulkReject">
                                    <i class="fa fa-times mr-2"></i>
                                    Reject Selected
                                </span>
                                <span wire:loading wire:target="bulkReject" class="flex items-center">
                                    <x-loading-spinner class="mr-2" />
                                    Rejecting...
                                </span>
                            </button>

                            <button wire:click="bulkApprove"
                                wire:confirm="Are you sure you want to approve {{ count($selectedApplications) }} application(s)?"
                                wire:loading.attr="disabled"
                                wire:target="bulkApprove"
                                class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="bulkApprove">
                                    <i class="fa fa-check mr-2"></i>
                                    Approve Selected
                                </span>
                                <span wire:loading wire:target="bulkApprove" class="flex items-center">
                                    <x-loading-spinner class="mr-2" />
                                    Approving...
                                </span>
                            </button>
                        </div>
                    </div>
        </div>
    @endif

    <!-- Table Section -->
    @php
        $creditSettings = \App\Models\CourseVerificationSetting::getSettings();
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto table-container">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 1200px;">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                                    <input type="checkbox" wire:model.live="selectAll"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                        title="Select all">
                                </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <button wire:click="sortBy('courseVerificationID')"
                            class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('studentName')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Current Credit
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('status')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        @if($isAcademicAdvisor)
                                            Eligibility Status
                                        @else
                                            Status
                                        @endif
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('applicationDate')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($applications as $application)
                    <tr
                        class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($isAcademicAdvisor && $application->academicAdvisorStatus === null) || ($isCoordinator && $application->status === 'pending' && $application->academicAdvisorStatus === 'approved') ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($isAcademicAdvisor)
                                            @if ($application->academicAdvisorStatus === null)
                                                <input type="checkbox"
                                                    wire:model.live="selectedApplications"
                                                    value="{{ $application->courseVerificationID }}"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                            @else
                                                <span class="text-gray-400" title="Already {{ $application->academicAdvisorStatus }}">
                                                    <i class="fa {{ $application->academicAdvisorStatus === 'approved' ? 'fa-check' : 'fa-times' }}"></i>
                                                </span>
                                            @endif
                                        @else
                                            @if ($application->status === 'pending' && $application->academicAdvisorStatus === 'approved')
                                                <input type="checkbox"
                                                    wire:model.live="selectedApplications"
                                                    value="{{ $application->courseVerificationID }}"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                            @else
                                                <span class="text-gray-400" title="Already {{ $application->status }}">
                                                    <i class="fa {{ $application->status === 'approved' ? 'fa-check' : 'fa-times' }}"></i>
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $application->courseVerificationID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $application->studentID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $application->student->user->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $application->currentCredit }} / {{ $creditSettings->maximum_credit_hour }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($isAcademicAdvisor)
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'approved' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                ];
                                                $displayStatus = $application->academicAdvisorStatus ?? 'pending';
                                                $statusLabels = [
                                                    'pending' => 'Pending Review',
                                                    'approved' => 'Marked as Eligible',
                                                    'rejected' => 'Marked as Ineligible',
                                                ];
                                            @endphp
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$displayStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusLabels[$displayStatus] ?? 'Unknown' }}
                                            </span>
                                        @else
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
                                            @if($application->academicAdvisorStatus === 'approved')
                                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800" title="Approved by Academic Advisor">
                                                    Academic Advisor ✓
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $application->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button wire:click="viewApplication({{ $application->courseVerificationID }})"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                title="View details">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                <span>View</span>
                                            </button>
                                            @if ($application->files->count() > 0)
                                                <button wire:click="downloadFile({{ $application->files->first()->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="downloadFile({{ $application->files->first()->id }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Download file">
                                                    <span wire:loading.remove wire:target="downloadFile({{ $application->files->first()->id }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                        </svg>
                                                        <span>Download</span>
                                                    </span>
                                                    <span wire:loading wire:target="downloadFile({{ $application->files->first()->id }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-green-600 dark:text-green-400" />
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
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
    </div>

    <!-- Pagination -->
    <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 sm:px-6 bg-white dark:bg-gray-800 rounded-b-lg">
        {{ $applications->links() }}
    </div>

    <!-- Application Detail Modal -->
    @if ($showDetailModal && $selectedApplication)
        @php
            $modalCreditSettings = \App\Models\CourseVerificationSetting::getSettings();
        @endphp
        <div class="modal-overlay">
            <div class="modal-content bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Application Details - ID: {{ $selectedApplication->courseVerificationID }}
                        </h3>
                        <button type="button" wire:click="closeDetailModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i class="fa fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4 space-y-6">
                    <!-- Student Information -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Student Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Student ID</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->studentID }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Name</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $selectedApplication->student->user->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $selectedApplication->student->user->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Program</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->student->program ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Application Information -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Application Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Current Credit</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->currentCredit }} / {{ $modalCreditSettings->maximum_credit_hour }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</p>
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                        'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                        'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$selectedApplication->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                                    {{ ucfirst($selectedApplication->status) }}
                                </span>
                                @if($selectedApplication->academicAdvisorStatus)
                                    <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$selectedApplication->academicAdvisorStatus] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}" title="Academic Advisor Status">
                                        Academic Advisor: {{ ucfirst($selectedApplication->academicAdvisorStatus) }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Application Date</p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $selectedApplication->applicationDate->format('F d, Y') }}</p>
                            </div>
                            @if ($selectedApplication->academicAdvisorID && $selectedApplication->academicAdvisor)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Academic Advisor</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->academicAdvisor->user->name ?? $selectedApplication->academicAdvisorID }}</p>
                                </div>
                            @endif
                            @if ($selectedApplication->lecturerID && $selectedApplication->lecturer)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Reviewed By Coordinator</p>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->lecturer->user->name ?? $selectedApplication->lecturerID }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Submitted File -->
                    @if ($selectedApplication->files->count() > 0)
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Submitted Document</h4>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fa fa-file text-green-600 dark:text-green-400 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $selectedApplication->files->first()->original_name ?? 'Course Documentation' }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $selectedApplication->files->first()->mime_type }} - {{ number_format($selectedApplication->files->first()->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                                <button wire:click="downloadFile({{ $selectedApplication->files->first()->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="downloadFile({{ $selectedApplication->files->first()->id }})"
                                    class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                                    <span wire:loading.remove wire:target="downloadFile({{ $selectedApplication->files->first()->id }})" class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        <span>Download File</span>
                                    </span>
                                    <span wire:loading wire:target="downloadFile({{ $selectedApplication->files->first()->id }})" class="flex items-center gap-2">
                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                        <span>Downloading...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Academic Advisor Review Section -->
                    @if($isAcademicAdvisor)
                        @if($selectedApplication->academicAdvisorStatus === null)
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-800">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Academic Advisor Review</h4>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">Please review this application and determine if it is eligible for coordinator approval.</p>
                            </div>
                        @else
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Your Review History</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Review Status:</span>
                                        @if($selectedApplication->academicAdvisorStatus === 'approved')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                <i class="fa fa-check mr-1"></i>Marked as Eligible
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                <i class="fa fa-times mr-1"></i>Marked as Ineligible
                                            </span>
                                        @endif
                                    </div>
                                    @if($selectedApplication->academicAdvisor)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reviewed By:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->academicAdvisor->user->name ?? $selectedApplication->academicAdvisorID }}</span>
                                        </div>
                                    @endif
                                    @if($selectedApplication->academicAdvisorStatus === 'approved' && $selectedApplication->status)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Coordinator Status:</span>
                                            @if($selectedApplication->status === 'approved')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                    Approved
                                                </span>
                                            @elseif($selectedApplication->status === 'rejected')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                    Rejected
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                                    Pending Review
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Coordinator Review Section -->
                    @if($isCoordinator)
                        @if($selectedApplication->status === 'pending' && $selectedApplication->academicAdvisorStatus === 'approved')
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-800">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Coordinator Review</h4>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">This application has been approved by the academic advisor. Please review and make a final decision.</p>
                            </div>
                        @elseif($selectedApplication->status !== 'pending' && $selectedApplication->academicAdvisorStatus === 'approved')
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Your Review History</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Review Status:</span>
                                        @if($selectedApplication->status === 'approved')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                <i class="fa fa-check mr-1"></i>Approved
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                <i class="fa fa-times mr-1"></i>Rejected
                                            </span>
                                        @endif
                                    </div>
                                    @if($selectedApplication->lecturer)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reviewed By:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->lecturer->user->name ?? $selectedApplication->lecturerID }}</span>
                                        </div>
                                    @endif
                                    @if($selectedApplication->academicAdvisor)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Academic Advisor:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedApplication->academicAdvisor->user->name ?? $selectedApplication->academicAdvisorID }}</span>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 ml-2">
                                                Approved
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Remarks Section -->
                    @if (($isAcademicAdvisor && $selectedApplication->academicAdvisorStatus === null) || ($isCoordinator && $selectedApplication->status === 'pending' && $selectedApplication->academicAdvisorStatus === 'approved'))
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Review Remarks</h4>
                            <div>
                                <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    @if($isAcademicAdvisor)
                                        Add remarks for eligibility review {{ $selectedApplication->academicAdvisorStatus === null ? '(required for rejection)' : '(optional)' }}:
                                    @else
                                        Add remarks for this application {{ $selectedApplication->status === 'pending' ? '(required for rejection)' : '(optional)' }}:
                                    @endif
                                </label>
                                <textarea wire:model="remarks" id="remarks" rows="4"
                                    class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter your comments, feedback, or reasons for your decision..."></textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    @if($isAcademicAdvisor)
                                        These remarks will be visible to the coordinator and student.
                                    @else
                                        These remarks will be visible to the student after you approve or reject their application.
                                    @endif
                                </p>
                            </div>
                        </div>
                    @elseif ($selectedApplication->remarks)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">
                                @if($isAcademicAdvisor && $selectedApplication->academicAdvisorStatus)
                                    Your Remarks
                                @elseif($isCoordinator && $selectedApplication->status !== 'pending')
                                    Your Remarks
                                @else
                                    Lecturer Remarks
                                @endif
                            </h4>
                            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-3">
                                <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $selectedApplication->remarks }}</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                @if($isAcademicAdvisor && $selectedApplication->academicAdvisorStatus)
                                    Reviewed by: {{ $selectedApplication->academicAdvisor->user->name ?? $selectedApplication->academicAdvisorID }}
                                @elseif($isCoordinator && $selectedApplication->status !== 'pending')
                                    Reviewed by: {{ $selectedApplication->lecturer->user->name ?? $selectedApplication->lecturerID }}
                                @else
                                    Reviewed by: {{ $selectedApplication->lecturer->user->name ?? $selectedApplication->lecturerID }}
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                @if (($isAcademicAdvisor && $selectedApplication->academicAdvisorStatus === null) || ($isCoordinator && $selectedApplication->status === 'pending' && $selectedApplication->academicAdvisorStatus === 'approved'))
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" wire:click="closeDetailModal"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <span>Cancel</span>
                        </button>
                        <button type="button"
                            wire:click="rejectApplication({{ $selectedApplication->courseVerificationID }})"
                            wire:confirm="Are you sure you want to reject this application?"
                            wire:loading.attr="disabled"
                            wire:target="rejectApplication({{ $selectedApplication->courseVerificationID }})"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="rejectApplication({{ $selectedApplication->courseVerificationID }})" class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                <span>
                                    @if($isAcademicAdvisor)
                                        Mark as Ineligible
                                    @else
                                        Reject
                                    @endif
                                </span>
                            </span>
                            <span wire:loading wire:target="rejectApplication({{ $selectedApplication->courseVerificationID }})" class="flex items-center gap-2">
                                <x-loading-spinner size="h-4 w-4" color="text-white" />
                                <span>
                                    @if($isAcademicAdvisor)
                                        Marking as Ineligible...
                                    @else
                                        Rejecting...
                                    @endif
                                </span>
                            </span>
                        </button>
                        <button type="button"
                            wire:click="approveApplication({{ $selectedApplication->courseVerificationID }})"
                            wire:confirm="Are you sure you want to approve this application?"
                            wire:loading.attr="disabled"
                            wire:target="approveApplication({{ $selectedApplication->courseVerificationID }})"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="approveApplication({{ $selectedApplication->courseVerificationID }})" class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>
                                    @if($isAcademicAdvisor)
                                        Mark as Eligible
                                    @else
                                        Approve
                                    @endif
                                </span>
                            </span>
                            <span wire:loading wire:target="approveApplication({{ $selectedApplication->courseVerificationID }})" class="flex items-center gap-2">
                                <x-loading-spinner size="h-4 w-4" color="text-white" />
                                <span>
                                    @if($isAcademicAdvisor)
                                        Marking as Eligible...
                                    @else
                                        Approving...
                                    @endif
                                </span>
                            </span>
                        </button>
                    </div>
                @else
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="button" wire:click="closeDetailModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Close
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Settings Modal -->
    @if($showSettingsModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border border-gray-300 dark:border-gray-700 w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Edit Credit Hour Requirements
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Minimum Credit Hour *
                            </label>
                            <input type="number" wire:model="minimumCreditHour" min="100" max="150"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300">
                            @error('minimumCreditHour')
                                <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Maximum Credit Hour *
                            </label>
                            <input type="number" wire:model="maximumCreditHour" min="100" max="150"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-300">
                            @error('maximumCreditHour')
                                <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                <strong>Note:</strong> Students will need to enter a credit value between the minimum and maximum credit hours when submitting their course verification.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 mt-6">
                        <button wire:click="closeSettingsModal"
                            class="px-4 py-2 bg-gray-500 dark:bg-gray-600 text-white rounded-md hover:bg-gray-600 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button wire:click="updateSettings"
                            class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-800">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
