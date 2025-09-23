<!-- Lecturer Registration Modal -->
<div id="lecturerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Register New Lecturer</h3>
                <button onclick="closeLecturerModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
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
                        <x-input-label for="lecturer_role" :value="__('Role')" />
                        <input id="lecturer_role" type="text" name="role"
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

                    <!-- Semester Selection -->
                    <div>
                        <x-input-label for="lecturer_semester" :value="__('Semester')" />
                        <select id="lecturer_semester" name="semester" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                    </div>

                    <!-- Year Input -->
                    <div>
                        <x-input-label for="lecturer_year" :value="__('Academic Year')" />
                        <input id="lecturer_year" type="number" name="year" min="2020" max="2050"
                            value="{{ date('Y') }}" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
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
                        <i class="fas fa-user-tie mr-2"></i>Register Lecturer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
