<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Request Defer</h1>
                <p class="text-gray-600">Submit and manage your internship defer requests</p>

                <!-- Important Notice -->
                <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-yellow-600 text-lg">⚠️</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                <strong>Important:</strong> Defer requests incur a penalty fee regardless of approval
                                status.
                                Please consider all options before submitting.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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

            @if (session()->has('warning'))
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-yellow-700">{{ session('warning') }}</p>
                </div>
            @endif

            <!-- Main Content Card -->
            <div class="bg-white shadow rounded-lg">
                <!-- Header with Request Button -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">My Defer Requests</h2>
                        @if ($canMakeRequest)
                            <button wire:click="openForm"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                📝 Request Defer
                            </button>
                        @else
                            <span class="text-gray-400 text-sm">Course verification approval required</span>
                        @endif
                    </div>
                </div>

                <!-- Filters -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
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
                                    Reason</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('startDate')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>Start Date</span>
                                        <span>{{ $sortField === 'startDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="sortBy('endDate')"
                                        class="flex items-center space-x-1 hover:text-gray-700">
                                        <span>End Date</span>
                                        <span>{{ $sortField === 'endDate' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
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
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-xs truncate" title="{{ $request->reason }}">
                                            {{ $request->reason }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->startDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->endDate->format('M d, Y') }}
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
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- View/Edit Actions -->
                                            @if ($request->committeeStatus === 'Pending' && $request->coordinatorStatus === 'Pending')
                                                <!-- Edit button - only when both are pending -->
                                                <button wire:click="edit({{ $request->deferID }})"
                                                    class="text-blue-600 hover:text-blue-900" title="Edit request">
                                                    ✏️ Edit
                                                </button>
                                            @else
                                                <!-- View button - when any approval has been made -->
                                                <button wire:click="view({{ $request->deferID }})"
                                                    class="text-gray-600 hover:text-gray-900"
                                                    title="View details (read-only)">
                                                    👁️ View
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <p class="text-lg font-medium mb-2">No defer requests found</p>
                                        <p class="text-sm">You haven't submitted any defer requests yet.</p>
                                        @if ($canMakeRequest)
                                            <button wire:click="openForm"
                                                class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                                Submit Your First Request
                                            </button>
                                        @endif
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

    <!-- Warning Modal -->
    @if ($showWarningModal)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="cancelRequest"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-red-600 text-3xl">⚠️</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-900">
                                Important Notice: Defer Request Penalty
                            </h3>
                            <p class="text-sm text-red-700 mt-1">
                                Please read this carefully before proceeding
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <!-- Main Warning -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-yellow-600 text-xl">💰</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-yellow-800">Financial Penalty</h4>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Submitting a defer request will incur a <strong>penalty fee</strong> that must
                                        be paid regardless of whether your request is approved or rejected.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-900">Before you proceed, please consider:</h4>
                            <ul class="list-disc list-inside space-y-2 text-sm text-gray-700">
                                <li><strong>Penalty Fee:</strong> A non-refundable administrative fee will be charged
                                    upon submission</li>
                                <li><strong>Academic Impact:</strong> Deferring may affect your graduation timeline and
                                    course sequence</li>
                                <li><strong>Financial Aid:</strong> Your financial aid status may be affected by the
                                    deferment</li>
                                <li><strong>Approval Not Guaranteed:</strong> The penalty applies even if your request
                                    is rejected</li>
                                <li><strong>Alternative Options:</strong> Consider discussing alternatives with your
                                    academic advisor first</li>
                            </ul>
                        </div>

                        <!-- Recommendation -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-blue-600 text-xl">💡</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-800">Recommendation</h4>
                                    <p class="text-sm text-blue-700 mt-1">
                                        We strongly recommend consulting with your academic advisor or the internship
                                        coordinator before submitting a defer request to explore all available options.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-800 mb-2">Need Help?</h4>
                            <p class="text-sm text-gray-600">
                                Contact the Academic Office or your assigned advisor for guidance before proceeding with
                                your defer request.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">⚠️ Remember:</span> Penalty fees apply regardless of approval
                            status
                        </div>
                        <div class="flex space-x-3">
                            <button wire:click="cancelRequest" type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button wire:click="proceedWithRequest" type="button"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                I Understand, Proceed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Defer Request Form Modal -->
    @if ($showForm)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="closeForm"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $editingId ? 'Edit Defer Request' : 'Submit Defer Request' }}
                    </h3>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <form wire:submit.prevent="submit">
                        <!-- Reason -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Defer *</label>
                            <textarea wire:model="reason" rows="4"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm @error('reason') border-red-500 @enderror"
                                placeholder="Please provide a detailed reason for your defer request..."></textarea>
                            @error('reason')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Defer Start Date *</label>
                            <input type="date" wire:model="startDate"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm @error('startDate') border-red-500 @enderror">
                            @error('startDate')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Defer End Date *</label>
                            <input type="date" wire:model="endDate"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm @error('endDate') border-red-500 @enderror">
                            @error('endDate')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supporting Documents</label>
                            <input type="file" wire:model="applicationFiles" multiple
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm @error('applicationFiles.*') border-red-500 @enderror"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <p class="text-xs text-gray-500 mt-1">Upload supporting documents (PDF, DOC, DOCX, JPG,
                                PNG). Max 10MB each.</p>
                            @error('applicationFiles.*')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <!-- Show existing files when editing -->
                            @if ($editingId && !empty($existingFiles))
                                <div class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current Files:</p>
                                    <div class="space-y-1">
                                        @foreach ($existingFiles as $file)
                                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                                <span>📄</span>
                                                <span>{{ $file['original_name'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Upload new files to replace existing ones.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- File upload progress -->
                        @if ($applicationFiles)
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Files to upload:</p>
                                <div class="space-y-1">
                                    @foreach ($applicationFiles as $file)
                                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                                            <span>📄</span>
                                            <span>{{ $file->getClientOriginalName() }}</span>
                                            <span
                                                class="text-gray-400">({{ number_format($file->getSize() / 1024, 1) }}
                                                KB)</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                    <button wire:click="closeForm" type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="submit" type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        {{ $editingId ? 'Update Request' : 'Submit Request' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Request Details Modal -->
    @if ($showViewModal && $viewingRequest)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="closeViewModal"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Defer Request #{{ $viewingRequest->deferID }} - Details
                        </h3>
                        <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">✖</button>
                    </div>
                    <div class="mt-2">
                        <span class="text-sm text-blue-600 bg-blue-50 px-2 py-1 rounded">
                            📖 Read-Only View
                        </span>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Request Details -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Request Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Request ID:</strong> #{{ $viewingRequest->deferID }}</p>
                                <p><strong>Start Date:</strong> {{ $viewingRequest->startDate->format('M d, Y') }}</p>
                                <p><strong>End Date:</strong> {{ $viewingRequest->endDate->format('M d, Y') }}</p>
                                <p><strong>Application Date:</strong>
                                    {{ $viewingRequest->applicationDate->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Approval Status -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Approval Status</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Committee:</span>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $viewingRequest->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingRequest->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingRequest->committeeStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Coordinator:</span>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $viewingRequest->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingRequest->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingRequest->coordinatorStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Overall Status:</span>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $viewingRequest->overall_status === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingRequest->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingRequest->overall_status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Reviewer Information -->
                        @if ($viewingRequest->committee || $viewingRequest->coordinator)
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-3">Reviewers</h4>
                                <div class="space-y-2 text-sm">
                                    @if ($viewingRequest->committee)
                                        <p><strong>Committee Member:</strong>
                                            {{ $viewingRequest->committee->user->name }}</p>
                                    @endif
                                    @if ($viewingRequest->coordinator)
                                        <p><strong>Coordinator:</strong> {{ $viewingRequest->coordinator->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Reason -->
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-3">Reason for Defer</h4>
                            <div class="bg-gray-50 p-3 rounded border text-sm">
                                {{ $viewingRequest->reason }}
                            </div>
                        </div>

                        <!-- Files -->
                        @if ($viewingRequest->files->count() > 0)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Submitted Files</h4>
                                <div class="space-y-2">
                                    @foreach ($viewingRequest->files as $file)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded border">
                                            <div class="flex items-center space-x-3">
                                                <span>📄</span>
                                                <div>
                                                    <p class="text-sm font-medium">{{ $file->original_name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $file->mime_type }} •
                                                        {{ number_format($file->file_size / 1024, 1) }} KB</p>
                                                </div>
                                            </div>
                                            <a href="{{ $file->url }}" target="_blank"
                                                class="text-blue-600 hover:text-blue-900 text-sm">
                                                View File
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Remarks -->
                        @if ($viewingRequest->remarks)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Lecturer Remarks</h4>
                                <div class="bg-blue-50 p-3 rounded border text-sm">
                                    {{ $viewingRequest->remarks }}
                                </div>
                            </div>
                        @endif

                        <!-- Information Notice -->
                        <div class="md:col-span-2">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <div class="flex items-start space-x-2">
                                    <span class="text-yellow-600">ℹ️</span>
                                    <div class="text-sm text-yellow-800">
                                        <p class="font-medium mb-1">Editing Restrictions:</p>
                                        @if ($viewingRequest->committeeStatus !== 'Pending' || $viewingRequest->coordinatorStatus !== 'Pending')
                                            <p>This request cannot be edited because it has been reviewed by committee
                                                or coordinator. You can only edit requests that are still pending review
                                                by both parties.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button wire:click="closeViewModal"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
