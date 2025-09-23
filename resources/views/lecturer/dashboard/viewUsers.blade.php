<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('View Registered Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Filter Form -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Filter Users</h3>

                        @if (session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('lecturer.filterUsers') }}" method="POST" class="space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Role Selection -->
                                <div>
                                    <x-input-label for="role" :value="__('Role')" />
                                    <select id="role" name="role" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select Role</option>
                                        <option value="student" {{ old('role', $role ?? '') == 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="lecturer" {{ old('role', $role ?? '') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                </div>

                                <!-- Semester Selection -->
                                <div>
                                    <x-input-label for="semester" :value="__('Semester')" />
                                    <select id="semester" name="semester" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select Semester</option>
                                        <option value="1" {{ old('semester', $semester ?? '') == '1' ? 'selected' : '' }}>Semester 1</option>
                                        <option value="2" {{ old('semester', $semester ?? '') == '2' ? 'selected' : '' }}>Semester 2</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('semester')" class="mt-2" />
                                </div>

                                <!-- Year Input -->
                                <div>
                                    <x-input-label for="year" :value="__('Academic Year')" />
                                    <input id="year" type="number" name="year" min="2020" max="2050"
                                        value="{{ old('year', $year ?? date('Y')) }}" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                                    <x-input-error :messages="$errors->get('year')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between">
                                <a href="{{ route('lecturer.dashboard') }}"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md text-sm font-medium">
                                    Back to Dashboard
                                </a>
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium">
                                    Filter Users
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Results Section -->
                    @if(isset($users) && $users->count() > 0)
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4">
                                {{ ucfirst($role) }} Users - Semester {{ $semester }}, Year {{ $year }}
                                <span class="text-sm text-gray-500">({{ $users->count() }} found)</span>
                            </h3>

                            @if($role === 'student')
                                <!-- Student Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student ID</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Address</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nationality</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Program</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Academic Advisor</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Industry Supervisor</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($users as $user)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $user->name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->email }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->studentID ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->phone ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->address ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->nationality ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->program ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $user->student->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ ucfirst($user->student->status ?? 'inactive') }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->academicAdvisorID ?? 'Not Assigned' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->industrySupervisorName ?? 'Not Assigned' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <!-- Lecturer Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Lecturer ID</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Staff Grade</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Position</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">State</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Research Group</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student Quota</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Roles</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($users as $user)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $user->name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->email }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->lecturerID ?? 'N/A' }}
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
                                                            @if($user->lecturer->isAcademicAdvisor)
                                                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Academic Advisor</span>
                                                            @endif
                                                            @if($user->lecturer->isSupervisorFaculty)
                                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Supervisor Faculty</span>
                                                            @endif
                                                            @if($user->lecturer->isCommittee)
                                                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Committee</span>
                                                            @endif
                                                            @if($user->lecturer->isCoordinator)
                                                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Coordinator</span>
                                                            @endif
                                                            @if($user->lecturer->isAdmin)
                                                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Admin</span>
                                                            @endif
                                                            @if(!$user->lecturer->isAcademicAdvisor && !$user->lecturer->isSupervisorFaculty && !$user->lecturer->isCommittee && !$user->lecturer->isCoordinator && !$user->lecturer->isAdmin)
                                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">No Special Roles</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @elseif(isset($users))
                        <div class="border-t pt-6">
                            <div class="text-center py-8">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No users found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    No {{ $role }} users found for Semester {{ $semester }}, Year {{ $year }}.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
