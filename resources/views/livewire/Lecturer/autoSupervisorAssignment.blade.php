<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Auto Supervisor Assignment</h1>
                <p class="text-gray-600">AI-powered supervisor recommendations based on coursework, travel preference, distance, and workload</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100">
                            <span class="text-yellow-600 text-2xl">⏳</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Awaiting Assignment</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_unassigned'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <span class="text-green-600 text-2xl">✅</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Already Assigned</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_assigned'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Students List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Students Awaiting Supervisor Assignment</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Scope</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $student->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $student->studentID }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $student->program ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($student->acceptedPlacementApplication)
                                            <div class="text-sm text-gray-900">{{ $student->acceptedPlacementApplication->companyName }}</div>
                                            <div class="text-sm text-gray-500">{{ $student->acceptedPlacementApplication->companyCity }}, {{ $student->acceptedPlacementApplication->companyState }}</div>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($student->acceptedPlacementApplication && $student->acceptedPlacementApplication->jobscope)
                                            <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $student->acceptedPlacementApplication->jobscope }}">
                                                {{ $student->acceptedPlacementApplication->jobscope }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="openRecommendationModal('{{ $student->studentID }}')"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                            </svg>
                                            Get Recommendations
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="mt-2 text-lg font-semibold">All students have been assigned supervisors!</p>
                                        <p class="text-sm">Great job! All students with accepted placements now have supervisors.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($students->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recommendation Modal -->
    @if($showRecommendationModal && $selectedStudent)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeRecommendationModal">
            <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">AI Supervisor Recommendations</h3>
                            <p class="text-sm text-gray-600 mt-1">Based on coursework match, travel preference, proximity, and workload analysis</p>
                        </div>
                        <button wire:click="closeRecommendationModal" class="text-gray-400 hover:text-gray-500">
                            <span class="text-3xl">&times;</span>
                        </button>
                    </div>

                    <!-- Student Info -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Student Information
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Name:</p>
                                <p class="font-medium text-gray-900">{{ $selectedStudent->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Student ID:</p>
                                <p class="font-medium text-gray-900">{{ $selectedStudent->studentID }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Program:</p>
                                <p class="font-medium text-gray-900">{{ $selectedStudent->program ?? 'N/A' }}</p>
                            </div>
                            @if($selectedStudent->acceptedPlacementApplication)
                                <div>
                                    <p class="text-sm text-gray-600">Company:</p>
                                    <p class="font-medium text-gray-900">{{ $selectedStudent->acceptedPlacementApplication->companyName }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Job Scope:</p>
                                    <p class="font-medium text-gray-900">{{ $selectedStudent->acceptedPlacementApplication->jobscope ?? 'N/A' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recommendations -->
                    @if(empty($recommendations))
                        <div class="p-6 text-center bg-yellow-50 border border-yellow-200 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <p class="mt-4 text-lg font-medium text-gray-900">No recommendations available</p>
                            <p class="text-sm text-gray-600 mt-2">This could be due to:</p>
                            <ul class="text-sm text-gray-600 mt-2 text-left max-w-md mx-auto">
                                <li>• All supervisors are at full capacity</li>
                                <li>• No supervisors match the student's department</li>
                                <li>• Missing location data for distance calculation</li>
                            </ul>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($recommendations as $index => $rec)
                                @php
                                    $lecturer = $rec['lecturer'];
                                    $score = $rec['score'];
                                    $breakdown = $rec['breakdown'];
                                    $distance = $rec['distance_km'];
                                    $availableQuota = $rec['available_quota'];

                                    // Determine rank badge color
                                    $badgeColors = [
                                        0 => 'bg-yellow-500 text-white',  // Gold
                                        1 => 'bg-gray-400 text-white',    // Silver
                                        2 => 'bg-orange-600 text-white',  // Bronze
                                    ];
                                    $badgeColor = $badgeColors[$index] ?? 'bg-gray-300 text-gray-700';

                                    // Score color based on value
                                    $scorePercent = $score * 100;
                                    if ($scorePercent >= 80) $scoreColor = 'text-green-600';
                                    elseif ($scorePercent >= 60) $scoreColor = 'text-blue-600';
                                    elseif ($scorePercent >= 40) $scoreColor = 'text-yellow-600';
                                    else $scoreColor = 'text-gray-600';
                                @endphp

                                <div class="border-2 {{ $index === 0 ? 'border-indigo-500' : 'border-gray-200' }} rounded-lg p-5 hover:shadow-lg transition-shadow">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full {{ $badgeColor }} font-bold text-lg">
                                                #{{ $index + 1 }}
                                            </span>
                                            <div>
                                                <h5 class="text-lg font-bold text-gray-900">{{ $lecturer->user->name }}</h5>
                                                <p class="text-sm text-gray-600">{{ $lecturer->lecturerID }} • {{ $lecturer->department ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-3xl font-bold {{ $scoreColor }}">{{ number_format($score * 100, 1) }}%</div>
                                            <div class="text-xs text-gray-500">Match Score</div>
                                        </div>
                                    </div>

                                    <!-- Score Breakdown -->
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div class="bg-blue-50 rounded p-3">
                                            <div class="text-xs text-gray-600 mb-1">Course Match (40%)</div>
                                            <div class="text-lg font-bold text-blue-600">{{ number_format($breakdown['course_match']['raw'] * 100) }}%</div>
                                        </div>
                                        <div class="bg-green-50 rounded p-3">
                                            <div class="text-xs text-gray-600 mb-1">Travel Pref (30%)</div>
                                            <div class="text-lg font-bold text-green-600">{{ number_format($breakdown['preference_match']['raw'] * 100) }}%</div>
                                        </div>
                                        <div class="bg-purple-50 rounded p-3">
                                            <div class="text-xs text-gray-600 mb-1">Distance (20%)</div>
                                            <div class="text-lg font-bold text-purple-600">{{ number_format($breakdown['distance_score']['raw'] * 100) }}%</div>
                                        </div>
                                        <div class="bg-orange-50 rounded p-3">
                                            <div class="text-xs text-gray-600 mb-1">Workload (10%)</div>
                                            <div class="text-lg font-bold text-orange-600">{{ number_format($breakdown['workload_score']['raw'] * 100) }}%</div>
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <strong>Distance:</strong>&nbsp;{{ $distance ? number_format($distance, 2) . ' km' : 'N/A' }}
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <strong>Travel Pref:</strong>&nbsp;{{ ucfirst($lecturer->travel_preference) }}
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            <strong>Workload:</strong>&nbsp;{{ $lecturer->current_assignments }}/{{ $lecturer->supervisor_quota }}
                                            <span class="ml-1 text-green-600">({{ $availableQuota }} available)</span>
                                        </div>
                                        @if($lecturer->program)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                                <strong>Program:</strong>&nbsp;
                                                @php
                                                    $programNames = [
                                                        'BCS' => 'BCS - Bachelor of Computer Science (Software Engineering) with Honours',
                                                        'BCN' => 'BCN - Bachelor of Computer Science (Computer Systems & Networking) with Honours',
                                                        'BCM' => 'BCM - Bachelor of Computer Science (Multimedia Software) with Honours',
                                                        'BCY' => 'BCY - Bachelor of Computer Science (Cyber Security) with Honours',
                                                        'DRC' => 'DRC - Diploma in Computer Science'
                                                    ];
                                                @endphp
                                                {{ $programNames[$lecturer->program] ?? $lecturer->program }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Assign Button -->
                                    <button wire:click="assignSupervisor('{{ $lecturer->lecturerID }}', {{ $score }})"
                                        wire:loading.attr="disabled"
                                        wire:target="assignSupervisor"
                                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="assignSupervisor">Assign as Supervisor</span>
                                        <span wire:loading wire:target="assignSupervisor">Assigning...</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Optional Notes -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Notes (Optional)
                            </label>
                            <textarea wire:model="assignmentNotes" rows="2"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                placeholder="Add any additional notes about the assignment..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Note: System will automatically add recommendation details to the assignment.</p>
                        </div>
                    @endif

                    <!-- Close Button -->
                    <div class="mt-6 flex justify-end">
                        <button wire:click="closeRecommendationModal"
                            class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
