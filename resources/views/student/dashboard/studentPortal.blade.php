<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600 dark:text-gray-400">You are logged in as a Student.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Course Enrollments -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">My Courses</h4>
                            <p class="text-blue-600 dark:text-blue-300 text-sm">View your enrolled courses and
                                assignments</p>
                            <button class="mt-3 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                                View Courses
                            </button>
                        </div>

                        <!-- Assignments -->
                        <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-green-800 dark:text-green-200 mb-2">Assignments</h4>
                            <p class="text-green-600 dark:text-green-300 text-sm">Check your pending assignments and
                                deadlines</p>
                            <button class="mt-3 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                                View Assignments
                            </button>
                        </div>

                        <!-- Grades -->
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-purple-800 dark:text-purple-200 mb-2">Grades</h4>
                            <p class="text-purple-600 dark:text-purple-300 text-sm">Check your academic performance</p>
                            <button class="mt-3 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm">
                                View Grades
                            </button>
                        </div>

                        <!-- Schedule -->
                        <div class="bg-orange-50 dark:bg-orange-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-orange-800 dark:text-orange-200 mb-2">Class Schedule</h4>
                            <p class="text-orange-600 dark:text-orange-300 text-sm">View your class timetable</p>
                            <button class="mt-3 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm">
                                View Schedule
                            </button>
                        </div>

                        <!-- Course Verification -->
                        <div class="bg-teal-50 dark:bg-green-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-teal-800 dark:text-teal-200 mb-2">Course Verification</h4>
                            <p class="text-teal-600 dark:text-teal-300 text-sm">Submit and track your course
                                verification applications</p>
                                <div class="mt-3">
                            <a href="{{ route('student.courseVerification') }}"
                                class="mt-3 bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded text-sm inline-block">
                                Manage Applications
                            </a>
                            </div>
                        </div>

                        <!-- Internship Placement -->
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">Internship Placement</h4>
                            <p class="text-indigo-600 dark:text-indigo-300 text-sm">Apply for internship placements and manage your applications</p>
                            <div class="mt-3">
                                <a href="{{ route('student.placementApplications') }}"
                                    class="mt-3 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded text-sm inline-block">
                                    Manage Applications
                                </a>
                            </div>
                        </div>

                        <!-- Request Defer -->
                        <div class="bg-amber-50 dark:bg-amber-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-amber-800 dark:text-amber-200 mb-2">Request Defer</h4>
                            <p class="text-amber-600 dark:text-amber-300 text-sm">Submit and manage your internship defer requests</p>
                            <div class="mt-3">
                                <a href="{{ route('student.requestDefer') }}"
                                    class="mt-3 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded text-sm inline-block">
                                    📝 Request Defer
                                </a>
                            </div>
                        </div>

                        <!-- Support -->
                        <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">Student Support</h4>
                            <p class="text-red-600 dark:text-red-300 text-sm">Get help and support services</p>
                            <button class="mt-3 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
                                Get Support
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
