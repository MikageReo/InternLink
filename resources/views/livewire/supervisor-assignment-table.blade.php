<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Manage Supervisor Assignments</h1>
                    <p class="text-gray-600">Assign supervisors to students with accepted placement applications</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100">
                            <span class="text-blue-600 text-xl">👥</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Eligible</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_eligible'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <span class="text-green-600 text-xl">✅</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Assigned</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['assigned'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100">
                            <span class="text-yellow-600 text-xl">⏳</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Unassigned</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['unassigned'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700">{{ session('success') }}</p>
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
                    <h2 class="text-lg font-medium text-gray-900">Students Requiring Supervisor Assignment</h2>
                </div>

                <!-- Advanced Filters -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                placeholder="Search by student ID, name, email, or company...">
                        </div>

                        <!-- Assignment Type Filter -->
                        <div>
                            <select wire:model.live="assignmentTypeFilter"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="unassigned">Unassigned Only</option>
                                <option value="assigned">Assigned Only</option>
                                <option value="all">All Students</option>
                            </select>
                        </div>

                        <!-- Semester Filter -->
                        <div>
                            <select wire:model.live="semesterFilter"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">All Semesters</option>
                                @foreach($availableSemesters as $semester)
                                    <option value="{{ $semester }}">Semester {{ $semester }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <select wire:model.live="yearFilter"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">All Years</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
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

                <!-- Students Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                    wire:click="sortBy('studentID')">
                                    Student ID
                                    @if($sortField === 'studentID')
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Supervisor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                    wire:click="sortBy('assigned_at')">
                                    Assigned Date
                                    @if($sortField === 'assigned_at')
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $student->studentID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $student->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $student->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($student->acceptedPlacementApplication)
                                            <div class="text-sm text-gray-900">{{ $student->acceptedPlacementApplication->companyName }}</div>
                                            <div class="text-sm text-gray-500">{{ $student->acceptedPlacementApplication->companyCity }}, {{ $student->acceptedPlacementApplication->companyState }}</div>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($student->supervisorAssignment && $student->supervisorAssignment->supervisor)
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $student->supervisorAssignment->supervisor->user->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $student->supervisorAssignment->supervisor->lecturerID }}</div>
                                                    @if($student->supervisorAssignment->distance_km)
                                                        <div class="text-xs text-gray-400">Distance: {{ number_format($student->supervisorAssignment->distance_km, 2) }} km</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-yellow-600 font-medium">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($student->supervisorAssignment)
                                            {{ $student->supervisorAssignment->assigned_at->format('Y-m-d') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if(!$student->supervisorAssignment)
                                                <button wire:click="openAssignModal('{{ $student->studentID }}')"
                                                    class="text-blue-600 hover:text-blue-900 mr-2">
                                                    Assign
                                                </button>
                                                <button wire:click="autoAssignSupervisor('{{ $student->studentID }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="autoAssignSupervisor('{{ $student->studentID }}')"
                                                    class="text-green-600 hover:text-green-900">
                                                    <span wire:loading.remove wire:target="autoAssignSupervisor('{{ $student->studentID }}')">Auto Assign</span>
                                                    <span wire:loading wire:target="autoAssignSupervisor('{{ $student->studentID }}')">Assigning...</span>
                                                </button>
                                            @else
                                                <button wire:click="viewAssignment({{ $student->supervisorAssignment->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                    wire:loading.attr="disabled"
                                                    wire:target="viewAssignment">
                                                    <span wire:loading.remove wire:target="viewAssignment">View Details</span>
                                                    <span wire:loading wire:target="viewAssignment">Loading...</span>
                                                </button>
                                                <button wire:click="openEditModal({{ $student->supervisorAssignment->id }})"
                                                    class="text-yellow-600 hover:text-yellow-900 mr-2">
                                                    Edit
                                                </button>
                                                <button wire:click="removeAssignment({{ $student->supervisorAssignment->id }})"
                                                    wire:confirm="Are you sure you want to remove this supervisor assignment?"
                                                    class="text-red-600 hover:text-red-900">
                                                    Remove
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Supervisor Modal -->
    @if($showAssignModal && $selectedStudent)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeAssignModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Assign Supervisor</h3>
                        <button wire:click="closeAssignModal" class="text-gray-400 hover:text-gray-500">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>

                    <!-- Student Info -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Student Information</h4>
                        <p><strong>Name:</strong> {{ $selectedStudent->user->name }}</p>
                        <p><strong>ID:</strong> {{ $selectedStudent->studentID }}</p>
                        @if($selectedStudent->acceptedPlacementApplication)
                            <p><strong>Company:</strong> {{ $selectedStudent->acceptedPlacementApplication->companyName }}</p>
                            <p><strong>Location:</strong> {{ $selectedStudent->acceptedPlacementApplication->companyFullAddress }}</p>
                        @endif
                    </div>

                    <!-- Recommended Supervisors -->
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 mb-2">Recommended Supervisors (Nearest First)</h4>

                        <div class="mb-3">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="quotaOverride" wire:change="toggleQuotaOverride"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Include supervisors with full quota (Override)</span>
                            </label>
                        </div>

                        @if(!empty($recommendedSupervisors))
                            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
                                @foreach($recommendedSupervisors as $supervisor)
                                    <label class="flex items-start p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100">
                                        <input type="radio" name="supervisor" value="{{ $supervisor->lecturerID }}"
                                            wire:model="selectedSupervisorID"
                                            class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="ml-3 flex-1">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $supervisor->user->name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $supervisor->lecturerID }} |
                                                        {{ $supervisor->department ?? 'N/A' }} |
                                                        {{ $supervisor->researchGroup ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    @if(isset($supervisor->distance))
                                                        <p class="text-sm font-medium text-gray-900">{{ number_format($supervisor->distance, 2) }} km</p>
                                                    @endif
                                                    <p class="text-xs text-gray-500">
                                                        Quota: {{ $supervisor->current_assignments }}/{{ $supervisor->supervisor_quota }}
                                                        @if(isset($supervisor->available_quota) && $supervisor->available_quota > 0)
                                                            <span class="text-green-600">({{ $supervisor->available_quota }} available)</span>
                                                        @else
                                                            <span class="text-red-600">(Full)</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">No available supervisors found. Please check quota settings or enable override.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Override Reason -->
                    @if($quotaOverride)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Override Reason <span class="text-red-500">*</span>
                            </label>
                            <textarea wire:model="overrideReason" rows="3"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                placeholder="Explain why you need to override the quota limit..."></textarea>
                            @error('overrideReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Assignment Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Assignment Notes (Optional)
                        </label>
                        <textarea wire:model="assignmentNotes" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                            placeholder="Add any notes about this assignment..."></textarea>
                    </div>

                    <!-- Validation Errors -->
                    @error('selectedSupervisorID')
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeAssignModal"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button wire:click="assignSupervisor"
                            wire:loading.attr="disabled"
                            wire:target="assignSupervisor"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="assignSupervisor">Assign Supervisor</span>
                            <span wire:loading wire:target="assignSupervisor">Assigning...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Assignment Modal -->
    @if($showEditModal && $editAssignmentID)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeEditModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Edit Supervisor Assignment</h3>
                        <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-500">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> Select a new supervisor to replace the current assignment. This will update the quota counts accordingly.
                        </p>
                    </div>

                    <!-- Available Supervisors -->
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 mb-2">Select New Supervisor (Nearest First)</h4>

                        @if(!empty($recommendedSupervisors))
                            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
                                @foreach($recommendedSupervisors as $supervisor)
                                    <label class="flex items-start p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100">
                                        <input type="radio" name="new_supervisor" value="{{ $supervisor->lecturerID }}"
                                            wire:model="newSupervisorID"
                                            class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="ml-3 flex-1">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $supervisor->user->name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $supervisor->lecturerID }} |
                                                        {{ $supervisor->department ?? 'N/A' }} |
                                                        {{ $supervisor->researchGroup ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    @if(isset($supervisor->distance))
                                                        <p class="text-sm font-medium text-gray-900">{{ number_format($supervisor->distance, 2) }} km</p>
                                                    @endif
                                                    <p class="text-xs text-gray-500">
                                                        Quota: {{ $supervisor->current_assignments }}/{{ $supervisor->supervisor_quota }}
                                                        @if(isset($supervisor->available_quota) && $supervisor->available_quota > 0)
                                                            <span class="text-green-600">({{ $supervisor->available_quota }} available)</span>
                                                        @else
                                                            <span class="text-red-600">(Full)</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">No supervisors available.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Assignment Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Assignment Notes (Optional)
                        </label>
                        <textarea wire:model="assignmentNotes" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                            placeholder="Add any notes about this assignment change..."></textarea>
                    </div>

                    <!-- Validation Errors -->
                    @error('newSupervisorID')
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeEditModal"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button wire:click="updateAssignment"
                            wire:loading.attr="disabled"
                            wire:target="updateAssignment"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-md text-sm font-medium hover:bg-yellow-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateAssignment">Update Assignment</span>
                            <span wire:loading wire:target="updateAssignment">Updating...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Assignment Detail Modal -->
    @if($showDetailModal && $selectedAssignment)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Assignment Details</h3>
                        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-500">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>

                    <!-- Student Info -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Student</h4>
                        <p><strong>Name:</strong> {{ $selectedAssignment['student_name'] }}</p>
                        <p><strong>ID:</strong> {{ $selectedAssignment['student_id'] }}</p>
                        <p><strong>Program:</strong> {{ $selectedAssignment['student_program'] ?? 'N/A' }}</p>
                        @if($selectedAssignment['company_name'])
                            <p><strong>Company:</strong> {{ $selectedAssignment['company_name'] }}</p>
                            <p><strong>Location:</strong>
                                {{ $selectedAssignment['company_city'] }},
                                {{ $selectedAssignment['company_state'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Supervisor Info -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Supervisor</h4>
                        <p><strong>Name:</strong> {{ $selectedAssignment['supervisor_name'] }}</p>
                        <p><strong>ID:</strong> {{ $selectedAssignment['supervisor_id'] }}</p>
                        <p><strong>Department:</strong> {{ $selectedAssignment['supervisor_department'] ?? 'N/A' }}</p>
                        <p><strong>Research Group:</strong> {{ $selectedAssignment['supervisor_research_group'] ?? 'N/A' }}</p>
                        <p><strong>Position:</strong> {{ $selectedAssignment['supervisor_position'] ?? 'N/A' }}</p>
                        @if($selectedAssignment['distance_km'])
                            <p><strong>Distance:</strong> {{ number_format($selectedAssignment['distance_km'], 2) }} km</p>
                        @endif
                        @if($selectedAssignment['quota_override'])
                            <p class="text-yellow-600"><strong>⚠️ Quota Override Applied</strong></p>
                            @if($selectedAssignment['override_reason'])
                                <p class="text-sm text-gray-600"><strong>Reason:</strong> {{ $selectedAssignment['override_reason'] }}</p>
                            @endif
                        @endif
                    </div>

                    <!-- Assignment Info -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Assignment Details</h4>
                        <p><strong>Status:</strong>
                            <span class="px-2 py-1 text-xs rounded-full {{ $selectedAssignment['status'] === 'assigned' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $selectedAssignment['status_display'] }}
                            </span>
                        </p>
                        <p><strong>Assigned By:</strong>
                            {{ $selectedAssignment['assigned_by_name'] }}
                            @if($selectedAssignment['assigned_by_id'])
                                ({{ $selectedAssignment['assigned_by_id'] }})
                            @endif
                        </p>
                        <p><strong>Assigned At:</strong> {{ $selectedAssignment['assigned_at'] }}</p>
                        @if($selectedAssignment['assignment_notes'])
                            <p><strong>Notes:</strong> {{ $selectedAssignment['assignment_notes'] }}</p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end">
                        <button wire:click="closeDetailModal"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-medium hover:bg-gray-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
