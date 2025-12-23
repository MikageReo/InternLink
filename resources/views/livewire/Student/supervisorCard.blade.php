<div>
    @if ($supervisorAssignment && $supervisorAssignment->supervisor)
        <!-- Supervisor Assignment Card -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">My Supervisor</h3>
                <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                    Assigned
                </span>
            </div>

            <div class="space-y-4">
                <!-- Supervisor Info -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        @if ($supervisorAssignment->supervisor->profile_photo)
                            <img src="{{ asset('storage/' . $supervisorAssignment->supervisor->profile_photo) }}"
                                alt="{{ $supervisorAssignment->supervisor->user->name }}"
                                class="h-16 w-16 rounded-full object-cover">
                        @else
                            <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 text-xl font-semibold">
                                    {{ substr($supervisorAssignment->supervisor->user->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-medium text-gray-900">
                            {{ $supervisorAssignment->supervisor->user->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $supervisorAssignment->supervisor->lecturerID }}</p>
                        @if ($supervisorAssignment->supervisor->position)
                            <p class="text-sm text-gray-600">{{ $supervisorAssignment->supervisor->position }}</p>
                        @endif
                        @if ($supervisorAssignment->supervisor->department)
                            <p class="text-sm text-gray-600">Department:
                                {{ $supervisorAssignment->supervisor->department }}</p>
                        @endif
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="border-t border-gray-200 pt-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">Contact Information</h5>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Email:</span>
                            <a href="mailto:{{ $supervisorAssignment->supervisor->user->email }}"
                                class="text-indigo-600 hover:text-indigo-800">
                                {{ $supervisorAssignment->supervisor->user->email }}
                            </a>
                        </p>
                        @if ($supervisorAssignment->supervisor->full_address)
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Address:</span>
                                {{ $supervisorAssignment->supervisor->full_address }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Assignment Details -->
                <div class="border-t border-gray-200 pt-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">Assignment Details</h5>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Assigned On:</span>
                            {{ $supervisorAssignment->assigned_at->format('F d, Y') }}
                        </p>
                        @if ($supervisorAssignment->distance_km)
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Distance:</span>
                                {{ number_format($supervisorAssignment->distance_km, 2) }} km
                            </p>
                        @endif
                        @if ($supervisorAssignment->assignment_notes)
                            <div class="mt-2">
                                <p class="text-sm font-medium text-gray-700">Notes:</p>
                                <p class="text-sm text-gray-600">{{ $supervisorAssignment->assignment_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Supervisor Assigned Card -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Supervisor Assigned</h3>
                <p class="text-sm text-gray-500 mb-4">
                    @if ($student->hasAcceptedPlacement())
                        A supervisor will be assigned to you soon. Please check back later.
                    @else
                        You need to have an accepted placement application before a supervisor can be assigned.
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>
