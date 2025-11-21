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

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('errors'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
            <p class="font-semibold">Errors occurred during registration:</p>
            <ul class="mt-2 list-disc list-inside">
                @foreach (session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Registration Buttons -->
    <div class="mb-4 flex flex-wrap gap-2">
        <button wire:click="toggleBulkRegistration"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-upload mr-2"></i>
            Bulk Registration (CSV)
        </button>
        <button wire:click="toggleStudentRegistration"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="fas fa-user-plus mr-2"></i>
            Register Student
        </button>
        <button wire:click="toggleLecturerRegistration"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
            <i class="fas fa-user-plus mr-2"></i>
            Register Lecturer
        </button>
    </div>

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
                                <!-- Supervisor Quota -->
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Supervisor Quota</th>
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
                                        {{ $user->lecturer->supervisor_quota ?? '0' }}
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

    <!-- Bulk Registration Modal -->
    @if ($showBulkRegistration)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Bulk User Registration from CSV
                    </h3>

                    <form wire:submit.prevent="registerUsersFromCSV">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                CSV File
                            </label>
                            <input type="file" wire:model="csvFile" accept=".csv,.txt"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('csvFile')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Semester
                                </label>
                                <select wire:model="bulkSemester"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Select Semester</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                                @error('bulkSemester')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Year
                                </label>
                                <input type="number" wire:model="bulkYear" min="2020" max="2040"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('bulkYear')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button" wire:click="toggleBulkRegistration"
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Upload & Register
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Student Registration Modal -->
    @if ($showStudentRegistration)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-screen overflow-y-auto">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Register New Student
                    </h3>

                    <form wire:submit.prevent="registerStudent">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Basic Information -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name *
                                </label>
                                <input type="text" wire:model="studentName"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('studentName')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email *
                                </label>
                                <input type="email" wire:model="studentEmail"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('studentEmail')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Student ID *
                                </label>
                                <input type="text" wire:model="studentID"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('studentID')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Phone Number
                                </label>
                                <input type="text" wire:model="studentPhone"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('studentPhone')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Address Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Street Address
                                    </label>
                                    <input type="text" wire:model="studentAddress"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('studentAddress')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        City
                                    </label>
                                    <input type="text" wire:model="studentCity"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('studentCity')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Postcode
                                    </label>
                                    <input type="text" wire:model="studentPostcode"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('studentPostcode')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        State
                                    </label>
                                    <select wire:model="studentState"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                    </select>
                                    @error('studentState')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div x-data="{ search: '', open: false }">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Country
                                    </label>
                                    <div class="relative">
                                        <select wire:model="studentCountry" @focus="open = true"
                                            @blur="setTimeout(() => open = false, 200)"
                                            @input="search = $event.target.value"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                            <option value="Iceland">Iceland</option>
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
                                            <option value="Laos">Laos</option>
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
                                    </div>
                                    @error('studentCountry')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Automatic Geocoding Notice -->
                        <div class="mb-4">
                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 dark:text-blue-300">
                                            <strong>🗺️ Smart Geocoding:</strong> Latitude and longitude coordinates
                                            will be automatically determined from the address using Google Maps API.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Academic Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nationality
                                    </label>
                                    <input type="text" wire:model="studentNationality"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('studentNationality')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Program
                                    </label>
                                    <select wire:model="studentProgram"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">Select Program</option>
                                        <option value="BCS">BCS - Bachelor of Computer Science (Software
                                            Engineering)</option>
                                        <option value="BCN">BCN - Bachelor of Computer Science (Computer Systems &
                                            Networking)</option>
                                        <option value="BCG">BCG - Bachelor of Computer Science (Graphics &
                                            Multimedia)</option>
                                        <option value="BCY">BCY - Bachelor of Computer Science (Cybersecurity)
                                        </option>
                                        <option value="CS">CS - Diploma of Computer Science</option>
                                    </select>
                                    @error('studentProgram')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Semester *
                                    </label>
                                    <select wire:model="studentSemester"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">Select Semester</option>
                                        <option value="1">Semester 1</option>
                                        <option value="2">Semester 2</option>
                                    </select>
                                    @error('studentSemester')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Year *
                                    </label>
                                    <input type="number" wire:model="studentYear" min="2020" max="2040"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('studentYear')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button" wire:click="toggleStudentRegistration"
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Lecturer Registration Modal -->
    @if ($showLecturerRegistration)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-screen overflow-y-auto">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Register New Lecturer
                    </h3>

                    <form wire:submit.prevent="registerLecturer">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Basic Information -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name *
                                </label>
                                <input type="text" wire:model="lecturerName"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('lecturerName')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email *
                                </label>
                                <input type="email" wire:model="lecturerEmail"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('lecturerEmail')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Lecturer ID *
                                </label>
                                <input type="text" wire:model="lecturerID"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('lecturerID')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Staff Grade
                                </label>
                                <select wire:model="lecturerStaffGrade"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Select Staff Grade</option>
                                    <option value="VK6-A">VK6-A</option>
                                    <option value="VK7-A">VK7-A</option>
                                    <option value="DS51-A">DS51-A</option>
                                    <option value="DS52-A">DS52-A</option>
                                    <option value="DS53-A">DS53-A</option>
                                    <option value="DS54-A">DS54-A</option>
                                    <option value="DS45-A">DS45-A</option>
                                    <option value="FP">FP</option>
                                </select>
                                @error('lecturerStaffGrade')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Role
                                </label>
                                <select wire:model="lecturerRole"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Select Role</option>
                                    <option value="Management">Management</option>
                                    <option value="Non-Management">Non-Management</option>
                                </select>
                                @error('lecturerRole')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Position
                                </label>
                                <select wire:model="lecturerPosition"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Select Position</option>
                                    <option value="Dean">Dean</option>
                                    <option value="Deputy Dean(R)">Deputy Dean (Research)</option>
                                    <option value="Deputy Dean(A)">Deputy Dean (Academic)</option>
                                    <option value="Coordinator (s)">Coordinator (s)</option>
                                    <option value="Head of Programs">Head of Programs</option>
                                    <option value="Committee">Committee</option>
                                </select>
                                @error('lecturerPosition')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Address Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Street Address
                                    </label>
                                    <input type="text" wire:model="lecturerAddress"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('lecturerAddress')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        City
                                    </label>
                                    <input type="text" wire:model="lecturerCity"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('lecturerCity')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Postcode
                                    </label>
                                    <input type="text" wire:model="lecturerPostcode"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('lecturerPostcode')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        State
                                    </label>
                                    <select wire:model="lecturerState"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                    </select>
                                    @error('lecturerState')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div x-data="{ search: '', open: false }">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Country
                                    </label>
                                    <div class="relative">
                                        <select wire:model="lecturerCountry" @focus="open = true"
                                            @blur="setTimeout(() => open = false, 200)"
                                            @input="search = $event.target.value"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                            <option value="Iceland">Iceland</option>
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
                                            <option value="Laos">Laos</option>
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
                                    </div>
                                    @error('lecturerCountry')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Automatic Geocoding Notice -->
                        <div class="mb-4">
                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 dark:text-blue-300">
                                            <strong>🗺️ Smart Geocoding:</strong> Latitude and longitude coordinates
                                            will be automatically determined from the address using Google Maps API.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <div class="mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Professional
                                Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Research Group
                                    </label>
                                    <select wire:model="lecturerResearchGroup"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">Select Research Group</option>
                                        <option value="CSRG">CSRG - Computer System Research Group</option>
                                        <option value="VISIC">VISIC - Virtual Simulation & Computing</option>
                                        <option value="MIRG">MIRG - Machine Intelligence Research Group</option>
                                        <option value="Cy-SIG">Cy-SIG - Cyber Security Interest Group</option>
                                        <option value="SERG">SERG - Software Engineering</option>
                                        <option value="KECL">KECL - Knowledge Engineering & Computational Linguistics
                                        </option>
                                        <option value="DSSIM">DSSIM - Data Science & Simulation Modeling</option>
                                        <option value="DBIS">DBIS - Database Technology & Information System</option>
                                        <option value="EDU-TECH">EDU-TECH - Educational Technology</option>
                                        <option value="ISP">ISP - Image Signal Processing</option>
                                        <option value="CNRG">CNRG - Computer Network & Research Group</option>
                                        <option value="SCORE">SCORE - Soft Computing & Optimization</option>
                                    </select>
                                    @error('lecturerResearchGroup')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Department
                                    </label>
                                    <select wire:model="lecturerDepartment"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">Select Department</option>
                                        <option value="CS">CS</option>
                                        <option value="SN">SN</option>
                                        <option value="GMM">GMM</option>
                                        <option value="CY">CY</option>
                                    </select>
                                    @error('lecturerDepartment')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Semester *
                                    </label>
                                    <select wire:model="lecturerSemester"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">Select Semester</option>
                                        <option value="1">Semester 1</option>
                                        <option value="2">Semester 2</option>
                                    </select>
                                    @error('lecturerSemester')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Year *
                                    </label>
                                    <input type="number" wire:model="lecturerYear" min="2020" max="2040"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('lecturerYear')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Supervisor Quota
                                    </label>
                                    <input type="number" wire:model="lecturerSupervisorQuota" min="0"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('lecturerSupervisorQuota')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Permissions</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="lecturerIsAcademicAdvisor"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Academic
                                        Advisor</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="lecturerIsSupervisorFaculty"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Supervisor
                                        Faculty</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="lecturerIsCommittee"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Committee</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="lecturerIsCoordinator"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Coordinator</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="lecturerIsAdmin"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Admin</label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button" wire:click="toggleLecturerRegistration"
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">
                                Register Lecturer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
