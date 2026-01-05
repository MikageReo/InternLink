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

    <script>
        function validateFileSize(input) {
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            const errorDiv = document.getElementById('fileSizeError');

            if (input.files && input.files[0]) {
                if (input.files[0].size > maxSize) {
                    errorDiv.classList.remove('hidden');
                    input.value = ''; // Clear the file input
                    return false;
                } else {
                    errorDiv.classList.add('hidden');
                }
            }
            return true;
        }

        function validateCreditInput(input) {
            // Get min and max values from data attributes
            const minCredit = parseInt(input.getAttribute('data-min-credit')) || 118;
            const maxCredit = parseInt(input.getAttribute('data-max-credit')) || 130;

            // Remove non-numeric characters
            let value = input.value.replace(/[^0-9]/g, '');

            // Limit to 3 digits
            if (value.length > 3) {
                value = value.substring(0, 3);
            }

            // Enforce range dynamically based on settings
            if (value) {
                const numValue = parseInt(value);
                if (numValue < minCredit) {
                    // If user is typing and value is less than minimum, allow typing but will validate on blur/submit
                    // Don't auto-correct while typing to allow user to type "1" then "18" then "118" (or whatever min is)
                } else if (numValue > maxCredit) {
                    value = maxCredit.toString();
                }
            }

            input.value = value;
        }
    </script>

    <!-- Course Verification Guide -->
    <div class="bg-blue-50 dark:bg-white-100/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 mt-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-info-circle text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-900">Course Verification Guide</h3>
                            <p class="text-sm text-blue-600 mt-1">
                                @if (!$showGuide)
                                    Fill in verification form → Prepare Documents → Merge files → Submit → Academic Advisor Review → Coordinator Approval
                                @else
                                    Follow these steps to complete your course verification submission and understand the approval process.
                                @endif
                            </p>
                        </div>
                    </div>
                    <button wire:click="$set('showGuide', {{ $showGuide ? 'false' : 'true' }})"
                        class="ml-4 inline-flex items-center px-3 py-2 border border-blue-400 rounded-md text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fa {{ $showGuide ? 'fa-chevron-up' : 'fa-chevron-down' }} mr-2"></i>
                        {{ $showGuide ? 'Hide Guide' : 'Show Full Guide' }}
                    </button>
                </div>

                @if ($showGuide)
                <div class="mt-4 space-y-4">
                    <!-- Step 1 -->
                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-semibold text-sm">1</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 mb-2">
                                    Fill in the course verification form with your current credit information.
                                </p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <a href="{{ asset('documents/Course-Verification-Form-Eng.docx') }}"
                                       target="_blank"
                                       download
                                       class="inline-flex items-center px-3 py-1.5 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50">
                                        <i class="fa fa-download mr-1.5"></i>
                                        English Version
                                    </a>
                                    <a href="{{ asset('documents/Course-Verification-Form-Malay.docx') }}"
                                       target="_blank"
                                       download
                                       class="inline-flex items-center px-3 py-1.5 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50">
                                        <i class="fa fa-download mr-1.5"></i>
                                        Malay Version
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-semibold text-sm">2</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    Please ask your academic advisor for the <strong>List of Taken / Untaken Courses</strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-semibold text-sm">3</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 mb-2">
                                    Screenshot the <strong>Registered Courses</strong> in E-COMMUNITY (My Course Results → Course Structure).
                                </p>
                                <div class="mt-2">
                                    <a href="{{ asset('documents/Registered-Course-Guide.pdf') }}"
                                       target="_blank"
                                       download
                                       class="inline-flex items-center px-3 py-1.5 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50">
                                        <i class="fa fa-download mr-1.5"></i>
                                        Screenshot Guide
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-semibold text-sm">4</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 mb-2">
                                    Once approved, please merge the <strong>List of Taken / Untaken Courses</strong> and <strong>Registered Courses</strong> together with verification form and save the file as PDF or ZIP file.
                                </p>
                                <p class="text-sm text-gray-700 mt-2">
                                    <strong>File naming convention:</strong><br>
                                    For PDF: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ Auth::user()->student->studentID ?? 'matricID' }}_CClist.pdf</code><br>
                                    For ZIP: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ Auth::user()->student->studentID ?? 'matricID' }}_CClist.zip</code>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-semibold text-sm">5</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    Submit the merged verification form at the course verification form below.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6 - Approval Process -->
                    <div class="bg-white rounded-lg p-4 border-2 border-green-200 bg-green-50">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-green-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                <span class="text-green-600 font-semibold text-sm">6</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 mb-2">
                                    <strong>Approval Process (Two-Step Review):</strong>
                                </p>
                                <div class="space-y-2 mt-2">
                                    <div class="flex items-start">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-600 text-xs font-semibold mr-2 mt-0.5">A</span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Academic Advisor Review</p>
                                            <p class="text-xs text-gray-600 mt-1">
                                                Your academic advisor will review your application to determine if it is <strong>eligible</strong> for coordinator approval.
                                                You will be notified once the review is complete.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-purple-100 text-purple-600 text-xs font-semibold mr-2 mt-0.5">B</span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Coordinator Review</p>
                                            <p class="text-xs text-gray-600 mt-1">
                                                If approved by your academic advisor, the application will be forwarded to the coordinator for <strong>final approval</strong>.
                                                You will receive a final notification once the coordinator makes a decision.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <p class="text-xs text-yellow-800">
                                        <i class="fa fa-info-circle mr-1"></i>
                                        <strong>Note:</strong> You can track the status of your application in the table below.
                                        The status will show both Academic Advisor and Coordinator review stages.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credit Requirements Info -->
                <div class="mt-4 pt-4 border-t border-blue-200">
                    <p class="text-sm text-blue-700">
                        <strong>Total Credits Required:</strong> {{ $totalCreditRequired }} credits
                    </p>
                </div>
                @endif
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
                            <div class="ml-3 flex-1">
                                <h3 class="text-lg font-medium">Current Application Status</h3>
                                <p class="text-sm mt-1">
                                    Application ID: {{ $currentApplication->courseVerificationID }} |
                                    Submitted: {{ $currentApplication->applicationDate->format('M d, Y') }} |
                                    Credits: {{ $currentApplication->currentCredit }}/{{ $totalCreditRequired }}
                                </p>

                                <!-- Status Breakdown -->
                                <div class="mt-3 space-y-2">
                                    <!-- Academic Advisor Status -->
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Academic Advisor Review:</span>
                                        @if($currentApplication->academicAdvisorStatus === 'approved')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                <i class="fa fa-check mr-1"></i>Approved (Eligible)
                                            </span>
                                        @elseif($currentApplication->academicAdvisorStatus === 'rejected')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                <i class="fa fa-times mr-1"></i>Rejected (Ineligible)
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                                <i class="fa fa-clock mr-1"></i>Pending Review
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Coordinator Status -->
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Coordinator Review:</span>
                                        @if($currentApplication->academicAdvisorStatus === 'rejected')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                Not Applicable
                                            </span>
                                        @elseif($currentApplication->academicAdvisorStatus === 'approved')
                                            @if($currentApplication->status === 'approved')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                    <i class="fa fa-check mr-1"></i>Approved
                                                </span>
                                            @elseif($currentApplication->status === 'rejected')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                    <i class="fa fa-times mr-1"></i>Rejected
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                                    <i class="fa fa-clock mr-1"></i>Pending Review
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Awaiting Academic Advisor Approval
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status Messages -->
                                @if ($currentApplication->status === 'pending')
                                    @if($currentApplication->academicAdvisorStatus === null)
                                        <p class="text-sm mt-3 text-yellow-700">
                                            <strong>Your application is awaiting academic advisor review.</strong> You cannot submit a new application while this one is pending.
                                        </p>
                                    @elseif($currentApplication->academicAdvisorStatus === 'approved')
                                        <p class="text-sm mt-3 text-yellow-700">
                                            <strong>Your application has been approved by your academic advisor and is now awaiting coordinator review.</strong> You cannot submit a new application while this one is pending.
                                        </p>
                                    @endif
                                @elseif($currentApplication->status === 'approved')
                                    <p class="text-sm mt-3 text-green-700">
                                        <strong>Congratulations!</strong> Your course verification has been fully approved. No further action is needed.
                                    </p>
                                @elseif($currentApplication->status === 'rejected')
                                    @if ($hasApprovedApplication)
                                        <p class="text-sm mt-3 text-red-700">
                                            <strong>This application was rejected.</strong> However, you have a previously approved verification and cannot submit new applications.
                                        </p>
                                    @else
                                        <p class="text-sm mt-3 text-red-700">
                                            <strong>Your application was rejected.</strong> You can submit a new application with updated information.
                                        </p>
                                    @endif
                                @endif

                                <!-- Remarks -->
                                @if($currentApplication->remarks)
                                    <div class="mt-3 p-3 bg-gray-50 rounded-md">
                                        <p class="text-xs font-medium text-gray-700 mb-1">Remarks:</p>
                                        <p class="text-sm text-gray-900">{{ $currentApplication->remarks }}</p>
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
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
                            @if ($hasApprovedApplication)
                                <div
                                    class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-100">
                                    <i class="fa fa-check-circle mr-2"></i>
                                    Verification Approved
                                </div>
                            @elseif ($currentApplication)
                                @if ($currentApplication->status === 'pending')
                                    <div
                                        class="inline-flex items-center px-4 py-2 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-yellow-100">
                                        <i class="fa fa-clock mr-2"></i>
                                        Application Under Review
                                    </div>
                                @endif
                            @else
                                <div
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700">
                                    <i class="fa fa-ban mr-2"></i>
                                    Cannot Apply
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('courseVerificationID')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('currentCredit')"
                                        class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <button wire:click="sortBy('status')" class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
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
                                    Remarks
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                             @forelse($verifications as $verification)
                                 <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $verification->status === 'approved' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-500' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <div class="flex items-center">
                                            @if($verification->status === 'approved')
                                                <i class="fa fa-check-circle text-green-500 mr-2" title="Approved"></i>
                                            @endif
                                            {{ $verification->courseVerificationID }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $verification->currentCredit }} / {{ $totalCreditRequired }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                                'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                                'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                            ];
                                        @endphp
                                        <div class="flex flex-col gap-1">
                                            <!-- Academic Advisor Status -->
                                            @if($verification->academicAdvisorStatus)
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$verification->academicAdvisorStatus] ?? 'bg-gray-100 text-gray-800' }}"
                                                    title="Academic Advisor Review">
                                                    Academic Advisor: {{ ucfirst($verification->academicAdvisorStatus) }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300"
                                                    title="Awaiting Academic Advisor Review">
                                                    Academic Advisor: Pending
                                                </span>
                                            @endif

                                            <!-- Coordinator Status -->
                                            @if($verification->academicAdvisorStatus === 'approved')
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$verification->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}"
                                                    title="Coordinator Review">
                                                    Coordinator: {{ ucfirst($verification->status) }}
                                                </span>
                                            @elseif($verification->academicAdvisorStatus === 'rejected')
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300"
                                                    title="Rejected by Academic Advisor">
                                                    Coordinator: Not Applicable
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300"
                                                    title="Awaiting Academic Advisor Approval">
                                                    Coordinator: Pending
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $verification->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 max-w-xs">
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


                                            <!-- Edit/Delete (only for current application and appropriate status) -->
                                            @if ($currentApplication && $currentApplication->courseVerificationID === $verification->courseVerificationID && !$hasApprovedApplication)
                                                @if (in_array($verification->status, ['pending', 'rejected']) && $verification->academicAdvisorStatus !== 'approved')
                                                    <button
                                                        wire:click="edit({{ $verification->courseVerificationID }})"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                                                        title="Edit application">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                        </svg>
                                                        <span>Edit</span>
                                                    </button>

                                                    <!-- Delete (only for current application) -->
                                                    <button
                                                        wire:click="deleteApplication({{ $verification->courseVerificationID }})"
                                                        wire:confirm="Are you sure you want to delete this application?"
                                                        wire:loading.attr="disabled"
                                                        wire:target="deleteApplication({{ $verification->courseVerificationID }})"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                        title="Delete application">
                                                        <span wire:loading.remove wire:target="deleteApplication({{ $verification->courseVerificationID }})" class="flex items-center gap-1.5">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                            <span>Delete</span>
                                                        </span>
                                                        <span wire:loading wire:target="deleteApplication({{ $verification->courseVerificationID }})">
                                                            <x-loading-spinner size="h-4 w-4" color="text-red-600" />
                                                        </span>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <i class="fa fa-file text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                            <p class="text-lg font-medium mb-2 text-gray-900 dark:text-gray-100">No applications found</p>
                                            @if ($canApply)
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Click "Apply for Verification" to submit your
                                                    application.</p>
                                            @else
                                                <p class="text-sm text-gray-600 dark:text-gray-400">You cannot apply for course verification at this
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
                <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    {{ $verifications->links() }}
                </div>
            </div>

    <!-- Application Form Modal -->
    @if ($showForm)
        <div class="modal-overlay">
            <div class="modal-content bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4">
                <form wire:submit="submit">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $editingId ? 'Edit' : 'Apply for' }} Course Verification
                            </h3>
                            <button type="button" wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                                <i class="fa fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <!-- Total Credit Info -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                            @php
                                $settings = \App\Models\CourseVerificationSetting::getSettings();
                            @endphp
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <strong>Credit Requirements:</strong> Minimum {{ $settings->minimum_credit_hour }} - Maximum {{ $settings->maximum_credit_hour }} credits
                            </p>
                        </div>

                        <!-- Current Credit -->
                        <div>
                            <label for="currentCredit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Total Taken Current Credit <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="currentCredit" id="currentCredit" maxlength="3"
                                data-min-credit="{{ $settings->minimum_credit_hour }}"
                                data-max-credit="{{ $settings->maximum_credit_hour }}"
                                oninput="validateCreditInput(this)"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your current credit count ({{ $settings->minimum_credit_hour }}-{{ $settings->maximum_credit_hour }})">
                            @error('currentCredit')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label for="submittedFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Course File <span class="text-red-500">*</span>
                            </label>
                            <input type="file" wire:model="submittedFile" id="submittedFile"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                                onchange="validateFileSize(this)"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, ZIP (Max: 5MB)
                            </p>
                            <div id="fileSizeError" class="mt-1 text-sm text-red-600 dark:text-red-400 hidden">
                                File size cannot exceed 5MB.
                            </div>
                            @error('submittedFile')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" wire:click="closeForm"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
