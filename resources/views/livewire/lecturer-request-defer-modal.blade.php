<!-- Request Detail Modal -->
@if ($showDetailModal && $selectedRequest)
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
         wire:click="closeDetailModal"></div>
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        Defer Request #{{ $selectedRequest->deferID }} - Details
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
                            <p><strong>Student ID:</strong> {{ $selectedRequest->student->studentID }}</p>
                            <p><strong>Name:</strong> {{ $selectedRequest->student->user->name }}</p>
                            <p><strong>Email:</strong> {{ $selectedRequest->student->user->email }}</p>
                        </div>
                    </div>

                    <!-- Request Details -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Request Information</h4>
                        <div class="space-y-2 text-sm">
                            <p><strong>Request ID:</strong> #{{ $selectedRequest->deferID }}</p>
                            <p><strong>Start Date:</strong> {{ $selectedRequest->startDate->format('M d, Y') }}</p>
                            <p><strong>End Date:</strong> {{ $selectedRequest->endDate->format('M d, Y') }}</p>
                            <p><strong>Application Date:</strong> {{ $selectedRequest->applicationDate->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <!-- Approval Status -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Approval Status</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium">Committee:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ $selectedRequest->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($selectedRequest->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $selectedRequest->committeeStatus }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="font-medium">Coordinator:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ $selectedRequest->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($selectedRequest->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $selectedRequest->coordinatorStatus }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="font-medium">Overall Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ $selectedRequest->overall_status === 'Approved' ? 'bg-green-100 text-green-800' : ($selectedRequest->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $selectedRequest->overall_status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Reviewer Information -->
                    @if($selectedRequest->committee || $selectedRequest->coordinator)
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Reviewers</h4>
                            <div class="space-y-2 text-sm">
                                @if($selectedRequest->committee)
                                    <p><strong>Committee Member:</strong> {{ $selectedRequest->committee->user->name }}</p>
                                @endif
                                @if($selectedRequest->coordinator)
                                    <p><strong>Coordinator:</strong> {{ $selectedRequest->coordinator->user->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Reason -->
                    <div class="md:col-span-2">
                        <h4 class="font-semibold text-gray-900 mb-3">Reason for Defer</h4>
                        <div class="bg-gray-50 p-3 rounded border text-sm">
                            {{ $selectedRequest->reason }}
                        </div>
                    </div>

                    <!-- Files -->
                    @if($selectedRequest->files->count() > 0)
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-3">Submitted Files</h4>
                            <div class="space-y-2">
                                @foreach($selectedRequest->files as $file)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded border">
                                        <div class="flex items-center space-x-3">
                                            <span>📄</span>
                                            <div>
                                                <p class="text-sm font-medium">{{ $file->original_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $file->mime_type }} • {{ number_format($file->file_size / 1024, 1) }} KB</p>
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

                    <!-- Remarks Section -->
                    <div class="md:col-span-2">
                        <h4 class="font-semibold text-gray-900 mb-3">Remarks</h4>
                        <textarea wire:model="remarks" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                            placeholder="Add your remarks here..."></textarea>
                    </div>

                    <!-- Current Remarks -->
                    @if($selectedRequest->remarks)
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-3">Current Remarks</h4>
                            <div class="bg-blue-50 p-3 rounded border text-sm">
                                {{ $selectedRequest->remarks }}
                            </div>
                        </div>
                    @endif

                    <!-- Role Information -->
                    <div class="md:col-span-2">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start space-x-2">
                                <span class="text-blue-600">ℹ️</span>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium mb-1">Your Approval Permissions:</p>
                                    @if(Auth::user()->lecturer->isCommittee && Auth::user()->lecturer->isCoordinator)
                                        <p>You can approve/reject as both Committee Member and Coordinator.</p>
                                    @elseif(Auth::user()->lecturer->isCommittee)
                                        <p>You can approve/reject as Committee Member only.</p>
                                    @elseif(Auth::user()->lecturer->isCoordinator)
                                        <p>You can approve/reject as Coordinator only (after committee approval).</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <!-- Committee Actions -->
                        @if($selectedRequest->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                            <button wire:click="approveAsCommittee({{ $selectedRequest->deferID }})"
                                wire:loading.attr="disabled"
                                wire:target="approveAsCommittee({{ $selectedRequest->deferID }})"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="approveAsCommittee({{ $selectedRequest->deferID }})">✅ Approve as Committee</span>
                                <span wire:loading wire:target="approveAsCommittee({{ $selectedRequest->deferID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Approving...
                                </span>
                            </button>
                            <button wire:click="rejectAsCommittee({{ $selectedRequest->deferID }})"
                                wire:loading.attr="disabled"
                                wire:target="rejectAsCommittee({{ $selectedRequest->deferID }})"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="rejectAsCommittee({{ $selectedRequest->deferID }})">❌ Reject as Committee</span>
                                <span wire:loading wire:target="rejectAsCommittee({{ $selectedRequest->deferID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Rejecting...
                                </span>
                            </button>
                        @endif

                        <!-- Coordinator Actions -->
                        @if($selectedRequest->coordinatorStatus === 'Pending' && $selectedRequest->committeeStatus === 'Approved' && Auth::user()->lecturer->isCoordinator)
                            <button wire:click="approveAsCoordinator({{ $selectedRequest->deferID }})"
                                wire:loading.attr="disabled"
                                wire:target="approveAsCoordinator({{ $selectedRequest->deferID }})"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="approveAsCoordinator({{ $selectedRequest->deferID }})">✅ Approve as Coordinator</span>
                                <span wire:loading wire:target="approveAsCoordinator({{ $selectedRequest->deferID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Approving...
                                </span>
                            </button>
                            <button wire:click="rejectAsCoordinator({{ $selectedRequest->deferID }})"
                                wire:loading.attr="disabled"
                                wire:target="rejectAsCoordinator({{ $selectedRequest->deferID }})"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="rejectAsCoordinator({{ $selectedRequest->deferID }})">❌ Reject as Coordinator</span>
                                <span wire:loading wire:target="rejectAsCoordinator({{ $selectedRequest->deferID }})" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Rejecting...
                                </span>
                            </button>
                        @endif
                    </div>

                    <button wire:click="closeDetailModal"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
