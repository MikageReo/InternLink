<!-- Change Request Detail Modal -->
@if ($showDetailModal && $selectedRequest)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Change Request Details</h3>
                <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="mt-4 max-h-96 overflow-y-auto">
                <!-- Request Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Request Information</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Request ID:</span>
                                <span class="text-sm text-gray-900 ml-2">#{{ $selectedRequest->justificationID }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Request Date:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->requestDate->format('M d, Y') }}</span>
                            </div>
                            @if($selectedRequest->decisionDate)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Decision Date:</span>
                                    <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->decisionDate->format('M d, Y') }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="text-sm font-medium text-gray-600">Overall Status:</span>
                                <span class="ml-2 inline-flex px-2 py-1 text-xs rounded-full
                                    {{ $selectedRequest->overall_status === 'Approved' ? 'bg-green-100 text-green-800' :
                                       ($selectedRequest->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $selectedRequest->overall_status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Student Information</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Student ID:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->placementApplication->student->studentID }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Name:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->placementApplication->student->user->name }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Email:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->placementApplication->student->user->email }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Placement Information -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Current Placement Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Company:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->placementApplication->companyName }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Position:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->placementApplication->position }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Work Method:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->placementApplication->method_of_work_display }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Duration:</span>
                            <span class="text-sm text-gray-900 ml-2">
                                {{ $selectedRequest->placementApplication->startDate->format('M d, Y') }} -
                                {{ $selectedRequest->placementApplication->endDate->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Reason for Change -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Reason for Change Request</h4>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <p class="text-sm text-gray-900">{{ $selectedRequest->reason }}</p>
                    </div>
                </div>

                <!-- Approval Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Committee Review</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Status:</span>
                                <span class="ml-2 inline-flex px-2 py-1 text-xs rounded-full
                                    {{ $selectedRequest->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' :
                                       ($selectedRequest->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $selectedRequest->committeeStatus }}
                                </span>
                            </div>
                            @if($selectedRequest->committee)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Reviewer:</span>
                                    <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->committee->user->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Coordinator Review</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Status:</span>
                                <span class="ml-2 inline-flex px-2 py-1 text-xs rounded-full
                                    {{ $selectedRequest->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' :
                                       ($selectedRequest->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $selectedRequest->coordinatorStatus }}
                                    @if($selectedRequest->committeeStatus === 'Rejected' && $selectedRequest->coordinatorStatus === 'Rejected' && !$selectedRequest->coordinatorID)
                                        <span class="ml-1" title="Auto-rejected due to committee rejection">*</span>
                                    @endif
                                </span>
                            </div>
                            @if($selectedRequest->coordinator)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Reviewer:</span>
                                    <span class="text-sm text-gray-900 ml-2">{{ $selectedRequest->coordinator->user->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Supporting Documents -->
                @if($selectedRequest->files->count() > 0)
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Supporting Documents</h4>
                        <div class="space-y-2">
                            @foreach($selectedRequest->files as $file)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-blue-500">📄</span>
                                        <span class="text-sm text-gray-900">{{ $file->original_name }}</span>
                                        <span class="text-xs text-gray-500">({{ number_format($file->file_size / 1024, 1) }} KB)</span>
                                    </div>
                                    <button wire:click="downloadFile({{ $file->id }})"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Download
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Remarks Section -->
                <div class="mb-6">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                        Reviewer Remarks
                    </label>
                    <textarea wire:model="remarks" id="remarks" rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Add your remarks here..."></textarea>
                </div>

                <!-- Role Information -->
                <div class="mb-6 p-3 bg-blue-50 rounded-md">
                    <p class="text-sm text-blue-800">
                        <strong>Your Approval Permissions:</strong>
                        @if(Auth::user()->lecturer->isCommittee && Auth::user()->lecturer->isCoordinator)
                            You can approve/reject as both Committee and Coordinator.
                        @elseif(Auth::user()->lecturer->isCommittee)
                            You can approve/reject as Committee only.
                        @elseif(Auth::user()->lecturer->isCoordinator)
                            You can approve/reject as Coordinator only.
                        @else
                            You do not have approval permissions.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-4 border-t">
                <div class="flex space-x-2">
                    <!-- Committee Actions -->
                    @if($selectedRequest->committeeStatus === 'Pending' && Auth::user()->lecturer->isCommittee)
                        <button wire:click="approveAsCommittee({{ $selectedRequest->justificationID }})"
                            wire:loading.attr="disabled"
                            wire:target="approveAsCommittee({{ $selectedRequest->justificationID }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="approveAsCommittee({{ $selectedRequest->justificationID }})">✅ Approve (Committee)</span>
                            <span wire:loading wire:target="approveAsCommittee({{ $selectedRequest->justificationID }})" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 718-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Approving...
                            </span>
                        </button>
                        <button wire:click="rejectAsCommittee({{ $selectedRequest->justificationID }})"
                            wire:loading.attr="disabled"
                            wire:target="rejectAsCommittee({{ $selectedRequest->justificationID }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="rejectAsCommittee({{ $selectedRequest->justificationID }})">❌ Reject (Committee)</span>
                            <span wire:loading wire:target="rejectAsCommittee({{ $selectedRequest->justificationID }})" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 718-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Rejecting...
                            </span>
                        </button>
                    @endif

                    <!-- Coordinator Actions -->
                    @if($selectedRequest->coordinatorStatus === 'Pending' && $selectedRequest->committeeStatus === 'Approved' && Auth::user()->lecturer->isCoordinator)
                        <button wire:click="approveAsCoordinator({{ $selectedRequest->justificationID }})"
                            wire:loading.attr="disabled"
                            wire:target="approveAsCoordinator({{ $selectedRequest->justificationID }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="approveAsCoordinator({{ $selectedRequest->justificationID }})">✅ Approve (Coordinator)</span>
                            <span wire:loading wire:target="approveAsCoordinator({{ $selectedRequest->justificationID }})" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 718-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Approving...
                            </span>
                        </button>
                        <button wire:click="rejectAsCoordinator({{ $selectedRequest->justificationID }})"
                            wire:loading.attr="disabled"
                            wire:target="rejectAsCoordinator({{ $selectedRequest->justificationID }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="rejectAsCoordinator({{ $selectedRequest->justificationID }})">❌ Reject (Coordinator)</span>
                            <span wire:loading wire:target="rejectAsCoordinator({{ $selectedRequest->justificationID }})" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 718-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Rejecting...
                            </span>
                        </button>
                    @endif
                </div>

                <button wire:click="closeDetailModal"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
@endif
