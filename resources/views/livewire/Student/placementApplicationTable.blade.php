<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Restrict numeric fields to numbers only
            const numericFields = document.querySelectorAll('input[pattern="[0-9]{1,10}"], input[pattern="[0-9]{1,15}"], input[pattern="[0-9]{1,5}"]');

            numericFields.forEach(function(field) {
                // Prevent paste of non-numeric characters
                field.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const numericOnly = paste.replace(/[^0-9]/g, '');
                    field.value = numericOnly;
                    // Trigger Livewire update
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                });

                field.addEventListener('input', function(e) {
                    // Remove any non-numeric characters
                    const originalValue = e.target.value;
                    const numericOnly = originalValue.replace(/[^0-9]/g, '');
                    if (originalValue !== numericOnly) {
                        e.target.value = numericOnly;
                        // Trigger Livewire update
                        e.target.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });

                field.addEventListener('keypress', function(e) {
                    // Only allow numeric keys
                    if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Placement Application Guide -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-info-circle text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-900">Placement Application Guide</h3>
                            <p class="text-sm text-blue-600 mt-1">
                                @if (!$showGuide)
                                    Choose placement → Email employer → Prepare interview → Receive documents → Submit
                                    for approval → Confirm details
                                @else
                                    Follow these steps to complete your placement application submission.
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
                                <div
                                    class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-semibold text-sm">1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 mb-2">
                                        <strong>Choose Your Preferred Placement</strong>
                                    </p>
                                    <ul class="text-sm text-gray-700 space-y-1 ml-4 list-disc mb-3">
                                        <li>Search for suitable internship opportunities related to your course.</li>
                                        <li>You may use platforms such as Indeed, JobStreet, LinkedIn, or any other
                                            trusted source.</li>
                                    </ul>
                                    <div class="mt-3">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Recommended Internship Platforms:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="https://mytalenthub.com.my/"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-3 py-2 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <i class="fa fa-external-link mr-1.5"></i>
                                                MyTalent Hub
                                            </a>
                                            <a href="https://www.mynext.my/"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-3 py-2 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <i class="fa fa-external-link mr-1.5"></i>
                                                MyNext
                                            </a>
                                            <a href="https://internseek.raegrp.com/"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-3 py-2 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <i class="fa fa-external-link mr-1.5"></i>
                                                InternSeek
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-semibold text-sm">2</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 mb-2">
                                        <strong>Send an Email Inquiry to the Employer</strong>
                                    </p>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Email your internship inquiry to the chosen company. Attach the following
                                        documents in your email:
                                    </p>
                                    <ul class="text-sm text-gray-700 space-y-1 ml-4 list-disc mb-2">
                                        <li>Resume / Curriculum Vitae (CV)</li>
                                        <li>Partial Academic transcript</li>
                                        <li>Reply Form (Student Information section completed)</li>
                                        <li>Student Application Letter (SAL)</li>
                                    </ul>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-2 mt-2">
                                        <p class="text-xs text-yellow-800">
                                            <strong>💡 Tip:</strong> Write a polite and professional email. Ensure all
                                            attachments are clearly named.
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <a href="{{ asset('documents/Reply-Form.docx') }}" target="_blank" download
                                            class="inline-flex items-center px-3 py-1.5 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50">
                                            <i class="fa fa-download mr-1.5"></i>
                                            Reply Form
                                        </a>
                                        <a href="{{ asset('documents/Student-Application-Letter.docx') }}"
                                            target="_blank" download
                                            class="inline-flex items-center px-3 py-1.5 border border-blue-400 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50">
                                            <i class="fa fa-download mr-1.5"></i>
                                            Student Application Letter (SAL)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-semibold text-sm">3</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 mb-2">
                                        <strong>Prepare for Interview</strong>
                                    </p>
                                    <ul class="text-sm text-gray-700 space-y-1 ml-4 list-disc">
                                        <li>Be ready for potential interviews.</li>
                                        <li>Research the company background to prepare yourself better.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-semibold text-sm">4</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 mb-2">
                                        <strong>Documents You Should Receive from the Employer</strong>
                                    </p>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Once the company confirms your placement, ensure you receive:
                                    </p>
                                    <ol class="text-sm text-gray-700 space-y-1 ml-4 list-decimal">
                                        <li><strong>Reply Form</strong> – filled by the employer (includes placement
                                            details, job description, and supervisor information)</li>
                                        <li><strong>Official Company Offer Letter</strong> – printed on company
                                            letterhead</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-semibold text-sm">5</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 mb-2">
                                        <strong>Submit for Faculty Approval</strong>
                                    </p>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Submit both documents (<strong>Reply Form</strong> and <strong>Offer
                                            Letter</strong>) at placement application to obtain approval from the FK LI
                                        Committee.
                                    </p>
                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>File naming convention:</strong><br>
                                        For PDF: <code
                                            class="bg-gray-100 px-2 py-1 rounded text-xs">{{ Auth::user()->student->studentID ?? 'matricID' }}_ReplyForm.pdf</code>
                                        and <code
                                            class="bg-gray-100 px-2 py-1 rounded text-xs">{{ Auth::user()->student->studentID ?? 'matricID' }}_OfferLetter.pdf</code><br>
                                        For ZIP: <code
                                            class="bg-gray-100 px-2 py-1 rounded text-xs">{{ Auth::user()->student->studentID ?? 'matricID' }}_PlacementDocuments.zip</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6 -->
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 bg-blue-100 rounded-full w-8 h-8 flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-semibold text-sm">6</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 mb-2">
                                        <strong>Confirm Important Details Before Final Decision</strong>
                                    </p>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Before accepting your internship placement, please ensure the following are
                                        verified:
                                    </p>
                                    <ul class="text-sm text-gray-700 space-y-1 ml-4 list-disc">
                                        <li>Relevance to your course/program</li>
                                        <li>Faculty approval obtained</li>
                                        <li>Suitable working hours</li>
                                        <li>Feasible transportation and accommodation arrangements</li>
                                        <li>Allowance (if any) is clearly stated</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Course Verification Alert -->
            @if (!$hasCourseVerification)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-900">Course Verification Required</h3>
                            <p class="text-red-700">You must have an approved course verification before applying for
                                internship placement.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($hasAcceptedApplication && !$canApply)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-yellow-400 text-xl">🔒</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-yellow-900">Application Restriction</h3>
                            <p class="text-yellow-700">You already have an accepted placement application. To submit a
                                new application, you must first request a placement change.</p>
                            <p class="text-yellow-600 text-sm mt-1">Use the "Request Change" button on your accepted
                                application to begin the process.</p>
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
        @php
            $errorMessage = session('error');
            $isRedundantError =
                str_contains($errorMessage, 'already have an accepted placement application') &&
                ($hasAcceptedApplication && !$canApply);
        @endphp
        @if (!$isRedundantError)
            <div class="bg-red-100 border border-red-400 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline">{{ $errorMessage }}</span>
            </div>
        @endif
    @endif

    <!-- Custom Styles for Responsive Table -->
    <style>
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
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

    <!-- Advanced Filters -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200"
                    placeholder="Search applications...">
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

            <!-- Per Page -->
            <div>
                <select wire:model.live="perPage"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-200">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Make More Application Button -->
    @if ($canApply && !$hasAcceptedApplication)
        <div class="mb-6 flex justify-end">
            <button wire:click="openForm"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                <i class="fa fa-plus mr-2"></i>
                Apply for New Application
            </button>
        </div>
    @endif

    <!-- Table Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto table-container">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 1000px;">
                <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Company Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Position
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Application Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Remarks
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($applications as $application)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        #{{ $application->applicationID }}
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
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        @if ($application->remarks)
                                            <div class="max-w-xs truncate" title="{{ $application->remarks }}">
                                                {{ $application->remarks }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- Student Acceptance Actions -->
                                            @if ($application->can_accept && !$hasAcceptedApplication)
                                                <button
                                                    wire:click="acceptApplication({{ $application->applicationID }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700 hover:bg-green-200 disabled:opacity-50"
                                                    wire:loading.attr="disabled"
                                                    wire:target="acceptApplication({{ $application->applicationID }})">
                                                    <span wire:loading.remove
                                                        wire:target="acceptApplication({{ $application->applicationID }})">
                                                        ✅ Accept
                                                    </span>
                                                    <span wire:loading
                                                        wire:target="acceptApplication({{ $application->applicationID }})"
                                                        class="flex items-center">
                                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-green-700"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4">
                                                            </circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        Accepting...
                                                    </span>
                                                </button>
                                                <button
                                                    wire:click="declineApplication({{ $application->applicationID }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700 hover:bg-red-200 disabled:opacity-50"
                                                    wire:loading.attr="disabled"
                                                    wire:target="declineApplication({{ $application->applicationID }})">
                                                    <span wire:loading.remove
                                                        wire:target="declineApplication({{ $application->applicationID }})">
                                                        ❌ Decline
                                                    </span>
                                                    <span wire:loading
                                                        wire:target="declineApplication({{ $application->applicationID }})"
                                                        class="flex items-center">
                                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-red-700"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4">
                                                            </circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        Declining...
                                                    </span>
                                                </button>
                                            @elseif ($application->studentAcceptance)
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->studentAcceptance === 'Accepted'
                                                        ? 'bg-green-100 text-green-800'
                                                        : ($application->studentAcceptance === 'Changed'
                                                            ? 'bg-orange-100 text-orange-800'
                                                            : 'bg-gray-100 text-gray-800') }}">
                                                    {{ $application->studentAcceptance }}
                                                </span>
                                            @endif

                                            <!-- View/Edit Actions -->
                                            @if (!$hasAcceptedApplication &&
                                                 $application->studentAcceptance !== 'Accepted' &&
                                                 $application->committeeStatus === 'Pending' &&
                                                 $application->coordinatorStatus === 'Pending')
                                                <!-- Edit button - only when no application has been accepted, this one is not accepted, and BOTH statuses are pending -->
                                                <button wire:click="edit({{ $application->applicationID }})"
                                                    class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 rounded-md dark:bg-blue-900/20 dark:text-blue-400 dark:hover:text-blue-300"
                                                    title="Edit application">
                                                    <i class="fa fa-edit mr-1"></i> Edit
                                                </button>
                                            @else
                                                <!-- View button - when any application has been accepted, or this one is accepted, or either committee/coordinator has reviewed -->
                                                <button wire:click="view({{ $application->applicationID }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                                    title="View details (read-only)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                    <span>View</span>
                                                </button>
                                            @endif

                                            <!-- Change Request Actions - only for approved and accepted applications (not Changed) -->
                                            @if ($application->overall_status === 'Approved' && $application->studentAcceptance === 'Accepted')
                                                @php
                                                    $hasChangeRequests = $application->changeRequests->count() > 0;
                                                    $hasPendingChangeRequest =
                                                        $application->changeRequests
                                                            ->where(function ($cr) {
                                                                return $cr->committeeStatus === 'Pending' ||
                                                                    $cr->coordinatorStatus === 'Pending';
                                                            })
                                                            ->count() > 0;
                                                    $hasApprovedChangeRequest =
                                                        $application->changeRequests
                                                            ->where(function ($cr) {
                                                                return $cr->committeeStatus === 'Approved' &&
                                                                    $cr->coordinatorStatus === 'Approved';
                                                            })
                                                            ->count() > 0;
                                                @endphp

                                                @if (!$hasPendingChangeRequest && !$hasApprovedChangeRequest && $isStudentActive)
                                                    <button
                                                        wire:click="openChangeRequestForm({{ $application->applicationID }})"
                                                        class="inline-flex items-center px-2 py-1 text-xs rounded bg-orange-100 text-orange-700 hover:bg-orange-200"
                                                        title="Request changes to this application">
                                                        🔄 Request Change
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <i class="fa fa-file text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No applications found</p>
                                            <p class="text-sm">You haven't submitted any internship applications yet.</p>
                                            @if ($canApply)
                                                <button wire:click="openForm"
                                                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                    @if ($hasApprovedChangeRequest)
                                                        Submit New Application
                                                    @else
                                                        Submit Your First Application
                                                    @endif
                                                </button>
                                            @endif
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
        </div>
    </div>

    <!-- Application Form Modal -->
    @if ($showForm)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="closeForm"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ $editingId ? 'Edit Application' : 'New Internship Application' }}
                        </h3>
                        <button wire:click="closeForm" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400">
                            ✖
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 relative">
                    <!-- Loading Overlay -->
                    <div wire:loading wire:target="submit"
                        class="absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-10 rounded">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mb-2" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span
                                class="text-sm text-gray-600 dark:text-gray-300">{{ $editingId ? 'Updating application...' : 'Submitting application...' }}</span>
                        </div>
                    </div>

                    <!-- Information Box -->
                    @if (!$editingId)
                        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div class="text-sm text-blue-800 dark:text-blue-300">
                                    <p class="font-semibold mb-2">📝 Before you start, make sure you have:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>Company's complete contact information and address</li>
                                        <li>Official offer letter or acceptance form</li>
                                        <li>Clear job scope and responsibilities</li>
                                        <li>Confirmed internship start and end dates</li>
                                        <li>Supporting documents in PDF or ZIP files</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Company Information Section -->
                            <div class="md:col-span-2">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Company Information</h4>
                            </div>

                            <!-- Company Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name *</label>
                                <div class="space-y-2">
                                    @if (!$isNewCompany)
                                        <div class="space-y-2">
                                            <select wire:model.live="selectedCompanyId"
                                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value="">- Select existing company -</option>
                                                @foreach ($existingCompanies as $company)
                                                    <option value="{{ $company['id'] }}">{{ $company['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($selectedCompanyId && $companyName)
                                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md px-3 py-2">
                                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                                        <strong>Selected:</strong> {{ $companyName }}
                                                    </p>
                                                </div>
                                            @endif
                                            <div class="flex items-center">
                                                <button type="button" wire:click="$set('isNewCompany', true)"
                                                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 focus:outline-none">
                                                    Enter New Company Name
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            <div
                                                class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-md px-3 py-2">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">Entering new company</span>
                                                <button type="button"
                                                    wire:click="$set('isNewCompany', false); $set('companyName', ''); $set('selectedCompanyId', null)"
                                                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 focus:outline-none">
                                                    Select from existing
                                                </button>
                                            </div>
                                            <input type="text" wire:model="companyName"
                                                placeholder="e.g., Tech Solutions Sdn Bhd"
                                                maxlength="50"
                                                required
                                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                        </div>
                                    @endif
                                </div>
                                @error('companyName')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Company Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Email *</label>
                                <input type="email" wire:model="companyEmail" placeholder="e.g., hr@company.com"
                                    maxlength="50"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('companyEmail')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Company Address -->
                            <!-- Company Address Information -->
                            <div class="md:col-span-2">
                                <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Company Address</h4>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Street Address *</label>
                                <input type="text" wire:model="companyAddressLine"
                                    placeholder="e.g., No. 123, Jalan Technology Park 2, Bukit Jalil"
                                    maxlength="255"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('companyAddressLine')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City *</label>
                                <input type="text" wire:model="companyCity"
                                    placeholder="e.g., Kuala Lumpur, Johor Bahru"
                                    maxlength="20"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('companyCity')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postcode *</label>
                                <input type="text" wire:model="companyPostcode" placeholder="e.g., 50400"
                                    pattern="[0-9]{1,10}"
                                    maxlength="10"
                                    required
                                    inputmode="numeric"
                                    onkeypress="return /[0-9]/i.test(event.key)"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('companyPostcode')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Numbers only, maximum 10 digits</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State *</label>
                                <select wire:model="companyState"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Select State</option>
                                    <option value="Johor">Johor</option>
                                    <option value="Kedah">Kedah</option>
                                    <option value="Kelantan">Kelantan</option>
                                    <option value="Kuala Lumpur">Kuala Lumpur</option>
                                    <option value="Labuan">Labuan</option>
                                    <option value="Melaka">Melaka</option>
                                    <option value="Negeri Sembilan">Negeri Sembilan</option>
                                    <option value="Pahang">Pahang</option>
                                    <option value="Penang">Penang</option>
                                    <option value="Perak">Perak</option>
                                    <option value="Perlis">Perlis</option>
                                    <option value="Putrajaya">Putrajaya</option>
                                    <option value="Sabah">Sabah</option>
                                    <option value="Sarawak">Sarawak</option>
                                    <option value="Selangor">Selangor</option>
                                    <option value="Terengganu">Terengganu</option>
                                    <option value="Outside Malaysia">Outside Malaysia</option>
                                </select>
                                @error('companyState')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                                <select wire:model="companyCountry"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Select Country</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Brunei">Brunei</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Hong Kong">Hong Kong</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran">Iran</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Libya">Libya</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palestine">Palestine</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Russia">Russia</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Korea">South Korea</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syria">Syria</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                    <option value="Vietnam">Vietnam</option>
                                    <option value="Yemen">Yemen</option>
                                </select>
                                @error('companyCountry')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Company Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Phone *</label>
                                <input type="text" wire:model="companyNumber"
                                    placeholder="e.g., 60312345678 or 01112345678"
                                    pattern="[0-9]{1,15}"
                                    maxlength="15"
                                    required
                                    inputmode="numeric"
                                    onkeypress="return /[0-9]/i.test(event.key)"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('companyNumber')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Numbers only, maximum 15 digits</p>
                            </div>

                            <!-- Monthly Allowance -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Allowance
                                    (RM) *</label>
                                <input type="text" wire:model="allowance"
                                    placeholder="e.g., 800"
                                    pattern="[0-9]{1,5}"
                                    maxlength="5"
                                    required
                                    inputmode="numeric"
                                    onkeypress="return /[0-9]/i.test(event.key)"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('allowance')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Numbers only, maximum 5 digits</p>
                            </div>

                            <!-- Industry Supervisor Information Section -->
                            <div class="md:col-span-2 mt-4">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Industry Supervisor Information
                                </h4>
                            </div>

                            <!-- Industry Supervisor Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supervisor Name *</label>
                                <input type="text" wire:model="industrySupervisorName"
                                    placeholder="e.g., Dr. Ahmad bin Abdullah"
                                    maxlength="50"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('industrySupervisorName')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Industry Supervisor Contact -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supervisor Contact Number
                                    *</label>
                                <input type="text" wire:model="industrySupervisorContact"
                                    placeholder="e.g., 60312345678 or 01112345678"
                                    pattern="[0-9]{1,15}"
                                    maxlength="15"
                                    required
                                    inputmode="numeric"
                                    onkeypress="return /[0-9]/i.test(event.key)"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('industrySupervisorContact')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Numbers only, maximum 15 digits</p>
                            </div>

                            <!-- Industry Supervisor Email -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supervisor Email *</label>
                                <input type="email" wire:model="industrySupervisorEmail"
                                    placeholder="e.g., ahmad.abdullah@company.com"
                                    maxlength="50"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('industrySupervisorEmail')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Position Information Section -->
                            <div class="md:col-span-2 mt-4">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Position Details</h4>
                            </div>

                            <!-- Position -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Position *</label>
                                <input type="text" wire:model="position"
                                    placeholder="e.g., Software Developer Intern, Marketing Intern"
                                    maxlength="50"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400">
                                @error('position')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Method of Work -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Method of Work *</label>
                                <select wire:model="methodOfWork"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Select method of work</option>
                                    <option value="WFO">🏢 Work From Office (WFO)</option>
                                    <option value="WOS">🏗️ Work On Site (WOS)</option>
                                    <option value="WOC">🎓 Work On Campus (WOC)</option>
                                    <option value="WFH">🏠 Work From Home (WFH)</option>
                                    <option value="WFO & WFH">🔄 Hybrid (Office & Home)</option>
                                </select>
                                @error('methodOfWork')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Job Scope -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Scope *</label>
                                <textarea wire:model="jobscope" rows="5"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400"
                                    placeholder="Describe your main responsibilities and tasks during the internship. For example:&#10;• Assist in developing web applications using Laravel and Vue.js&#10;• Collaborate with team members on software projects&#10;• Participate in code reviews and testing&#10;• Document development processes"></textarea>
                                @error('jobscope')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Be specific and detailed about your
                                    responsibilities</p>
                            </div>

                            <!-- Duration Section -->
                            <div class="md:col-span-2 mt-4">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Internship Duration</h4>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date *</label>
                                <input type="date" wire:model="startDate"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                @error('startDate')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Internship start date</p>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date *</label>
                                <input type="date" wire:model="endDate"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                @error('endDate')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Internship end date (must be after start date)
                                </p>
                            </div>

                            <!-- File Upload Section -->
                            <div class="md:col-span-2 mt-4">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Supporting Documents</h4>
                                <div
                                    class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <span class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-gray-400 dark:text-gray-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                            Upload Files
                                        </span>
                                    </label>
                                    <input type="file" wire:model="applicationFiles" multiple
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50">
                                    <div class="mt-2 flex items-start space-x-2">
                                        <svg class="w-4 h-4 text-blue-500 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            <p class="font-medium">Documents:</p>
                                            <ul class="list-disc list-inside mt-1 space-y-0.5">
                                                <li>Offer Letter from the company</li>
                                                <li>Reply Form</li>
                                            </ul>
                                            <p class="mt-2 text-gray-500 dark:text-gray-400">Accepted formats: PDF, DOC, DOCX, JPG, JPEG,
                                                PNG • Max: 5MB per file</p>
                                        </div>
                                    </div>
                                    @error('applicationFiles')
                                        <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                                            <p class="text-red-800 dark:text-red-300 text-sm font-medium mb-1">File Upload Error:</p>
                                            @if (is_array($message))
                                                <ul class="list-disc list-inside text-red-700 dark:text-red-400 text-sm space-y-1">
                                                    @foreach ($message as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-red-700 dark:text-red-400 text-sm">{{ $message }}</p>
                                            @endif
                                        </div>
                                    @enderror
                                    @error('applicationFiles.*')
                                        <p class="text-red-600 dark:text-red-400 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Show existing files if editing -->
                                @if ($editingId && !empty($existingFiles))
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Files:</p>
                                        <div class="space-y-2">
                                            @foreach ($existingFiles as $file)
                                                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <span>📄</span>
                                                    <span>{{ $file['original_name'] ?? 'File' }}</span>
                                                    <span
                                                        class="text-gray-400 dark:text-gray-500">({{ number_format($file['file_size'] / 1024, 1) }}
                                                        KB)</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                            Note: Uploading new files will replace existing files.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Form Footer -->
                            <div class="md:col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                                <button wire:click="closeForm" type="button"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    wire:loading.attr="disabled" wire:target="submit">
                                    <span wire:loading.remove wire:target="submit">
                                        {{ $editingId ? 'Update Application' : 'Submit Application' }}
                                    </span>
                                    <span wire:loading wire:target="submit" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ $editingId ? 'Updating...' : 'Submitting...' }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Application Details Modal -->
    @if ($showViewModal && $viewingApplication)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="closeViewModal"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
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
                                <p><strong>Address:</strong> {{ $viewingApplication->company_full_address }}</p>
                                @if ($viewingApplication->has_geocoding)
                                    <p><strong>Coordinates:</strong> {{ $viewingApplication->companyLatitude }},
                                        {{ $viewingApplication->companyLongitude }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Industry Supervisor Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Industry Supervisor Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Name:</strong>
                                    {{ $viewingApplication->industrySupervisorName ?? 'Not provided' }}</p>
                                <p><strong>Contact:</strong>
                                    {{ $viewingApplication->industrySupervisorContact ?? 'Not provided' }}</p>
                                <p><strong>Email:</strong>
                                    {{ $viewingApplication->industrySupervisorEmail ?? 'Not provided' }}</p>
                            </div>
                        </div>

                        <!-- Position Details -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Position Details</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Position:</strong> {{ $viewingApplication->position }}</p>
                                <p><strong>Method of Work:</strong> {{ $viewingApplication->methodOfWorkDisplay }}</p>
                                <p><strong>Allowance:</strong>
                                    {{ $viewingApplication->allowance ? 'RM ' . number_format($viewingApplication->allowance, 2) : 'Not specified' }}
                                </p>
                                <p><strong>Duration:</strong> {{ $viewingApplication->startDate->format('M d, Y') }} -
                                    {{ $viewingApplication->endDate->format('M d, Y') }}</p>
                                <p><strong>Application Date:</strong>
                                    {{ $viewingApplication->applicationDate->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Approval Status -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Approval Status</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Committee:</span>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->committeeStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Coordinator:</span>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->coordinatorStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Overall Status:</span>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->overall_status === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->overall_status }}
                                    </span>
                                </div>
                                @if ($viewingApplication->studentAcceptance)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">Your Response:</span>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->studentAcceptance === 'Accepted'
                                                ? 'bg-blue-100 text-blue-800'
                                                : ($viewingApplication->studentAcceptance === 'Changed'
                                                    ? 'bg-orange-100 text-orange-800'
                                                    : 'bg-gray-100 text-gray-800') }}">
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
                                @if ($viewingApplication->committee)
                                    <p><strong>Committee Member:</strong>
                                        {{ $viewingApplication->committee->user->name }}</p>
                                @endif
                                @if ($viewingApplication->coordinator)
                                    <p><strong>Coordinator:</strong> {{ $viewingApplication->coordinator->user->name }}
                                    </p>
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
                        @if ($viewingApplication->files->count() > 0)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Submitted Files</h4>
                                <div class="space-y-2">
                                    @foreach ($viewingApplication->files as $file)
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
                        @if ($viewingApplication->remarks)
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
                                        @if ($viewingApplication->committeeStatus !== 'Pending' || $viewingApplication->coordinatorStatus !== 'Pending')
                                            <p>This application cannot be edited because it has been reviewed by
                                                committee or coordinator. You can only edit applications that are still
                                                pending review by both parties.</p>
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

    <!-- Change Request Form Modal -->
    @if ($showChangeRequestForm)
        <div>
            <script>
                document.body.classList.add('modal-open');
            </script>
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
                wire:click="closeChangeRequestForm"
                onclick="document.body.classList.remove('modal-open');"></div>
            <div
                style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51;">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Request Application Change
                        </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Application #{{ $changeRequestApplicationID }} -
                        {{ $selectedApplicationForChange?->companyName ?? '' }}
                    </p>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <form wire:submit.prevent="submitChangeRequest">
                        <!-- Current Application Info -->
                        @if ($selectedApplicationForChange)
                            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Current Application Details</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div class="text-gray-700 dark:text-gray-300"><strong>Company:</strong> {{ $selectedApplicationForChange->companyName }}
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300"><strong>Position:</strong> {{ $selectedApplicationForChange->position }}
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300"><strong>Method:</strong> {{ $selectedApplicationForChange->methodOfWork }}
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300"><strong>Duration:</strong>
                                        {{ $selectedApplicationForChange->startDate->format('M d, Y') }} -
                                        {{ $selectedApplicationForChange->endDate->format('M d, Y') }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Reason for Change -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Reason for Change Request *
                            </label>
                            <textarea wire:model="changeRequestReason" rows="4"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 @error('changeRequestReason') border-red-500 dark:border-red-500 @enderror"
                                placeholder="Please provide a detailed justification for requesting changes to your approved application. Explain what changes you need and why they are necessary..."></textarea>
                            @error('changeRequestReason')
                                <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum 20 characters, maximum 1000 characters</p>
                        </div>

                        <!-- Supporting Documents -->
                        <div class="mb-4"></div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supporting Documents *</label>
                            <input type="file" wire:model="changeRequestFiles" multiple
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('changeRequestFiles.*') border-red-500 dark:border-red-500 @enderror"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upload documents supporting your change request (PDF,
                                DOC, DOCX, JPG, PNG). Max 5MB each.</p>
                            @error('changeRequestFiles')
                                <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                                    <p class="text-red-800 dark:text-red-300 text-sm font-medium mb-1">File Upload Error:</p>
                                    @if (is_array($message))
                                        <ul class="list-disc list-inside text-red-700 dark:text-red-400 text-sm space-y-1">
                                            @foreach ($message as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-red-700 dark:text-red-400 text-sm">{{ $message }}</p>
                                    @endif
                                </div>
                            @enderror
                            @error('changeRequestFiles.*')
                                <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <!-- File upload progress -->
                            @if ($changeRequestFiles)
                                <div class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Files to upload:</p>
                                    <div class="space-y-1">
                                        @foreach ($changeRequestFiles as $file)
                                            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                                <span>📄</span>
                                                <span>{{ $file->getClientOriginalName() }}</span>
                                                <span
                                                    class="text-gray-400 dark:text-gray-500">({{ number_format($file->getSize() / 1024, 1) }}
                                                    KB)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Important Notice -->
                        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <div class="flex items-start">
                                <span class="text-yellow-600 dark:text-yellow-400 text-lg mr-2">⚠️</span>
                                <div class="text-sm text-yellow-800 dark:text-yellow-300">
                                    <p class="font-medium mb-1">Important Notice:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Change requests require approval from both committee and coordinator</li>
                                        <li>If approved, you will need to submit a new placement application</li>
                                        <li>Your current application will remain active until the new one is approved
                                        </li>
                                        <li>Approval is not guaranteed - provide strong justification</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-2">
                    <button wire:click="closeChangeRequestForm" type="button"
                        onclick="document.body.classList.remove('modal-open');"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button wire:click="submitChangeRequest" type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 dark:bg-orange-700 hover:bg-orange-700 dark:hover:bg-orange-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled" wire:target="submitChangeRequest">
                        <span wire:loading.remove wire:target="submitChangeRequest">
                            Submit Change Request
                        </span>
                        <span wire:loading wire:target="submitChangeRequest" class="flex items-center">
                            <x-loading-spinner size="h-4 w-4" color="text-white" class="mr-3" />
                            Submitting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Change Requests Modal -->
    @if ($viewingChangeRequests && $selectedApplicationForChange)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
            wire:click="closeChangeRequestsView"></div>
        <div
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Change Request History
                            </h3>
                            <p class="text-sm text-gray-600">
                                Application #{{ $selectedApplicationForChange->applicationID }} -
                                {{ $selectedApplicationForChange->companyName }}
                            </p>
                        </div>
                        <button wire:click="closeChangeRequestsView"
                            class="text-gray-400 hover:text-gray-600">✖</button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    @if ($selectedApplicationForChange->changeRequests->count() > 0)
                        <div class="space-y-4">
                            @foreach ($selectedApplicationForChange->changeRequests as $changeRequest)
                                <div
                                    class="border rounded-lg p-4 {{ $changeRequest->overall_status === 'Approved' ? 'bg-green-50 border-green-200' : ($changeRequest->overall_status === 'Rejected' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Change Request
                                                #{{ $changeRequest->justificationID }}</h4>
                                            <p class="text-sm text-gray-600">Submitted:
                                                {{ $changeRequest->requestDate->format('F d, Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs rounded-full {{ $changeRequest->overall_status === 'Approved' ? 'bg-green-100 text-green-800' : ($changeRequest->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $changeRequest->overall_status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Reason -->
                                    <div class="mb-3">
                                        <h5 class="font-medium text-gray-900 mb-1">Reason:</h5>
                                        <p class="text-sm text-gray-700">{{ $changeRequest->reason }}</p>
                                    </div>

                                    <!-- Status Details -->
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Committee:</span>
                                            <span
                                                class="ml-2 inline-flex px-2 py-1 text-xs rounded-full {{ $changeRequest->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($changeRequest->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $changeRequest->committeeStatus }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Coordinator:</span>
                                            <span
                                                class="ml-2 inline-flex px-2 py-1 text-xs rounded-full {{ $changeRequest->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($changeRequest->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $changeRequest->coordinatorStatus }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Files -->
                                    @if ($changeRequest->files->count() > 0)
                                        <div class="mb-3">
                                            <h5 class="font-medium text-gray-900 mb-1">Supporting Documents:</h5>
                                            <div class="space-y-1">
                                                @foreach ($changeRequest->files as $file)
                                                    <div class="flex items-center space-x-2 text-sm">
                                                        <span>📄</span>
                                                        <a href="{{ $file->url }}" target="_blank"
                                                            class="text-blue-600 hover:text-blue-900">
                                                            {{ $file->original_name }}
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Remarks -->
                                    @if ($changeRequest->remarks)
                                        <div class="mb-3">
                                            <h5 class="font-medium text-gray-900 mb-1">Reviewer Remarks:</h5>
                                            <div class="bg-blue-50 p-3 rounded border text-sm">
                                                {{ $changeRequest->remarks }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Decision Date -->
                                    @if ($changeRequest->decisionDate)
                                        <div class="text-sm text-gray-600">
                                            Decision Date: {{ $changeRequest->decisionDate->format('F d, Y') }}
                                        </div>
                                    @endif

                                    <!-- New Application Notice -->
                                    @if ($changeRequest->overall_status === 'Approved')
                                        <div class="mt-3 p-3 bg-green-100 border border-green-300 rounded">
                                            <div class="flex items-center">
                                                <span class="text-green-600 mr-2">✅</span>
                                                <div class="text-sm text-green-800">
                                                    <p class="font-medium">Change Request Approved!</p>
                                                    <p>You can now submit a new placement application with your
                                                        requested changes.</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No change requests found for this application.</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button wire:click="closeChangeRequestsView"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Company Status Warning Modal -->
    @if($showCompanyWarningModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-[9999]"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            <div class="relative top-20 mx-auto p-5 border border-gray-300 dark:border-gray-700 w-96 shadow-lg rounded-md bg-white dark:bg-gray-800"
                 style="position: relative; margin: 5rem auto; padding: 1.25rem; border: 1px solid; width: 24rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-radius: 0.5rem; background-color: white;">
                <div class="mt-3">
                    <!-- Warning Icon -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <!-- Warning Title -->
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 text-center mb-2">
                        Company Status Warning
                    </h3>

                    <!-- Warning Message -->
                    <div class="mt-2 px-4 py-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-2">
                            <strong>Company:</strong> {{ $warningCompanyName }}
                        </p>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-2">
                            <strong>Status:</strong> {{ $warningCompanyStatus }}
                        </p>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 mt-3">
                            This company has been marked as <strong>{{ $warningCompanyStatus }}</strong>.
                            Are you sure you want to proceed with your application to this company?
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <button wire:click="cancelCompanyWarning"
                            class="px-4 py-2 bg-gray-500 dark:bg-gray-600 text-white rounded-md hover:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button wire:click="proceedWithCompanyWarning"
                            class="px-4 py-2 bg-yellow-600 dark:bg-yellow-700 text-white rounded-md hover:bg-yellow-700 dark:hover:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Proceed Anyway
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
