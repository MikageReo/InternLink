<div>
    <!-- Custom Styles for Responsive Table -->
    <style>
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 1024px) {
            .table-container table {
                min-width: 1000px;
            }
        }

        /* Ensure buttons are clickable */
        .table-container button {
            position: relative;
            z-index: 1;
            pointer-events: auto;
        }
    </style>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                            <i class="fa fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Eligible</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_eligible'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <i class="fa fa-check text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Assigned</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['assigned'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                            <i class="fa fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unassigned</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['unassigned'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                    <p class="text-green-700 dark:text-green-300">{{ session('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                    <p class="text-red-700 dark:text-red-300">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Bulk Actions Bar -->
            @if(count($selectedStudents) > 0 || $isBulkAssigning)
                <div class="px-6 py-3 mb-6 border border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ count($selectedStudents) }} student(s) selected
                            </span>
                            @if($isBulkAssigning)
                                <div class="flex items-center space-x-2">
                                    <div class="w-48 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                             style="width: {{ $bulkAssignTotal > 0 ? ($bulkAssignProgress / $bulkAssignTotal * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $bulkAssignProgress }} / {{ $bulkAssignTotal }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="clearSelection"
                                    class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                                Clear Selection
                            </button>
                            <button wire:click="bulkAutoAssign"
                                    wire:loading.attr="disabled"
                                    wire:target="bulkAutoAssign"
                                    @if($isBulkAssigning) disabled @endif
                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="bulkAutoAssign">Bulk Auto Assign</span>
                                <span wire:loading wire:target="bulkAutoAssign">Assigning...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Advanced Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
                        <!-- Search -->
                        <div class="lg:col-span-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                                placeholder="Search by student ID, name, email, or company...">
                        </div>

                        <!-- Assignment Type Filter -->
                        <div>
                            <select wire:model.live="assignmentTypeFilter"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <option value="unassigned">Unassigned Only</option>
                                <option value="assigned">Assigned Only</option>
                                <option value="all">All Students</option>
                            </select>
                        </div>

                        <!-- Program Filter -->
                        <div>
                            <select wire:model.live="program"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <option value="">All Programs</option>
                                <option value="BCS">Bachelor of Computer Science (Software Engineering) with Honours</option>
                                <option value="BCN">Bachelor of Computer Science (Computer Systems & Networking) with Honours</option>
                                <option value="BCM">Bachelor of Computer Science (Multimedia Software) with Honours</option>
                                <option value="BCY">Bachelor of Computer Science (Cyber Security) with Honours</option>
                                <option value="DRC">Diploma in Computer Science</option>
                            </select>
                        </div>

                        <!-- Semester Filter -->
                        <div>
                            <select wire:model.live="semesterFilter"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <option value="">All Semesters</option>
                                @foreach($availableSemesters as $semester)
                                    <option value="{{ $semester }}">Semester {{ $semester }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <select wire:model.live="yearFilter"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <option value="">All Years</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Per Page -->
                        <div>
                            <select wire:model.live="perPage"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                        </div>
                    </div>
                </div>

            <!-- Students Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto table-container">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 1200px;">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <label class="flex items-center cursor-pointer">
                                        @php
                                            // Only count unassigned students for select all
                                            $unassignedStudentIDs = $students->filter(function ($student) {
                                                return !$student->supervisorAssignment ||
                                                       $student->supervisorAssignment->status !== \App\Models\SupervisorAssignment::STATUS_ASSIGNED;
                                            })->pluck('studentID')->toArray();
                                            $selectedUnassigned = array_intersect($selectedStudents, $unassignedStudentIDs);
                                            $allUnassignedSelected = !empty($unassignedStudentIDs) &&
                                                count($selectedUnassigned) === count($unassignedStudentIDs);
                                        @endphp
                                        <input type="checkbox"
                                               @if($allUnassignedSelected) checked @endif
                                               wire:click="toggleSelectAll"
                                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-500 focus:ring-indigo-500 dark:bg-gray-700">
                                        <span class="ml-2">Select</span>
                                    </label>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                                    wire:click="sortBy('studentID')">
                                    Student ID
                                    @if($sortField === 'studentID')
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Student Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Company
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Supervisor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                                    wire:click="sortBy('assigned_at')">
                                    Assigned Date
                                    @if($sortField === 'assigned_at')
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($students as $student)
                                @php
                                    $hasAssignment = $student->supervisorAssignment &&
                                                     $student->supervisorAssignment->status === \App\Models\SupervisorAssignment::STATUS_ASSIGNED;
                                    $canSelect = !$hasAssignment;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $this->isStudentSelected($student->studentID) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }} {{ !$canSelect ? 'opacity-60' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                               @if($this->isStudentSelected($student->studentID)) checked @endif
                                               @if(!$canSelect) disabled @endif
                                               wire:click="toggleStudentSelection('{{ $student->studentID }}')"
                                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                               title="{{ $canSelect ? 'Select for bulk auto-assignment' : 'Student already has a supervisor assigned' }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $student->studentID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->user->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $student->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($student->acceptedPlacementApplication)
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $student->acceptedPlacementApplication->companyName }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $student->acceptedPlacementApplication->companyCity }}, {{ $student->acceptedPlacementApplication->companyState }}</div>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($student->supervisorAssignment && $student->supervisorAssignment->supervisor)
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->supervisorAssignment->supervisor->user->name }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $student->supervisorAssignment->supervisor->lecturerID }}</div>
                                                    @if($student->supervisorAssignment->distance_km)
                                                        <div class="text-xs text-gray-400 dark:text-gray-500">Distance: {{ number_format($student->supervisorAssignment->distance_km, 2) }} km</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($student->supervisorAssignment)
                                            {{ $student->supervisorAssignment->assigned_at->format('Y-m-d') }}
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            @if(!$student->supervisorAssignment)
                                                <button type="button" wire:click="openAssignModal('{{ $student->studentID }}')"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                                    </svg>
                                                    <span>Assign</span>
                                                </button>
                                                <button type="button" wire:click="autoAssignSupervisor('{{ $student->studentID }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="autoAssignSupervisor('{{ $student->studentID }}')"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span wire:loading.remove wire:target="autoAssignSupervisor('{{ $student->studentID }}')" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                        <span>Auto Assign</span>
                                                    </span>
                                                    <span wire:loading wire:target="autoAssignSupervisor('{{ $student->studentID }}')" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                                        <span>Assigning...</span>
                                                    </span>
                                                </button>
                                            @else
                                                <button type="button" wire:click="viewAssignment({{ $student->supervisorAssignment->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="viewAssignment"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span wire:loading.remove wire:target="viewAssignment" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                        </svg>
                                                        <span>View</span>
                                                    </span>
                                                    <span wire:loading wire:target="viewAssignment" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-indigo-600 dark:text-indigo-400" />
                                                    </span>
                                                </button>
                                                <button type="button" wire:click="openEditModal({{ $student->supervisorAssignment->id }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 dark:hover:bg-yellow-900/30 rounded-lg transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                    <span>Edit</span>
                                                </button>
                                                <button type="button" wire:click="removeAssignment({{ $student->supervisorAssignment->id }})"
                                                    wire:confirm="Are you sure you want to remove this supervisor assignment?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="removeAssignment({{ $student->supervisorAssignment->id }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span wire:loading.remove wire:target="removeAssignment({{ $student->supervisorAssignment->id }})" class="flex items-center gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                        <span>Remove</span>
                                                    </span>
                                                    <span wire:loading wire:target="removeAssignment({{ $student->supervisorAssignment->id }})" class="flex items-center gap-1.5">
                                                        <x-loading-spinner size="h-4 w-4" color="text-red-600 dark:text-red-400" />
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($students->hasPages())
                    <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 sm:px-6 bg-white dark:bg-gray-800 rounded-b-lg">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Assign Supervisor Modal -->
    @if($showAssignModal && $selectedStudent)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50" wire:click="closeAssignModal">
            <div class="relative top-20 mx-auto p-5 border border-gray-300 dark:border-gray-700 w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Assign Supervisor</h3>
                        <button wire:click="closeAssignModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>

                    <!-- Student Info -->
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Student Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            <p class="text-gray-700 dark:text-gray-300"><strong>Name:</strong> {{ $selectedStudent->user->name }}</p>
                            <p class="text-gray-700 dark:text-gray-300"><strong>ID:</strong> {{ $selectedStudent->studentID }}</p>
                            @if($selectedStudent->program)
                                <p class="text-gray-700 dark:text-gray-300"><strong>Program:</strong> {{ $selectedStudent->program }}</p>
                            @endif
                            @if($selectedStudent->acceptedPlacementApplication)
                                <p class="text-gray-700 dark:text-gray-300"><strong>Company:</strong> {{ $selectedStudent->acceptedPlacementApplication->companyName }}</p>
                                <p class="md:col-span-2 text-gray-700 dark:text-gray-300"><strong>Location:</strong> {{ $selectedStudent->acceptedPlacementApplication->companyFullAddress }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Recommended Supervisors -->
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Recommended Supervisors (Best Match First)</h4>

                        @if(!empty($recommendedSupervisors))
                            <div class="max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg space-y-2 p-2">
                                @foreach($recommendedSupervisors as $index => $supervisor)
                                    @php
                                        $lecturer = $supervisor['lecturer'] ?? $supervisor;
                                        $score = $supervisor['score'] ?? 0;
                                        $breakdown = $supervisor['breakdown'] ?? [];
                                        $distance = $supervisor['distance_km'] ?? $supervisor['distance'] ?? null;
                                        $availableQuota = $supervisor['available_quota'] ?? 0;

                                        // Score color based on value
                                        $scorePercent = $score * 100;
                                        if ($scorePercent >= 80) $scoreColor = 'text-green-600 dark:text-green-400';
                                        elseif ($scorePercent >= 60) $scoreColor = 'text-blue-600 dark:text-blue-400';
                                        elseif ($scorePercent >= 40) $scoreColor = 'text-yellow-600 dark:text-yellow-400';
                                        else $scoreColor = 'text-gray-600 dark:text-gray-400';
                                    @endphp

                                    <label class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer border border-gray-200 dark:border-gray-700 rounded-lg transition-colors {{ $selectedSupervisorID == $lecturer->lecturerID ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-600' : '' }}">
                                        <input type="radio" name="supervisor" value="{{ $lecturer->lecturerID }}"
                                            wire:model="selectedSupervisorID"
                                            class="mt-1 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-500 focus:ring-indigo-500 dark:bg-gray-700">

                                        <div class="ml-3 flex-1">
                                            <!-- Header with name and total score -->
                                            <div class="flex justify-between items-start mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lecturer->user->name }}</p>
                                                        @if($index === 0)
                                                            <span class="px-2 py-0.5 text-xs font-medium bg-yellow-500 dark:bg-yellow-600 text-white rounded">Best Match</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $lecturer->lecturerID }} | {{ $lecturer->department ?? 'N/A' }} | {{ $lecturer->researchGroup ?? 'N/A' }}
                                                        @if($lecturer->program)
                                                            @php
                                                                $programCodes = [
                                                                    'Bachelor of Computer Science (Software Engineering) with Honours' => 'BCS',
                                                                    'Bachelor of Computer Science (Computer Systems & Networking) with Honours' => 'BCN',
                                                                    'Bachelor of Computer Science (Multimedia Software) with Honours' => 'BCM',
                                                                    'Bachelor of Computer Science (Cyber Security) with Honours' => 'BCY',
                                                                    'Diploma in Computer Science' => 'DRC',
                                                                ];
                                                                $programCode = $programCodes[$lecturer->program] ?? null;
                                                            @endphp
                                                            @if($programCode)
                                                                | {{ $programCode }}
                                                            @endif
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="text-right ml-4">
                                                    <div class="text-2xl font-bold {{ $scoreColor }}">{{ number_format($score * 100, 1) }}%</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Score</div>
                                                </div>
                                            </div>

                                            <!-- Score Breakdown -->
                                            @if(!empty($breakdown))
                                                <div class="grid grid-cols-4 gap-2 mb-3">
                                                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded p-2 border border-blue-100 dark:border-blue-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Course Match</div>
                                                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ number_format(($breakdown['course_match']['raw'] ?? 0) * 100) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['course_match']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                    <div class="bg-green-50 dark:bg-green-900/30 rounded p-2 border border-green-100 dark:border-green-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Travel Prefer</div>
                                                        <div class="text-sm font-bold text-green-600 dark:text-green-400">{{ number_format(($breakdown['preference_match']['raw'] ?? 0) * 100) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['preference_match']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                    <div class="bg-purple-50 dark:bg-purple-900/30 rounded p-2 border border-purple-100 dark:border-purple-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Distance</div>
                                                        <div class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ number_format(($breakdown['distance_score']['raw'] ?? 0) * 100, 1) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['distance_score']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                    <div class="bg-orange-50 dark:bg-orange-900/30 rounded p-2 border border-orange-100 dark:border-orange-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Workload</div>
                                                        <div class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ number_format(($breakdown['workload_score']['raw'] ?? 0) * 100) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['workload_score']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Additional Info -->
                                            <div class="flex flex-wrap gap-3 text-xs text-gray-600 dark:text-gray-400">
                                                @if($distance !== null)
                                                    <div class="flex items-center">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        <strong>Distance:</strong> {{ number_format($distance, 2) }} km
                                                    </div>
                                                @endif
                                                <div class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                    <strong>Quota:</strong> {{ $lecturer->current_assignments }}/{{ $lecturer->supervisor_quota }}
                                                    @if($availableQuota > 0)
                                                        <span class="text-green-600 ml-1">({{ $availableQuota }} available)</span>
                                                    @else
                                                        <span class="text-red-600 ml-1">(Full)</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <strong>Travel:</strong> {{ ucfirst($lecturer->travel_preference ?? 'N/A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <p class="text-sm text-yellow-800 dark:text-yellow-300">No available supervisors found. Please check quota settings or enable override.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Assignment Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Assignment Notes (Optional)
                        </label>
                        <textarea wire:model="assignmentNotes" rows="3"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                            placeholder="Add any notes about this assignment..."></textarea>
                    </div>

                    <!-- Validation Errors -->
                    @error('selectedSupervisorID')
                        <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-800 dark:text-red-300">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeAssignModal"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <span>Cancel</span>
                        </button>
                        <button wire:click="assignSupervisor"
                            wire:loading.attr="disabled"
                            wire:target="assignSupervisor"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm hover:shadow-md">
                            <span wire:loading.remove wire:target="assignSupervisor" class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                                <span>Assign Supervisor</span>
                            </span>
                            <span wire:loading wire:target="assignSupervisor" class="flex items-center gap-2">
                                <x-loading-spinner size="h-4 w-4" color="text-white" />
                                <span>Assigning...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Assignment Modal -->
    @if($showEditModal && $editAssignmentID)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50" wire:click="closeEditModal">
            <div class="relative top-20 mx-auto p-5 border border-gray-300 dark:border-gray-700 w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Supervisor Assignment</h3>
                        <button wire:click="closeEditModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            <strong>Note:</strong> Select a new supervisor to replace the current assignment. This will update the quota counts accordingly.
                        </p>
                    </div>

                    <!-- Available Supervisors -->
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Recommended Supervisors (Best Match First)</h4>

                        @if(!empty($recommendedSupervisors))
                            <div class="max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg space-y-2 p-2">
                                @foreach($recommendedSupervisors as $index => $supervisor)
                                    @php
                                        $lecturer = $supervisor['lecturer'] ?? $supervisor;
                                        $score = $supervisor['score'] ?? 0;
                                        $breakdown = $supervisor['breakdown'] ?? [];
                                        $distance = $supervisor['distance_km'] ?? $supervisor['distance'] ?? null;
                                        $availableQuota = $supervisor['available_quota'] ?? 0;

                                        // Score color based on value
                                        $scorePercent = $score * 100;
                                        if ($scorePercent >= 80) $scoreColor = 'text-green-600 dark:text-green-400';
                                        elseif ($scorePercent >= 60) $scoreColor = 'text-blue-600 dark:text-blue-400';
                                        elseif ($scorePercent >= 40) $scoreColor = 'text-yellow-600 dark:text-yellow-400';
                                        else $scoreColor = 'text-gray-600 dark:text-gray-400';
                                    @endphp

                                    <label class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer border border-gray-200 dark:border-gray-700 rounded-lg transition-colors {{ $newSupervisorID == $lecturer->lecturerID ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-600' : '' }}">
                                        <input type="radio" name="new_supervisor" value="{{ $lecturer->lecturerID }}"
                                            wire:model="newSupervisorID"
                                            class="mt-1 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-500 focus:ring-indigo-500 dark:bg-gray-700">

                                        <div class="ml-3 flex-1">
                                            <!-- Header with name and total score -->
                                            <div class="flex justify-between items-start mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lecturer->user->name }}</p>
                                                        @if($index === 0)
                                                            <span class="px-2 py-0.5 text-xs font-medium bg-yellow-500 dark:bg-yellow-600 text-white rounded">Best Match</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $lecturer->lecturerID }} | {{ $lecturer->department ?? 'N/A' }} | {{ $lecturer->researchGroup ?? 'N/A' }}
                                                        @if($lecturer->program)
                                                            @php
                                                                $programCodes = [
                                                                    'Bachelor of Computer Science (Software Engineering) with Honours' => 'BCS',
                                                                    'Bachelor of Computer Science (Computer Systems & Networking) with Honours' => 'BCN',
                                                                    'Bachelor of Computer Science (Multimedia Software) with Honours' => 'BCM',
                                                                    'Bachelor of Computer Science (Cyber Security) with Honours' => 'BCY',
                                                                    'Diploma in Computer Science' => 'DRC',
                                                                ];
                                                                $programCode = $programCodes[$lecturer->program] ?? null;
                                                            @endphp
                                                            @if($programCode)
                                                                | {{ $programCode }}
                                                            @endif
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="text-right ml-4">
                                                    <div class="text-2xl font-bold {{ $scoreColor }}">{{ number_format($score * 100, 1) }}%</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Score</div>
                                                </div>
                                            </div>

                                            <!-- Score Breakdown -->
                                            @if(!empty($breakdown))
                                                <div class="grid grid-cols-4 gap-2 mb-3">
                                                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded p-2 border border-blue-100 dark:border-blue-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Course Match</div>
                                                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ number_format(($breakdown['course_match']['raw'] ?? 0) * 100) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['course_match']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                    <div class="bg-green-50 dark:bg-green-900/30 rounded p-2 border border-green-100 dark:border-green-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Travel Prefer</div>
                                                        <div class="text-sm font-bold text-green-600 dark:text-green-400">{{ number_format(($breakdown['preference_match']['raw'] ?? 0) * 100) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['preference_match']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                    <div class="bg-purple-50 dark:bg-purple-900/30 rounded p-2 border border-purple-100 dark:border-purple-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Distance</div>
                                                        <div class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ number_format(($breakdown['distance_score']['raw'] ?? 0) * 100, 1) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['distance_score']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                    <div class="bg-orange-50 dark:bg-orange-900/30 rounded p-2 border border-orange-100 dark:border-orange-800">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Workload</div>
                                                        <div class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ number_format(($breakdown['workload_score']['raw'] ?? 0) * 100) }}%</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">({{ $breakdown['workload_score']['weight'] ?? '0%' }})</div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Additional Info -->
                                            <div class="flex flex-wrap gap-3 text-xs text-gray-600 dark:text-gray-400">
                                                @if($distance !== null)
                                                    <div class="flex items-center">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <span>{{ number_format($distance, 2) }} km</span>
                                                    </div>
                                                @endif
                                                <div class="flex items-center">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    <span>Quota: {{ $lecturer->current_assignments ?? 0 }}/{{ $lecturer->supervisor_quota ?? 0 }}</span>
                                                    @if($availableQuota > 0)
                                                        <span class="ml-1 text-green-600 dark:text-green-400">({{ $availableQuota }} available)</span>
                                                    @else
                                                        <span class="ml-1 text-red-600 dark:text-red-400">(Full)</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <p class="text-sm text-yellow-800 dark:text-yellow-300">No supervisors available.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Assignment Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Assignment Notes (Optional)
                        </label>
                        <textarea wire:model="assignmentNotes" rows="3"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                            placeholder="Add any notes about this assignment change..."></textarea>
                    </div>

                    <!-- Validation Errors -->
                    @error('newSupervisorID')
                        <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-800 dark:text-red-300">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeEditModal"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <span>Cancel</span>
                        </button>
                        <button wire:click="updateAssignment"
                            wire:loading.attr="disabled"
                            wire:target="updateAssignment"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-600 dark:hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm hover:shadow-md">
                            <span wire:loading.remove wire:target="updateAssignment" class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                                <span>Update Assignment</span>
                            </span>
                            <span wire:loading wire:target="updateAssignment" class="flex items-center gap-2">
                                <x-loading-spinner size="h-4 w-4" color="text-white" />
                                <span>Updating...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Assignment Detail Modal -->
    @if($showDetailModal && $selectedAssignment)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border border-gray-300 dark:border-gray-700 w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Assignment Details</h3>
                        <button wire:click="closeDetailModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>

                    <!-- Student Info -->
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Student</h4>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Name:</strong> {{ $selectedAssignment['student_name'] }}</p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>ID:</strong> {{ $selectedAssignment['student_id'] }}</p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Program:</strong> {{ $selectedAssignment['student_program'] ?? 'N/A' }}</p>
                        @if($selectedAssignment['company_name'])
                            <p class="text-gray-700 dark:text-gray-300"><strong>Company:</strong> {{ $selectedAssignment['company_name'] }}</p>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Location:</strong>
                                {{ $selectedAssignment['company_city'] }},
                                {{ $selectedAssignment['company_state'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Supervisor Info -->
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Supervisor</h4>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Name:</strong> {{ $selectedAssignment['supervisor_name'] }}</p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>ID:</strong> {{ $selectedAssignment['supervisor_id'] }}</p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Department:</strong> {{ $selectedAssignment['supervisor_department'] ?? 'N/A' }}</p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Research Group:</strong> {{ $selectedAssignment['supervisor_research_group'] ?? 'N/A' }}</p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Position:</strong> {{ $selectedAssignment['supervisor_position'] ?? 'N/A' }}</p>
                        @if($selectedAssignment['distance_km'])
                            <p class="text-gray-700 dark:text-gray-300"><strong>Distance:</strong> {{ number_format($selectedAssignment['distance_km'], 2) }} km</p>
                        @endif
                        @if($selectedAssignment['quota_override'])
                            <p class="text-yellow-600 dark:text-yellow-400"><strong>⚠️ Quota Override Applied</strong></p>
                            @if($selectedAssignment['override_reason'])
                                <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Reason:</strong> {{ $selectedAssignment['override_reason'] }}</p>
                            @endif
                        @endif
                    </div>

                    <!-- Assignment Info -->
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Assignment Details</h4>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Status:</strong>
                            <span class="px-2 py-1 text-xs rounded-full {{ $selectedAssignment['status'] === 'assigned' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                                {{ $selectedAssignment['status_display'] }}
                            </span>
                        </p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Assigned By:</strong>
                            {{ $selectedAssignment['assigned_by_name'] }}
                            @if($selectedAssignment['assigned_by_id'])
                                ({{ $selectedAssignment['assigned_by_id'] }})
                            @endif
                        </p>
                        <p class="text-gray-700 dark:text-gray-300"><strong>Assigned At:</strong> {{ $selectedAssignment['assigned_at'] }}</p>
                        @if($selectedAssignment['assignment_notes'])
                            <p class="text-gray-700 dark:text-gray-300"><strong>Notes:</strong> {{ $selectedAssignment['assignment_notes'] }}</p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end">
                        <button wire:click="closeDetailModal"
                            class="px-4 py-2 bg-gray-600 dark:bg-gray-700 text-white rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-gray-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
