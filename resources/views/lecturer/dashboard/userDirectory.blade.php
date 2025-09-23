<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Directory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Header with Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">User Directory</h3>
                            <p class="text-gray-600 dark:text-gray-400">Advanced user management with search, sorting, and export capabilities</p>
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

                    <!-- Livewire User Directory Table -->
                    @livewire('user-directory-table')
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

                <form action="{{ route('lecturer.registerUsers') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- CSV File Upload -->
                    <div>
                        <x-input-label for="csv_file" :value="__('CSV File')" />
                        <input id="csv_file" type="file" name="csv_file" accept=".csv" required
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300" />
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

                <!-- CSV Format Examples -->
                    <div class="mt-4">
                        <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Download Sample Files:</h5>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Include the existing student and lecturer modals -->
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
