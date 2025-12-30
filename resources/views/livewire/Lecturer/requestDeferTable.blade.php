<div>
    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Analytics Dashboard -->
            @if ($analytics)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100">
                                <span class="text-blue-600 text-xl">📊</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Requests</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['total_requests'] }}</p>
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
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['pending_requests'] }}</p>
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
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['approved_requests'] }}</p>
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
                                <p class="text-2xl font-bold text-gray-900">{{ $analytics['rejected_requests'] }}</p>
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
                        <h2 class="text-lg font-medium text-gray-900">Defer Requests</h2>
                        <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
                            Clear All Filters
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions Section -->
                @if (count($selectedRequests) > 0)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mx-6 mt-4 mb-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-blue-900">
                                    {{ count($selectedRequests) }} request(s) selected
                                </span>
                            </div>

                            <div class="flex flex-col md:flex-row gap-2">
                                <!-- Bulk Remarks Input -->
                                <input type="text" wire:model="bulkRemarks"
                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="Remarks (optional)">

                                <!-- Committee Actions -->
                                @if (Auth::user()->lecturer->isCommittee)
                                    <button wire:click="bulkApproveCommittee"
                                        wire:confirm="Are you sure you want to approve these requests as committee?"
                                        wire:loading.attr="disabled"
                                        wire:target="bulkApproveCommittee"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="bulkApproveCommittee" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Committee Approve
                                        </span>
                                        <span wire:loading wire:target="bulkApproveCommittee" class="flex items-center">
                                            <x-loading-spinner size="h-4 w-4" class="mr-2" />
                                            Approving...
                                        </span>
                                    </button>
                                    <button wire:click="bulkRejectCommittee"
                                        wire:confirm="Are you sure you want to reject these requests as committee?"
                                        wire:loading.attr="disabled"
                                        wire:target="bulkRejectCommittee"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="bulkRejectCommittee" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Committee Reject
                                        </span>
                                        <span wire:loading wire:target="bulkRejectCommittee" class="flex items-center">
                                            <x-loading-spinner size="h-4 w-4" class="mr-2" />
                                            Rejecting...
                                        </span>
                                    </button>
                                @endif

                                <!-- Coordinator Actions -->
                                @if (Auth::user()->lecturer->isCoordinator)
                                    <button wire:click="bulkApproveCoordinator"
                                        wire:confirm="Are you sure you want to approve these requests as coordinator?"
                                        wire:loading.attr="disabled"
                                        wire:target="bulkApproveCoordinator"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="bulkApproveCoordinator" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Coordinator Approve
                                        </span>
                                        <span wire:loading wire:target="bulkApproveCoordinator" class="flex items-center">
                                            <x-loading-spinner size="h-4 w-4" class="mr-2" />
                                            Approving...
                                        </span>
                                    </button>
                                    <button wire:click="bulkRejectCoordinator"
                                        wire:confirm="Are you sure you want to reject these requests as coordinator?"
                                        wire:loading.attr="disabled"
                                        wire:target="bulkRejectCoordinator"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="bulkRejectCoordinator" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Coordinator Reject
                                        </span>
                                        <span wire:loading wire:target="bulkRejectCoordinator" class="flex items-center">
                                            <x-loading-spinner size="h-4 w-4" class="mr-2" />
                                            Rejecting...
                                        </span>
                                    </button>
                                @endif

                                <!-- Download Button -->
                                <button wire:click="bulkDownload"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download All Files
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Advanced Filters -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-8 gap-4">
                        <!-- Search -->
                        <div class="lg:col-span-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                placeholder="Search requests, students...">
                        </div>

                        <!-- Program Filter -->
                        <div>
                            <select wire:model.live="program"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm dark:text-gray-600">
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
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm dark:text-gray-600">
                                <option value="">All Semesters</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <select wire:model.live="year"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm dark:text-gray-600">
                                <option value="">All Years</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select wire:model.live="statusFilter"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm dark:text-gray-600">
                                <option value="">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <select wire:model.live="roleFilter"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm dark:text-gray-600">
                                <option value="">All Requests</option>
                                <option value="committee_pending">Committee Pending</option>
                                <option value="coordinator_pending">Coordinator Pending</option>
                            </select>
                        </div>

                        <!-- Per Page -->
                        <div>
                            <select wire:model.live="perPage"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm dark:text-gray-600">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left">
                                    <input type="checkbox"
                                           wire:model.live="selectAll"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('deferID')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>ID</span>
                                        <span>{{ $sortField === 'deferID' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('studentName')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>STUDENT ID</span>
                                        <span>{{ $sortField === 'studentName' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    REASON</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('startDate')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>DEFER PERIOD</span>
                                        <span>{{ $sortField === 'startDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('applicationDate')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>APPLICATION DATE</span>
                                        <span>{{ $sortField === 'applicationDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    STATUS</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
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
                                <tr class="hover:bg-gray-50 {{ $isSelected ? 'bg-blue-50' : '' }}">
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                               @if($isSelected) checked @endif
                                               @if(!$canSelect) disabled @endif
                                               wire:click="toggleRequestSelection({{ $request->deferID }})"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                               title="{{ $canSelect ? 'Select for bulk action' : 'Request cannot be selected' }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $request->deferID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $request->student->studentID }}</div>
                                        <div class="text-sm text-gray-500">{{ $request->student->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-xs truncate" title="{{ $request->reason }}">
                                            {{ $request->reason }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>{{ $request->startDate->format('M d, Y') }}</div>
                                        <div class="text-gray-500">to {{ $request->endDate->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                        <div class="text-xs space-y-1">
                                            <div>
                                                <span class="font-medium">Committee:</span>
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $request->committeeStatus === 'Approved'
                                                        ? 'bg-green-100 text-green-800'
                                                        : ($request->committeeStatus === 'Rejected'
                                                            ? 'bg-red-100 text-red-800'
                                                            : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $request->committeeStatus }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Coordinator:</span>
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $request->coordinatorStatus === 'Approved'
                                                        ? 'bg-green-100 text-green-800'
                                                        : ($request->coordinatorStatus === 'Rejected'
                                                            ? 'bg-red-100 text-red-800'
                                                            : 'bg-yellow-100 text-yellow-800') }}">
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

                                            <!-- Download Files -->
                                            @if ($request->files->count() > 0)
                                                <div class="relative group">
                                                    <button class="text-gray-500 hover:text-blue-600"
                                                        title="{{ $request->files->count() }} file(s)">
                                                        📎 {{ $request->files->count() }}
                                                    </button>
                                                    <!-- File dropdown -->
                                                    <div
                                                        class="absolute right-0 top-full mt-1 w-48 bg-white shadow-lg rounded-md border hidden group-hover:block z-10">
                                                        @foreach ($request->files as $file)
                                                            <button wire:click="downloadFile({{ $file->id }})"
                                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 flex items-center space-x-2">
                                                                <span>📄</span>
                                                                <span
                                                                    class="truncate">{{ $file->original_name }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <p class="text-lg font-medium mb-2">No defer requests found</p>
                                        <p class="text-sm">No defer requests match your current filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($requests->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Include the detail modal -->
    @include('livewire.lecturer.requestDeferModal')
</div>
