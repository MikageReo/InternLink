<!-- Student Registration Modal -->
<div id="studentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Register New Student</h3>
                <button onclick="closeStudentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
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

                    <!-- Semester Selection -->
                    <div>
                        <x-input-label for="student_semester" :value="__('Semester')" />
                        <select id="student_semester" name="semester" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                    </div>

                    <!-- Year Input -->
                    <div>
                        <x-input-label for="student_year" :value="__('Academic Year')" />
                        <input id="student_year" type="number" name="year" min="2020" max="2050"
                            value="{{ date('Y') }}" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeStudentModal()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-user-plus mr-2"></i>Register Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
