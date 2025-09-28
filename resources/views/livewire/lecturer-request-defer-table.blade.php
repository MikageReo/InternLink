<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Defer Request Management</h1>
                        <p class="text-gray-600">Review and approve student defer requests</p>
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

                <!-- Advanced Filters -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div class="lg:col-span-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                placeholder="Search requests, students...">
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
                                <option value="">All Requests</option>
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

                    <!-- Date Range Filter -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" wire:model.live="dateFrom"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" wire:model.live="dateTo"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
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
                                        <span>Student ID</span>
                                        <span>{{ $sortField === 'studentName' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Reason</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('startDate')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>Defer Period</span>
                                        <span>{{ $sortField === 'startDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('applicationDate')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>Application Date</span>
                                        <span>{{ $sortField === 'applicationDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($requests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $request->deferID }}
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
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                                class="text-blue-600 hover:text-blue-900" title="View details">
                                                👁️
                                            </button>

                                            <!-- Committee Actions (Only for Committee Members) -->
                                            @if ($request->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                                                <button wire:click="approveAsCommittee({{ $request->deferID }})"
                                                    class="text-green-600 hover:text-green-900"
                                                    title="Approve as Committee">
                                                    ✅
                                                </button>
                                                <button wire:click="rejectAsCommittee({{ $request->deferID }})"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Reject as Committee">
                                                    ❌
                                                </button>
                                            @endif

                                            <!-- Coordinator Actions (Only for Coordinators) -->
                                            @if (
                                                $request->coordinatorStatus === 'Pending' &&
                                                    $request->committeeStatus === 'Approved' &&
                                                    Auth::user()->lecturer->isCoordinator)
                                                <button wire:click="approveAsCoordinator({{ $request->deferID }})"
                                                    class="text-green-600 hover:text-green-900"
                                                    title="Approve as Coordinator">
                                                    ✅ Coord
                                                </button>
                                                <button wire:click="rejectAsCoordinator({{ $request->deferID }})"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Reject as Coordinator">
                                                    ❌ Coord
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
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
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
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Include the detail modal -->
    @include('livewire.lecturer-request-defer-modal')
</div>
