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

        .fa-plus:before {
            content: "➕";
        }

        .fa-edit:before {
            content: "✏️";
        }

        .fa-trash:before {
            content: "🗑️";
        }

        .fa-file:before {
            content: "📄";
        }

        .fa-times:before {
            content: "✖";
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
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Course Verification</h1>
                <p class="text-gray-600">Submit and manage your course verification applications</p>
            </div>

            <!-- Total Credit Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-blue-900">Credit Requirements</h3>
                        <p class="text-blue-700">
                            <strong>Total Credits Required:</strong> {{ $totalCreditRequired }} credits
                        </p>
                        <p class="text-sm text-blue-600 mt-1">
                            Submit your current credit count and course documentation for verification.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Current Application Status -->
            @if ($currentApplication)
                <div class="mb-6">
                    @php
                        $statusClasses = [
                            'pending' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                            'approved' => 'bg-green-50 border-green-200 text-green-800',
                            'rejected' => 'bg-red-50 border-red-200 text-red-800',
                        ];
                        $statusIcons = [
                            'pending' => 'fa-clock',
                            'approved' => 'fa-check-circle',
                            'rejected' => 'fa-times-circle',
                        ];
                    @endphp
                    <div
                        class="border rounded-lg p-4 {{ $statusClasses[$currentApplication->status] ?? 'bg-gray-50 border-gray-200 text-gray-800' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa {{ $statusIcons[$currentApplication->status] ?? 'fa-file' }} text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium">Current Application Status:
                                    {{ ucfirst($currentApplication->status) }}</h3>
                                <p class="text-sm mt-1">
                                    Application ID: {{ $currentApplication->courseVerificationID }} |
                                    Submitted: {{ $currentApplication->applicationDate->format('M d, Y') }} |
                                    Credits: {{ $currentApplication->currentCredit }}/{{ $totalCreditRequired }}
                                </p>
                                @if ($currentApplication->status === 'pending')
                                    <p class="text-sm mt-2">
                                        <strong>Your application is under review.</strong> You cannot submit a new
                                        application while this one is pending.
                                    </p>
                                @elseif($currentApplication->status === 'approved')
                                    <p class="text-sm mt-2">
                                        <strong>Congratulations!</strong> Your course verification has been approved. No
                                        further action is needed.
                                    </p>
                                @elseif($currentApplication->status === 'rejected')
                                    <p class="text-sm mt-2">
                                        <strong>Your application was rejected.</strong> You can submit a new application
                                        with updated information.
                                    </p>
                                @endif

                                @if (in_array($currentApplication->status, ['approved', 'rejected']) && $currentApplication->remarks)
                                    <div class="mt-4 p-3 bg-white border border-gray-200 rounded-md">
                                        <h5 class="text-sm font-semibold text-gray-900 mb-2">Lecturer's Remarks:</h5>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">
                                            {{ $currentApplication->remarks }}</p>
                                        @if ($currentApplication->lecturerID)
                                            <p class="text-xs text-gray-500 mt-2">Reviewed by:
                                                {{ $currentApplication->lecturerID }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
                                placeholder="Search applications...">
                        </div>
                    </div>

                    <!-- Apply Button -->
                    <div class="flex items-center gap-2">
                        @if ($canApply)
                            <button wire:click="openForm"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fa fa-plus mr-2"></i>
                                @if ($currentApplication && $currentApplication->status === 'rejected')
                                    Apply Again
                                @else
                                    Apply for Verification
                                @endif
                            </button>
                        @else
                            @if ($currentApplication)
                                @if ($currentApplication->status === 'pending')
                                    <div
                                        class="inline-flex items-center px-4 py-2 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-yellow-100">
                                        <i class="fa fa-clock mr-2"></i>
                                        Application Under Review
                                    </div>
                                @elseif($currentApplication->status === 'approved')
                                    <div
                                        class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-100">
                                        <i class="fa fa-check-circle mr-2"></i>
                                        Verification Approved
                                    </div>
                                @endif
                            @else
                                <div
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-gray-100">
                                    <i class="fa fa-ban mr-2"></i>
                                    Cannot Apply
                                </div>
                            @endif
                        @endif
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
                                        ID
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
                                    Remarks
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             @forelse($verifications as $verification)
                                 <tr class="hover:bg-gray-50 {{ $verification->status === 'approved' ? 'bg-green-50 border-l-4 border-green-400' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <div class="flex items-center">
                                            @if($verification->status === 'approved')
                                                <i class="fa fa-check-circle text-green-500 mr-2" title="Approved"></i>
                                            @endif
                                            {{ $verification->courseVerificationID }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $verification->currentCredit }} / {{ $totalCreditRequired }}
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
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$verification->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($verification->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $verification->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                        @if (in_array($verification->status, ['approved', 'rejected']) && $verification->remarks)
                                            <div class="truncate" title="{{ $verification->remarks }}">
                                                {{ Str::limit($verification->remarks, 50) }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- View File -->
                                            @if ($verification->files->count() > 0)
                                                <a href="{{ $verification->files->first()->url }}"
                                                    target="_blank" class="text-blue-600 hover:text-blue-900 title"
                                                    title="View submitted file">
                                                    <i class="fa fa-file"></i>
                                                </a>
                                            @endif

                                            <!-- Edit/Delete (only for current application and appropriate status) -->
                                            @if ($currentApplication && $currentApplication->courseVerificationID === $verification->courseVerificationID)
                                                @if (in_array($verification->status, ['pending', 'rejected']))
                                                    <button
                                                        wire:click="edit({{ $verification->courseVerificationID }})"
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                        title="Edit application">
                                                        <i class="fa fa-edit"></i>
                                                    </button>

                                                    <!-- Delete (only for current application) -->
                                                    <button
                                                        wire:click="deleteApplication({{ $verification->courseVerificationID }})"
                                                        wire:confirm="Are you sure you want to delete this application?"
                                                        class="text-red-600 hover:text-red-900"
                                                        title="Delete application">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fa fa-file text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No applications found</p>
                                            @if ($canApply)
                                                <p class="text-sm">Click "Apply for Verification" to submit your
                                                    application.</p>
                                            @else
                                                <p class="text-sm">You cannot apply for course verification at this
                                                    time.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $verifications->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Application Form Modal -->
    @if ($showForm)
        <div class="modal-overlay">
            <div class="modal-content bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
                <form wire:submit="submit">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $editingId ? 'Edit' : 'Apply for' }} Course Verification
                            </h3>
                            <button type="button" wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                                <i class="fa fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <!-- Total Credit Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-sm text-blue-700">
                                <strong>Total Credits Required:</strong> {{ $totalCreditRequired }} credits
                            </p>
                        </div>

                        <!-- Current Credit -->
                        <div>
                            <label for="currentCredit" class="block text-sm font-medium text-gray-700 mb-1">
                                Current Credit <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model="currentCredit" id="currentCredit" min="0"
                                max="118"
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your current credit count">
                            @error('currentCredit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label for="submittedFile" class="block text-sm font-medium text-gray-700 mb-1">
                                Course File <span class="text-red-500">*</span>
                            </label>
                            <input type="file" wire:model="submittedFile" id="submittedFile"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">
                                Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)
                            </p>
                            @error('submittedFile')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" wire:click="closeForm"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                            <span wire:loading.remove>
                                {{ $editingId ? 'Update' : 'Submit' }} Application
                            </span>
                            <span wire:loading>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
