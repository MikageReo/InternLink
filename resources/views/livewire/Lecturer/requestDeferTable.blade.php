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
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Requests</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['total_requests'] }}</p>
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
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['pending_requests'] }}</p>
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
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['approved_requests'] }}</p>
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
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $analytics['rejected_requests'] }}</p>
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
                                placeholder="Search requests, students...">
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
        @if (count($selectedRequests) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selection Info -->
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ count($selectedRequests) }} request(s) selected
                    </span>
                    <button wire:click="$set('selectedRequests', [])"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline">
                        Clear Selection
                    </button>

                    <div class="flex-1"></div>

                    <!-- Bulk Remarks Input -->
                    <input type="text" wire:model="bulkRemarks"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Remarks (optional)">

                    <!-- Action Buttons -->
                    <!-- Committee Actions -->
                    @if (Auth::user()->lecturer->isCommittee)
                        <button wire:click="bulkApproveCommittee"
                            wire:confirm="Are you sure you want to approve these requests as committee?"
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
                            wire:confirm="Are you sure you want to reject these requests as committee?"
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
                    @if (Auth::user()->lecturer->isCoordinator)
                        <button wire:click="bulkApproveCoordinator"
                            wire:confirm="Are you sure you want to approve these requests as coordinator?"
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
                            wire:confirm="Are you sure you want to reject these requests as coordinator?"
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
                                    <button wire:click="sortBy('deferID')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>ID</span>
                                        <span class="ml-1">{{ $sortField === 'deferID' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('studentName')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>STUDENT ID</span>
                                        <span class="ml-1">{{ $sortField === 'studentName' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    REASON
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('startDate')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>DEFER PERIOD</span>
                                        <span class="ml-1">{{ $sortField === 'startDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('applicationDate')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>APPLICATION DATE</span>
                                        <span class="ml-1">{{ $sortField === 'applicationDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    STATUS
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    ACTIONS
                                </th>
                            </tr>
                        </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($requests as $request)
                                @php
                                    $isSelected = in_array($request->deferID, $selectedRequests);
                                    $canSelect = false;
                                    if (Auth::user()->lecturer->isCommittee && $request->committeeStatus === 'Pending') {
                                        $canSelect = true;
                                    }
                                    if (Auth::user()->lecturer->isCoordinator && $request->coordinatorStatus === 'Pending' && $request->committeeStatus === 'Approved') {
                                        $canSelect = true;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $isSelected ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                               @if($isSelected) checked @endif
                                               @if(!$canSelect) disabled @endif
                                               wire:click="toggleRequestSelection({{ $request->deferID }})"
                                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                               title="{{ $canSelect ? 'Select for bulk action' : 'Request cannot be selected' }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $request->deferID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $request->student->studentID }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $request->student->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div class="max-w-xs truncate" title="{{ $request->reason }}">
                                            {{ $request->reason }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <div>{{ $request->startDate->format('M d, Y') }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">to {{ $request->endDate->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $request->applicationDate->format('M d, Y') }}
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
                                                    @if ($request->committeeStatus === 'Rejected' && $request->coordinatorStatus === 'Rejected' && !$request->coordinatorID)
                                                        <span class="ml-1"
                                                            title="Auto-rejected due to committee rejection">*</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- View Details -->
                                            <button wire:click="viewRequest({{ $request->deferID }})"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                title="View details">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                <span>View</span>
                                            </button>

                                            <!-- Committee Actions (Only for Committee Members) -->
                                            @if ($request->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                                                <button wire:click="approveAsCommittee({{ $request->deferID }})"
                                                    wire:confirm="Are you sure you want to approve this defer request as committee?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCommittee({{ $request->deferID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Committee">
                                                    <span wire:loading.remove wire:target="approveAsCommittee({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                        <span>Approve</span>
                                                    </span>
                                                    <span wire:loading wire:target="approveAsCommittee({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                                <button wire:click="rejectAsCommittee({{ $request->deferID }})"
                                                    wire:confirm="Are you sure you want to reject this defer request as committee?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCommittee({{ $request->deferID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Committee">
                                                    <span wire:loading.remove wire:target="rejectAsCommittee({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        <span>Reject</span>
                                                    </span>
                                                    <span wire:loading wire:target="rejectAsCommittee({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                            @endif

                                            <!-- Coordinator Actions (Only for Coordinators) -->
                                            @if (
                                                $request->coordinatorStatus === 'Pending' &&
                                                    $request->committeeStatus === 'Approved' &&
                                                    Auth::user()->lecturer->isCoordinator)
                                                <button wire:click="approveAsCoordinator({{ $request->deferID }})"
                                                    wire:confirm="Are you sure you want to approve this defer request as coordinator?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCoordinator({{ $request->deferID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Coordinator">
                                                    <span wire:loading.remove wire:target="approveAsCoordinator({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                        <span>Approve</span>
                                                    </span>
                                                    <span wire:loading wire:target="approveAsCoordinator({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                                <button wire:click="rejectAsCoordinator({{ $request->deferID }})"
                                                    wire:confirm="Are you sure you want to reject this defer request as coordinator?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCoordinator({{ $request->deferID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Coordinator">
                                                    <span wire:loading.remove wire:target="rejectAsCoordinator({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        <span>Reject</span>
                                                    </span>
                                                    <span wire:loading wire:target="rejectAsCoordinator({{ $request->deferID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fa fa-file text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No defer requests found</p>
                                        <p class="text-sm">No defer requests match your current filters.</p>
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
                        {{ $requests->links() }}
    </div>

    <!-- Include the detail modal -->
    @include('livewire.lecturer.requestDeferModal')
</div>
