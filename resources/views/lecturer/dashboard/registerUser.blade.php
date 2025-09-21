<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Register Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">User Registration</h3>
                        <p class="text-gray-600 dark:text-gray-400">Register users individually or upload a CSV file for
                            bulk registration.</p>

                        <!-- Individual Registration Buttons -->
                        <div class="flex space-x-4 mb-6">
                            <button onclick="openStudentModal()"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Register Student
                            </button>
                            <button onclick="openLecturerModal()"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Register Lecturer
                            </button>
                        </div>

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

                        @if (session('errors') && count(session('errors')) > 0)
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <h4 class="font-semibold">Errors encountered:</h4>
                                <ul class="list-disc list-inside mt-2">
                                    @foreach (session('errors') as $error)
                                        <li class="text-sm">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('lecturer.registerUser') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf

                        <!-- CSV File Upload -->
                        <div>
                            <x-input-label for="csv_file" :value="__('CSV File')" />
                            <input id="csv_file" type="file" name="csv_file" accept=".csv" required
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300" />
                            <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Upload a CSV file with user data. The file should contain columns: name, email, role,
                                student_id (for students).
                            </p>
                        </div>

                        <!-- Semester Selection -->
                        <div>
                            <x-input-label for="semester" :value="__('Semester')" />
                            <select id="semester" name="semester" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select Semester</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                            <x-input-error :messages="$errors->get('semester')" class="mt-2" />
                        </div>

                        <!-- Year Input -->
                        <div>
                            <x-input-label for="year" :value="__('Academic Year')" />
                            <input id="year" type="number" name="year" min="2020" max="2030"
                                value="{{ date('Y') }}" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                            <x-input-error :messages="$errors->get('year')" class="mt-2" />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('lecturer.dashboard') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md text-sm font-medium">
                                Cancel
                            </a>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium">
                                Register Users
                            </button>
                        </div>
                    </form>

                    <!-- CSV Format Example -->
                    <div class="mt-8 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">CSV Format Examples:</h4>

                        <div class="mb-4">
                            <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2">For Students:</h5>
                            <div class="text-sm text-gray-600 dark:text-gray-400 font-mono">
                                <div>name,email,role,studentID,phone,address,nationality,program</div>
                                <div>John Doe,john.doe@example.com,student,STU001,0123456789,123 Main
                                    St,Malaysian,Computer Science</div>
                                <div>Jane Smith,jane.smith@example.com,student,STU002,0123456790,456 Oak
                                    Ave,Malaysian,Information Technology</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2">For Lecturers:</h5>
                            <div class="text-sm text-gray-600 dark:text-gray-400 font-mono">
                                <div>name,email,role,lecturerID,staffGrade,position,state,researchGroup,department</div>
                                <div>Dr. Johnson,dr.johnson@example.com,lecturer,LEC001,Professor,Head of
                                    Department,Selangor,AI Research,Computer Science</div>
                                <div>Dr. Wilson,dr.wilson@example.com,lecturer,LEC002,Associate Professor,Lecturer,Kuala
                                    Lumpur,Data Science,Information Technology</div>
                            </div>
                        </div>

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Note: All fields except name, email, and role are optional. studentID is required for
                            students, lecturerID is required for lecturers.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Registration Modal -->
    <div id="studentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div
            class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Register New Student</h3>
                    <button onclick="closeStudentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('lecturer.registerStudent') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic Information -->
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Basic Information</h4>
                        </div>

                        <div>
                            <x-input-label for="student_name" :value="__('Full Name')" />
                            <input id="student_name" type="text" name="name" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="student_email" :value="__('Email')" />
                            <input id="student_email" type="email" name="email" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="studentID" :value="__('Student ID')" />
                            <input id="studentID" type="text" name="studentID" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="student_phone" :value="__('Phone')" />
                            <input id="student_phone" type="text" name="phone"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="student_address" :value="__('Address')" />
                            <textarea id="student_address" name="address" rows="2"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                        </div>

                        <div>
                            <x-input-label for="student_nationality" :value="__('Nationality')" />
                            <input id="student_nationality" type="text" name="nationality"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="student_program" :value="__('Program')" />
                            <input id="student_program" type="text" name="program"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeStudentModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Register Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lecturer Registration Modal -->
    <div id="lecturerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div
            class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Register New Lecturer</h3>
                    <button onclick="closeLecturerModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('lecturer.registerLecturer') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic Information -->
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Basic Information</h4>
                        </div>

                        <div>
                            <x-input-label for="lecturer_name" :value="__('Full Name')" />
                            <input id="lecturer_name" type="text" name="name" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturer_email" :value="__('Email')" />
                            <input id="lecturer_email" type="email" name="email" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturerID" :value="__('Lecturer ID')" />
                            <input id="lecturerID" type="text" name="lecturerID" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturer_staffGrade" :value="__('Staff Grade')" />
                            <input id="lecturer_staffGrade" type="text" name="staffGrade"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturer_position" :value="__('Position')" />
                            <input id="lecturer_position" type="text" name="position"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturer_state" :value="__('State')" />
                            <input id="lecturer_state" type="text" name="state"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturer_researchGroup" :value="__('Research Group')" />
                            <input id="lecturer_researchGroup" type="text" name="researchGroup"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturer_department" :value="__('Department')" />
                            <input id="lecturer_department" type="text" name="department"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <div>
                            <x-input-label for="lecturer_studentQuota" :value="__('Student Quota')" />
                            <input id="lecturer_studentQuota" type="number" name="studentQuota" min="0"
                                value="0"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>

                        <!-- Role Flags -->
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Role Assignments</h4>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="isAcademicAdvisor" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Academic Advisor</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="isSupervisorFaculty" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Supervisor Faculty</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="isCommittee" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Committee Member</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="isCoordinator" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Coordinator</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="isAdmin" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Administrator</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeLecturerModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Register Lecturer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for Modal Functionality -->
    <script>
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
            const studentModal = document.getElementById('studentModal');
            const lecturerModal = document.getElementById('lecturerModal');

            if (event.target === studentModal) {
                closeStudentModal();
            }
            if (event.target === lecturerModal) {
                closeLecturerModal();
            }
        }
    </script>
</x-app-layout>
