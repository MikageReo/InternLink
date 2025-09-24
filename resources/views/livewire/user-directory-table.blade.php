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

        .fa-file-excel:before {
            content: "📊";
        }

        .fa-file-csv:before {
            content: "📄";
        }

        .fa-file-pdf:before {
            content: "📄";
        }

        .fa-file-word:before {
            content: "📝";
        }

        .fa-times:before {
            content: "✖";
        }

        .fa-upload:before {
            content: "⬆";
        }

        .fa-user-plus:before {
            content: "👤+";
        }

        .fa-user-tie:before {
            content: "👨‍💼";
        }

        .fa-users:before {
            content: "👥";
        }
    </style>


    <!-- Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            @if ($role)
                Showing {{ $users->count() }} {{ $role }} users
            @else
                Select filters to view users
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 mt-2 sm:mt-0">
            @if ($role && $totalCount > 0)
                <!-- Export Format Selector -->
                <div class="flex items-center gap-2">
                    <select wire:model="exportFormat"
                        class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm
                                    dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                        <option value="word">Word</option>
                    </select>

                    <button wire:click="exportData" wire:loading.attr="disabled" wire:target="exportData"
                        title="Export filtered {{ $role }} data as {{ strtoupper($exportFormat) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white text-sm font-medium rounded-md transition-colors duration-200 shadow-sm">
                        <span wire:loading.remove wire:target="exportData">
                            @if ($exportFormat === 'csv')
                                <i class="fas fa-file-csv mr-2"></i>
                            @elseif($exportFormat === 'pdf')
                                <i class="fas fa-file-pdf mr-2"></i>
                            @else
                                <i class="fas fa-file-word mr-2"></i>
                            @endif
                            Download
                        </span>
                        <span wire:loading wire:target="exportData" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Downloading...
                        </span>
                    </button>
                </div>
            @endif

            <button wire:click="clearFilters"
                class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-times mr-2"></i>
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="flex flex-wrap gap-4 items-end">

        <!-- Role Filter -->
        <div class="w-full md:w-40">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
            <select wire:model.live="role"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Select</option>
                <option value="student">Student</option>
                <option value="lecturer">Lecturer</option>
            </select>
        </div>

        <!-- Semester Filter -->
        <div class="w-full md:w-40">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Semester</label>
            <select wire:model.live="semester"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
            </select>
        </div>

        <!-- Year Filter -->
        <div class="w-full md:w-32">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
            <input type="number" wire:model.live="year" min="2020" max="2050"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        <!-- Per Page -->
        <div class="w-full md:w-24">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Show</label>
            <select wire:model.live="perPage"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <!-- Search Bar -->
        <div class="w-full md:w-64">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search all columns..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500
                                focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm
                                dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
            </div>
        </div>
    </div>

    <br>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed top-0 left-0 right-0 z-50">
        <div class="bg-indigo-500 h-1">
            <div class="bg-white h-1 rounded animate-pulse"></div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
        @if ($role && $users->count() > 0)
            <div class="overflow-x-auto">
                @if ($role === 'student')
                    <!-- Student Table -->
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <!-- Sortable Headers for Students -->
                                <!-- StudentID -->
                                <th wire:click="sortBy('studentID')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Student ID</span>
                                        @if ($sortField === 'studentID')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Email -->
                                <th wire:click="sortBy('email')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Email</span>
                                        @if ($sortField === 'email')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Name -->
                                <th wire:click="sortBy('name')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Name</span>
                                        @if ($sortField === 'name')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Program -->
                                <th wire:click="sortBy('program')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Program</span>
                                        @if ($sortField === 'program')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Academic Advisor -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Academic Advisor</th>
                                <!-- Industry Supervisor -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Industry Supervisor</th>
                                <!-- Phone -->
                                <th wire:click="sortBy('phone')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Phone</span>
                                        @if ($sortField === 'phone')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Status -->
                                <th wire:click="sortBy('status')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Status</span>
                                        @if ($sortField === 'status')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                    <!-- Address -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Address</th>
                                <!-- Nationality -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Nationality</th>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $user->student->studentID ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->student->program ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->student->academicAdvisorID ?? 'Not Assigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->student->industrySupervisorName ?? 'Not Assigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->student->phone ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ ($user->student->status ?? 'inactive') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($user->student->status ?? 'inactive') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->student->address ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->student->nationality ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <!-- Lecturer Table -->
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <!-- Sortable Headers for Lecturers -->
                                <!-- LecturerID -->
                                <th wire:click="sortBy('lecturerID')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Lecturer ID</span>
                                        @if ($sortField === 'lecturerID')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Email -->
                                <th wire:click="sortBy('email')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Email</span>
                                        @if ($sortField === 'email')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Name -->
                                <th wire:click="sortBy('name')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Name</span>
                                        @if ($sortField === 'name')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Staff Grade -->
                                <th wire:click="sortBy('staffGrade')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Staff Grade</span>
                                        @if ($sortField === 'staffGrade')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Role -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Role</th>
                                <th wire:click="sortBy('position')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Position</span>
                                        @if ($sortField === 'position')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- State -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    State</th>
                                <!-- Research Group -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Research Group</th>
                                <!-- Department -->
                                <th wire:click="sortBy('department')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <span>Department</span>
                                        @if ($sortField === 'department')
                                            @if ($sortDirection === 'asc')
                                                <i class="fas fa-sort-up text-indigo-500 sort-icon"></i>
                                            @else
                                                <i class="fas fa-sort-down text-indigo-500 sort-icon"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400 opacity-50 sort-icon"></i>
                                        @endif
                                    </div>
                                </th>
                                <!-- Student Quota -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Student Quota</th>
                                <!-- Special Roles -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Special Roles</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $user->lecturer->lecturerID ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->lecturer->staffGrade ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->lecturer->role ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->lecturer->position ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->lecturer->state ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->lecturer->researchGroup ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->lecturer->department ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $user->lecturer->studentQuota ?? '0' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($user->lecturer->isAcademicAdvisor)
                                                <span
                                                    class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Academic
                                                    Advisor</span>
                                            @endif
                                            @if ($user->lecturer->isSupervisorFaculty)
                                                <span
                                                    class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Supervisor
                                                    Faculty</span>
                                            @endif
                                            @if ($user->lecturer->isCommittee)
                                                <span
                                                    class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Committee</span>
                                            @endif
                                            @if ($user->lecturer->isCoordinator)
                                                <span
                                                    class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Coordinator</span>
                                            @endif
                                            @if ($user->lecturer->isAdmin)
                                                <span
                                                    class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Admin</span>
                                            @endif
                                            @if (
                                                !$user->lecturer->isAcademicAdvisor &&
                                                    !$user->lecturer->isSupervisorFaculty &&
                                                    !$user->lecturer->isCommittee &&
                                                    !$user->lecturer->isCoordinator &&
                                                    !$user->lecturer->isAdmin)
                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">No
                                                    Special Roles</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Pagination -->
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                {{ $users->links() }}
            </div>
        @elseif($role)
            <!-- No Results -->
            <div class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">
                    <i class="fas fa-search text-4xl mb-4"></i>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No {{ $role }} users
                    found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Try adjusting your search criteria or filters.
                </p>
            </div>
        @else
            <!-- Welcome State -->
            <div class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">
                    <i class="fas fa-users text-4xl mb-4"></i>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Welcome to User Directory</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Select a role filter to view users in the directory.
                </p>
            </div>
        @endif
    </div>
</div>
