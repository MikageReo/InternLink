<div>
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
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Applications</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['total_applications'] }}</p>
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
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['pending_applications'] }}</p>
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
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['approved_applications'] }}</p>
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
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['rejected_applications'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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

    <!-- Advanced Filters -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
            <!-- Search -->
                        <div class="lg:col-span-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200"
                                placeholder="Search applications, students, companies...">
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
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
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

            <!-- Clear Filters -->
            <div>
                <button wire:click="clearFilters"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Bulk Actions Section -->
        @if (count($selectedApplications) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selection Info -->
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ count($selectedApplications) }} application(s) selected
                    </span>
                    <button wire:click="$set('selectedApplications', [])"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline">
                        Clear Selection
                    </button>

                    <div class="flex-1"></div>

                    <!-- Action Buttons -->
                    <!-- Committee Actions -->
                    @if(Auth::user()->lecturer->isCommittee)
                        <button wire:click="bulkApproveCommittee"
                                wire:confirm="Are you sure you want to approve these applications as committee?"
                                wire:loading.attr="disabled"
                                wire:target="bulkApproveCommittee"
                                class="inline-flex items-center gap-1.5 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="bulkApproveCommittee" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Committee Approve</span>
                            </span>
                            <span wire:loading wire:target="bulkApproveCommittee" class="flex items-center gap-1.5">
                                <x-loading-spinner size="h-4 w-4" />
                                <span>Approving...</span>
                            </span>
                        </button>
                        <button wire:click="bulkRejectCommittee"
                                wire:confirm="Are you sure you want to reject these applications as committee?"
                                wire:loading.attr="disabled"
                                wire:target="bulkRejectCommittee"
                                class="inline-flex items-center gap-1.5 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="bulkRejectCommittee" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Committee Reject</span>
                            </span>
                            <span wire:loading wire:target="bulkRejectCommittee" class="flex items-center gap-1.5">
                                <x-loading-spinner size="h-4 w-4" />
                                <span>Rejecting...</span>
                            </span>
                        </button>
                    @endif

                    <!-- Coordinator Actions -->
                    @if(Auth::user()->lecturer->isCoordinator)
                        <button wire:click="bulkApproveCoordinator"
                                wire:confirm="Are you sure you want to approve these applications as coordinator?"
                                wire:loading.attr="disabled"
                                wire:target="bulkApproveCoordinator"
                                class="inline-flex items-center gap-1.5 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="bulkApproveCoordinator" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Coordinator Approve</span>
                            </span>
                            <span wire:loading wire:target="bulkApproveCoordinator" class="flex items-center gap-1.5">
                                <x-loading-spinner size="h-4 w-4" />
                                <span>Approving...</span>
                            </span>
                        </button>
                        <button wire:click="bulkRejectCoordinator"
                                wire:confirm="Are you sure you want to reject these applications as coordinator?"
                                wire:loading.attr="disabled"
                                wire:target="bulkRejectCoordinator"
                                class="inline-flex items-center gap-1.5 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 dark:bg-orange-500 dark:hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="bulkRejectCoordinator" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Coordinator Reject</span>
                            </span>
                            <span wire:loading wire:target="bulkRejectCoordinator" class="flex items-center gap-1.5">
                                <x-loading-spinner size="h-4 w-4" />
                                <span>Rejecting...</span>
                            </span>
                        </button>
                    @endif

                    <!-- Download Button -->
                    <button wire:click="bulkDownload"
                            class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Download All Files</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto table-container">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 1200px;">
                <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-3 text-left">
                                    <input type="checkbox"
                                           wire:model.live="selectAll"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('applicationID')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>ID</span>
                                        <span class="ml-1">{{ $sortField === 'applicationID' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('studentID')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>STUDENT ID</span>
                                        <span class="ml-1">{{ $sortField === 'studentID' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('applyCount')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>APPLY COUNT</span>
                                        <span class="ml-1">{{ $sortField === 'applyCount' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('companyName')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>COMPANY NAME</span>
                                        <span class="ml-1">{{ $sortField === 'companyName' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    POSITION
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('applicationDate')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>APPLICATION DATE</span>
                                        <span class="ml-1">{{ $sortField === 'applicationDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('committeeStatus')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>APPROVAL STATUS</span>
                                        <span class="ml-1">{{ $sortField === 'committeeStatus' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    REMARKS
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    ACTIONS
                                </th>
                            </tr>
                        </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($applications as $application)
                                @php
                                    $canSelect = false;
                                    $lecturer = Auth::user()->lecturer;

                                    // Determine if this application can be selected based on user's role
                                    if ($lecturer->isCommittee && $application->committeeStatus === 'Pending') {
                                        $canSelect = true;
                                    }
                                    if ($lecturer->isCoordinator && $application->coordinatorStatus === 'Pending' && $application->committeeStatus === 'Approved') {
                                        $canSelect = true;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        @if($canSelect)
                                            <input type="checkbox"
                                                   wire:model.live="selectedApplications"
                                                   value="{{ $application->applicationID }}"
                                                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $application->applicationID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $application->student->studentID }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $application->student->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            {{ $application->applyCount ?? 0 }} {{ ($application->applyCount ?? 0) === 1 ? 'time' : 'times' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $application->companyName }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $application->position }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $application->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        <div class="text-xs space-y-1">
                                            <div>
                                                <span class="font-medium dark:text-gray-300">Committee:</span>
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->committeeStatus === 'Approved'
                                                        ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                        : ($application->committeeStatus === 'Rejected'
                                                            ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                                                            : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200') }}">
                                                    {{ $application->committeeStatus }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium dark:text-gray-300">Coordinator:</span>
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->coordinatorStatus === 'Approved'
                                                        ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                        : ($application->coordinatorStatus === 'Rejected'
                                                            ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                                                            : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200') }}">
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
                                                    <span class="font-medium dark:text-gray-300">Student:</span>
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs rounded-full
                                                        {{ $application->studentAcceptance === 'Accepted' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' :
                                                           ($application->studentAcceptance === 'Changed' ? 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200') }}">
                                                        {{ $application->studentAcceptance }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        @if ($application->remarks)
                                            <div class="max-w-xs truncate" title="{{ $application->remarks }}">
                                                {{ $application->remarks }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- View Details -->
                                            <button wire:click="viewApplication({{ $application->applicationID }})"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                title="View details">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                <span>View</span>
                                            </button>

                                            <!-- Committee Actions (Only for Committee Members) -->
                                            @if ($application->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                                                <button
                                                    wire:click="approveAsCommittee({{ $application->applicationID }})"
                                                    wire:confirm="Are you sure you want to approve this application as committee?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCommittee({{ $application->applicationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Committee">
                                                    <span wire:loading.remove wire:target="approveAsCommittee({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                        <span>Approve</span>
                                                    </span>
                                                    <span wire:loading wire:target="approveAsCommittee({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                                <button
                                                    wire:click="rejectAsCommittee({{ $application->applicationID }})"
                                                    wire:confirm="Are you sure you want to reject this application as committee?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCommittee({{ $application->applicationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Committee">
                                                    <span wire:loading.remove wire:target="rejectAsCommittee({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        <span>Reject</span>
                                                    </span>
                                                    <span wire:loading wire:target="rejectAsCommittee({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
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
                                                    wire:confirm="Are you sure you want to approve this application as coordinator?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCoordinator({{ $application->applicationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Coordinator">
                                                    <span wire:loading.remove wire:target="approveAsCoordinator({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                        <span>Approve</span>
                                                    </span>
                                                    <span wire:loading wire:target="approveAsCoordinator({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                                <button
                                                    wire:click="rejectAsCoordinator({{ $application->applicationID }})"
                                                    wire:confirm="Are you sure you want to reject this application as coordinator?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCoordinator({{ $application->applicationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Coordinator">
                                                    <span wire:loading.remove wire:target="rejectAsCoordinator({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        <span>Reject</span>
                                                    </span>
                                                    <span wire:loading wire:target="rejectAsCoordinator({{ $application->applicationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fa fa-file text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No applications found</p>
                                        <p class="text-sm">No placement applications match your current filters.</p>
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
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="closeDetailModal"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white dark:text-gray-900 rounded-lg shadow-xl max-w-4xl w-full mx-4">
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

                        <!-- Industry Supervisor Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Industry Supervisor Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Name:</strong> {{ $selectedApplication->industrySupervisorName ?? 'Not provided' }}</p>
                                <p><strong>Contact:</strong> {{ $selectedApplication->industrySupervisorContact ?? 'Not provided' }}</p>
                                <p><strong>Email:</strong> {{ $selectedApplication->industrySupervisorEmail ?? 'Not provided' }}</p>
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
                                            class="px-2 py-1 text-xs rounded-full {{ $selectedApplication->studentAcceptance === 'Accepted' ? 'bg-blue-100 text-blue-800' :
                                               ($selectedApplication->studentAcceptance === 'Changed' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800') }}">
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
                                wire:confirm="Are you sure you want to reject this application as committee?"
                                wire:loading.attr="disabled"
                                wire:target="rejectAsCommittee({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="rejectAsCommittee({{ $selectedApplication->applicationID }})">Reject as Committee</span>
                                <span wire:loading wire:target="rejectAsCommittee({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <x-loading-spinner size="h-4 w-4" color="text-red-600" class="mr-3" />
                                    Rejecting...
                                </span>
                            </button>
                            <button wire:click="approveAsCommittee({{ $selectedApplication->applicationID }})"
                                wire:loading.attr="disabled"
                                wire:target="approveAsCommittee({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="approveAsCommittee({{ $selectedApplication->applicationID }})">Approve as Committee</span>
                                <span wire:loading wire:target="approveAsCommittee({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <x-loading-spinner size="h-4 w-4" color="text-green-600" class="mr-3" />
                                    Approving...
                                </span>
                            </button>
                        @endif

                        @if (
                            $selectedApplication->coordinatorStatus === 'Pending' &&
                                $selectedApplication->committeeStatus === 'Approved' &&
                                Auth::user()->lecturer->isCoordinator)
                            <button wire:click="rejectAsCoordinator({{ $selectedApplication->applicationID }})"
                                wire:confirm="Are you sure you want to reject this application as coordinator?"
                                wire:loading.attr="disabled"
                                wire:target="rejectAsCoordinator({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="rejectAsCoordinator({{ $selectedApplication->applicationID }})">Reject as Coordinator</span>
                                <span wire:loading wire:target="rejectAsCoordinator({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <x-loading-spinner size="h-4 w-4" color="text-red-600" class="mr-3" />
                                    Rejecting...
                                </span>
                            </button>
                            <button wire:click="approveAsCoordinator({{ $selectedApplication->applicationID }})"
                                wire:loading.attr="disabled"
                                wire:target="approveAsCoordinator({{ $selectedApplication->applicationID }})"
                                class="px-4 py-2 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="approveAsCoordinator({{ $selectedApplication->applicationID }})">Approve as Coordinator</span>
                                <span wire:loading wire:target="approveAsCoordinator({{ $selectedApplication->applicationID }})" class="flex items-center">
                                    <x-loading-spinner size="h-4 w-4" color="text-green-600" class="mr-3" />
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
