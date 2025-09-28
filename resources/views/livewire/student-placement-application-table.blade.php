<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Internship Placement Applications</h1>
                <p class="text-gray-600">Submit and manage your internship placement applications</p>
            </div>

            <!-- Course Verification Alert -->
            @if (!$canApply)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-900">Course Verification Required</h3>
                            <p class="text-red-700">You must have an approved course verification before applying for internship placement.</p>
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
                        <h2 class="text-lg font-medium text-gray-900">Your Applications</h2>
                        @if ($canApply)
                            <button wire:click="openForm" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                ➕ New Application
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <input type="text" wire:model.live.debounce.300ms="search"
                               class="flex-1 border border-gray-300 rounded-md px-3 py-2"
                               placeholder="Search applications...">
                        <select wire:model.live="statusFilter" class="border border-gray-300 rounded-md px-3 py-2">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($applications as $application)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $application->applicationID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->companyName }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->position }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs space-y-1">
                                            <div>
                                                <span class="font-medium">Committee:</span>
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' :
                                                       ($application->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $application->committeeStatus }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Coordinator:</span>
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' :
                                                       ($application->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $application->coordinatorStatus }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($application->remarks)
                                            <div class="max-w-xs truncate" title="{{ $application->remarks }}">
                                                {{ $application->remarks }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- Student Acceptance Actions -->
                                            @if ($application->can_accept && !$hasAcceptedApplication)
                                                <button wire:click="acceptApplication({{ $application->applicationID }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700 hover:bg-green-200">
                                                    ✅ Accept
                                                </button>
                                                <button wire:click="declineApplication({{ $application->applicationID }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700 hover:bg-red-200">
                                                    ❌ Decline
                                                </button>
                                            @elseif ($application->studentAcceptance)
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->studentAcceptance === 'Accepted' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $application->studentAcceptance }}
                                                </span>
                                            @endif

                                            <!-- View/Edit Actions -->
                                            @if ($application->committeeStatus === 'Pending' && $application->coordinatorStatus === 'Pending')
                                                <!-- Edit button - only when both are pending -->
                                                <button wire:click="edit({{ $application->applicationID }})"
                                                    class="text-blue-600 hover:text-blue-900" title="Edit application">
                                                    ✏️ Edit
                                                </button>
                                            @else
                                                <!-- View button - when any approval has been made -->
                                                <button wire:click="view({{ $application->applicationID }})"
                                                    class="text-gray-600 hover:text-gray-900" title="View details (read-only)">
                                                    👁️ View
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <p class="text-lg font-medium mb-2">No applications found</p>
                                            <p class="text-sm">You haven't submitted any internship applications yet.</p>
                                            @if ($canApply)
                                                <button wire:click="openForm"
                                                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                    Submit Your First Application
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($applications->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Application Form Modal -->
    @if ($showForm)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
             wire:click="closeForm"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $editingId ? 'Edit Application' : 'New Internship Application' }}
                        </h3>
                        <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                            ✖
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Company Information Section -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Company Information</h4>
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                            <input type="text" wire:model="companyName" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('companyName')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Company Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Email *</label>
                            <input type="email" wire:model="companyEmail" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('companyEmail')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Company Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Address *</label>
                            <textarea wire:model="companyAddress" rows="3" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                            @error('companyAddress')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Company Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Phone *</label>
                            <input type="text" wire:model="companyNumber" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('companyNumber')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Monthly Allowance -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Allowance (RM)</label>
                            <input type="number" wire:model="allowance" step="0.01" min="0" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('allowance')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Position Information Section -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Position Details</h4>
                        </div>

                        <!-- Position -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                            <input type="text" wire:model="position" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('position')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Method of Work -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Method of Work *</label>
                            <select wire:model="methodOfWork" class="w-full border border-gray-300 rounded px-3 py-2">
                                <option value="">Select method of work</option>
                                <option value="WFO">Work From Office</option>
                                <option value="WOS">Work On Site</option>
                                <option value="WOC">Work On Campus</option>
                                <option value="WFH">Work From Home</option>
                                <option value="WFO & WFH">Hybrid (Office & Home)</option>
                            </select>
                            @error('methodOfWork')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Job Scope -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Scope *</label>
                            <textarea wire:model="jobscope" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"
                                      placeholder="Describe the main responsibilities and tasks..."></textarea>
                            @error('jobscope')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Duration Section -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Internship Duration</h4>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                            <input type="date" wire:model="startDate" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('startDate')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                            <input type="date" wire:model="endDate" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('endDate')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- File Upload Section -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Supporting Documents</h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Files</label>
                                <input type="file" wire:model="applicationFiles" multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       class="w-full border border-gray-300 rounded px-3 py-2">
                                <p class="text-sm text-gray-500 mt-1">
                                    You can upload multiple files. Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB each)
                                </p>
                                @error('applicationFiles.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- Show existing files if editing -->
                            @if ($editingId && !empty($existingFiles))
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current Files:</p>
                                    <div class="space-y-2">
                                        @foreach ($existingFiles as $file)
                                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                                <span>📄</span>
                                                <span>{{ $file['original_name'] ?? 'File' }}</span>
                                                <span class="text-gray-400">({{ number_format($file['file_size'] / 1024, 1) }} KB)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Note: Uploading new files will replace existing files.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="closeForm" type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="submit" type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        {{ $editingId ? 'Update Application' : 'Submit Application' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Application Details Modal -->
    @if ($showViewModal && $viewingApplication)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
             wire:click="closeViewModal"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Application #{{ $viewingApplication->applicationID }} - Details
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
                        <!-- Company Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Company Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Company Name:</strong> {{ $viewingApplication->companyName }}</p>
                                <p><strong>Email:</strong> {{ $viewingApplication->companyEmail }}</p>
                                <p><strong>Phone:</strong> {{ $viewingApplication->companyNumber }}</p>
                                <p><strong>Address:</strong> {{ $viewingApplication->companyAddress }}</p>
                            </div>
                        </div>

                        <!-- Position Details -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Position Details</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Position:</strong> {{ $viewingApplication->position }}</p>
                                <p><strong>Method of Work:</strong> {{ $viewingApplication->methodOfWorkDisplay }}</p>
                                <p><strong>Allowance:</strong> {{ $viewingApplication->allowance ? 'RM ' . number_format($viewingApplication->allowance, 2) : 'Not specified' }}</p>
                                <p><strong>Duration:</strong> {{ $viewingApplication->startDate->format('M d, Y') }} - {{ $viewingApplication->endDate->format('M d, Y') }}</p>
                                <p><strong>Application Date:</strong> {{ $viewingApplication->applicationDate->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Approval Status -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Approval Status</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Committee:</span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->committeeStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Coordinator:</span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->coordinatorStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Overall Status:</span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->overall_status === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->overall_status }}
                                    </span>
                                </div>
                                @if($viewingApplication->studentAcceptance)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">Your Response:</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->studentAcceptance === 'Accepted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $viewingApplication->studentAcceptance }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Reviewer Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Reviewers</h4>
                            <div class="space-y-2 text-sm">
                                @if($viewingApplication->committee)
                                    <p><strong>Committee Member:</strong> {{ $viewingApplication->committee->user->name }}</p>
                                @endif
                                @if($viewingApplication->coordinator)
                                    <p><strong>Coordinator:</strong> {{ $viewingApplication->coordinator->user->name }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Job Scope -->
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-3">Job Scope</h4>
                            <div class="bg-gray-50 p-3 rounded border text-sm">
                                {{ $viewingApplication->jobscope }}
                            </div>
                        </div>

                        <!-- Files -->
                        @if($viewingApplication->files->count() > 0)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Submitted Files</h4>
                                <div class="space-y-2">
                                    @foreach($viewingApplication->files as $file)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded border">
                                            <div class="flex items-center space-x-3">
                                                <span>📄</span>
                                                <div>
                                                    <p class="text-sm font-medium">{{ $file->original_name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $file->mime_type }} • {{ number_format($file->file_size / 1024, 1) }} KB</p>
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
                        @if($viewingApplication->remarks)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Lecturer Remarks</h4>
                                <div class="bg-blue-50 p-3 rounded border text-sm">
                                    {{ $viewingApplication->remarks }}
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
                                        @if($viewingApplication->committeeStatus !== 'Pending' || $viewingApplication->coordinatorStatus !== 'Pending')
                                            <p>This application cannot be edited because it has been reviewed by committee or coordinator. You can only edit applications that are still pending review by both parties.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button wire:click="closeViewModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
