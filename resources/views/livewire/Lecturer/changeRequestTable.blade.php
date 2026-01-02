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
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-8 gap-4">

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

                        <!-- Search -->
                        <div class="lg:col-span-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200"
                                   placeholder="Search requests, students, companies...">
                        </div>

                        <!-- Program Filter -->
                        <div>
                <select wire:model.live="program" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
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
                <select wire:model.live="semester" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                                <option value="">All Semesters</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                <select wire:model.live="year" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                                <option value="">All Years</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                <select wire:model.live="statusFilter" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                                <option value="">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Role Filter -->
                        <div>
                <select wire:model.live="roleFilter" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                                <option value="">All Requests</option>
                                <option value="committee_pending">Committee Pending</option>
                                <option value="coordinator_pending">Coordinator Pending</option>
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
                                    <button wire:click="sortBy('justificationID')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>ID</span>
                                        <span class="ml-1">{{ $sortField === 'justificationID' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('studentName')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>STUDENT</span>
                                        <span class="ml-1">{{ $sortField === 'studentName' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    CURRENT COMPANY
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">REASON</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('requestDate')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                                        <span>REQUEST DATE</span>
                                        <span class="ml-1">{{ $sortField === 'requestDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">STATUS</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ACTIONS</th>
                            </tr>
                        </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($requests as $request)
                                @php
                                    $isSelected = in_array($request->justificationID, $selectedRequests);
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
                                               wire:click="toggleRequestSelection({{ $request->justificationID }})"
                                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                               title="{{ $canSelect ? 'Select for bulk action' : 'Request cannot be selected' }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        #{{ $request->justificationID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $request->placementApplication->student->studentID }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $request->placementApplication->student->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $request->placementApplication->companyName }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $request->placementApplication->position }}</div>
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
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $request->committeeStatus === 'Approved' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                                       ($request->committeeStatus === 'Rejected' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200') }}">
                                                    {{ $request->committeeStatus }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium dark:text-gray-300">Coordinator:</span>
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $request->coordinatorStatus === 'Approved' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                                       ($request->coordinatorStatus === 'Rejected' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200') }}">
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
                                            <!-- View Details -->
                                            <button wire:click="viewRequest({{ $request->justificationID }})"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                title="View details">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                <span>View</span>
                                            </button>

                                            <!-- Committee Actions -->
                                            @if($request->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                                                <button wire:click="approveAsCommittee({{ $request->justificationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCommittee({{ $request->justificationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Committee">
                                                    <span wire:loading.remove wire:target="approveAsCommittee({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                        <span>Approve</span>
                                                    </span>
                                                    <span wire:loading wire:target="approveAsCommittee({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                                <button wire:click="rejectAsCommittee({{ $request->justificationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCommittee({{ $request->justificationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Committee">
                                                    <span wire:loading.remove wire:target="rejectAsCommittee({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        <span>Reject</span>
                                                    </span>
                                                    <span wire:loading wire:target="rejectAsCommittee({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                            @endif

                                            <!-- Coordinator Actions -->
                                            @if($request->coordinatorStatus === 'Pending' && $request->committeeStatus === 'Approved' && Auth::user()->lecturer->isCoordinator)
                                                <button wire:click="approveAsCoordinator({{ $request->justificationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approveAsCoordinator({{ $request->justificationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Approve as Coordinator">
                                                    <span wire:loading.remove wire:target="approveAsCoordinator({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                        <span>Approve</span>
                                                    </span>
                                                    <span wire:loading wire:target="approveAsCoordinator({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                                <button wire:click="rejectAsCoordinator({{ $request->justificationID }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejectAsCoordinator({{ $request->justificationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Reject as Coordinator">
                                                    <span wire:loading.remove wire:target="rejectAsCoordinator({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        <span>Reject</span>
                                                    </span>
                                                    <span wire:loading wire:target="rejectAsCoordinator({{ $request->justificationID }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                    </span>
                                                </button>
                                            @endif

                                            <!-- Download Files -->
                                            @if($request->files->count() > 0)
                                                <div class="relative group">
                                                    <button class="text-gray-500 hover:text-blue-600" title="{{ $request->files->count() }} file(s)">
                                                        📎 {{ $request->files->count() }}
                                                    </button>
                                                    <div class="absolute right-0 top-full mt-1 w-48 bg-white shadow-lg rounded-md border hidden group-hover:block z-10">
                                                        @foreach($request->files as $file)
                                                            <button wire:click="downloadFile({{ $file->id }})"
                                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 flex items-center space-x-2">
                                                                <span>📄</span>
                                                                <span class="truncate">{{ $file->original_name }}</span>
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
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fa fa-file text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No change requests found</p>
                                        <p class="text-sm">No change requests match your current filters.</p>
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
    @include('livewire.lecturer.changeRequestModal')
</div>
