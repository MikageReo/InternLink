<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Header with Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">User Management</h3>
                            <p class="text-gray-600 dark:text-gray-400">Manage registered users and add new ones</p>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                            <button onclick="openStudentModal()"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-user-plus mr-2"></i>Register Student
                            </button>
                            <button onclick="openLecturerModal()"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-user-tie mr-2"></i>Register Lecturer
                            </button>
                            <button onclick="openCsvModal()"
                                class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-upload mr-2"></i>Upload CSV
                            </button>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
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

                    @if (session('csvErrors') && count(session('csvErrors')) > 0)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <h4 class="font-semibold">Errors encountered:</h4>
                            <ul class="list-disc list-inside mt-2">
                                @foreach (session('csvErrors') as $error)
                                    <li class="text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Filter Form -->
                    <div class="mb-6 border-b pb-6">
                        <h4 class="text-md font-semibold mb-4">Filter Users</h4>

                        <form action="{{ route('lecturer.manageUsers.filter') }}" method="POST" class="space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Role Selection -->
                                <div>
                                    <x-input-label for="role" :value="__('Role')" />
                                    <select id="role" name="role" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select Role</option>
                                        <option value="student"
                                            {{ old('role', $role ?? '') == 'student' ? 'selected' : '' }}>Student
                                        </option>
                                        <option value="lecturer"
                                            {{ old('role', $role ?? '') == 'lecturer' ? 'selected' : '' }}>Lecturer
                                        </option>
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                </div>

                                <!-- Semester Selection -->
                                <div>
                                    <x-input-label for="semester" :value="__('Semester')" />
                                    <select id="semester" name="semester" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select Semester</option>
                                        <option value="1"
                                            {{ old('semester', $semester ?? '') == '1' ? 'selected' : '' }}>Semester 1
                                        </option>
                                        <option value="2"
                                            {{ old('semester', $semester ?? '') == '2' ? 'selected' : '' }}>Semester 2
                                        </option>
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

                                <!-- Submit Button -->
                                <div class="flex items-end">
                                    <button type="submit"
                                        class="w-full bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-md text-sm font-medium">
                                        <i class="fas fa-search mr-2"></i>Filter Users
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Results Section -->
                    @if (isset($users) && $users->count() > 0)
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4">
                                {{ ucfirst($role) }} Users - Semester {{ $semester }}, Year {{ $year }}
                                <span class="text-sm text-gray-500">({{ $users->count() }} found)</span>
                            </h3>

                            @if ($role === 'student')
                                <!-- Student Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Name</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Email</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Student ID</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Phone</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Address</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Nationality</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Program</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Status</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Academic Advisor</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Industry Supervisor</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($users as $user)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $user->name }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->email }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->studentID ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->phone ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->address ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->nationality ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->program ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $user->student->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ ucfirst($user->student->status ?? 'inactive') }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->student->academicAdvisorID ?? 'Not Assigned' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
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
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Name</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Email</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Lecturer ID</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Staff Grade</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Role</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Position</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    State</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Research Group</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Department</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Student Quota</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Special Roles</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($users as $user)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $user->name }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->email }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->lecturerID ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->staffGrade ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->role ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->position ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->state ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->researchGroup ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                        {{ $user->lecturer->department ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
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
                                                                <span
                                                                    class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">No
                                                                    Special Roles</span>
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
                                    <i class="fas fa-search text-4xl mb-4"></i>
                                </div>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No users found
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    No {{ $role }} users found for Semester {{ $semester }}, Year
                                    {{ $year }}.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users text-4xl mb-4"></i>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Welcome to User
                                Management</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Use the filter above to view users or register new ones using the buttons above.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- CSV Upload Modal -->
    <div id="csvModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Upload CSV File</h3>
                    <button onclick="closeCsvModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Sample CSV Download -->
                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Download sample CSV format:</p>
                    <div class="flex space-x-4">
                        <a href="{{ asset('sample/sample_students.csv') }}" download
                           class="text-blue-600 dark:text-blue-400 hover:underline">
                            <i class="fas fa-file-csv mr-1"></i> Student Sample
                        </a>
                        <a href="{{ asset('sample/sample_lecturers.csv') }}" download
                           class="text-blue-600 dark:text-blue-400 hover:underline">
                            <i class="fas fa-file-csv mr-1"></i> Lecturer Sample
                        </a>
                    </div>
                </div>

                <form action="{{ route('lecturer.registerUsers') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <!-- CSV File Upload -->
                    <div>
                        <x-input-label for="csv_file" :value="__('CSV File')" />
                        <input id="csv_file" type="file" name="csv_file" accept=".csv" required
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Upload a CSV file with user data. The file should contain columns: name, email, studentID
                            (for students) or lecturerID (for lecturers).
                        </p>
                    </div>

                    <!-- Semester Selection -->
                    <div>
                        <x-input-label for="csv_semester" :value="__('Semester')" />
                        <select id="csv_semester" name="semester" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                    </div>

                    <!-- Year Input -->
                    <div>
                        <x-input-label for="csv_year" :value="__('Academic Year')" />
                        <input id="csv_year" type="number" name="year" min="2020" max="2050"
                            value="{{ date('Y') }}" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeCsvModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                            class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-upload mr-2"></i>Upload CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include the existing student and lecturer modals from registerUser.blade.php -->
    @include('lecturer.dashboard.partials.student-modal')
    @include('lecturer.dashboard.partials.lecturer-modal')

    <!-- JavaScript for Modal Functionality -->
    <script>
        function openCsvModal() {
            document.getElementById('csvModal').classList.remove('hidden');
        }

        function closeCsvModal() {
            document.getElementById('csvModal').classList.add('hidden');
        }

        function openStudentModal() {
            document.getElementById('studentModal').classList.remove('hidden');
        }

        function closeStudentModal() {
            document.getElementById('studentModal').classList.add('hidden');
        }

        function openLecturerModal() {
            document.getElementById('lecturerModal').classList.remove('hidden');
        }

        function closeLecturerModal() {
            document.getElementById('lecturerModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const csvModal = document.getElementById('csvModal');
            const studentModal = document.getElementById('studentModal');
            const lecturerModal = document.getElementById('lecturerModal');

            if (event.target === csvModal) {
                closeCsvModal();
            }
            if (event.target === studentModal) {
                closeStudentModal();
            }
            if (event.target === lecturerModal) {
                closeLecturerModal();
            }
        }
    </script>
</x-app-layout>
