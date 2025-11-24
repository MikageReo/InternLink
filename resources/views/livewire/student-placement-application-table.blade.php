<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-start justify-between">
                <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Internship Placement Applications</h1>
                <p class="text-gray-600">Submit and manage your internship placement applications</p>
                </div>
                <a href="{{ asset('documents/placement-application-guide.pdf') }}"
                   target="_blank"
                   download
                   class="inline-flex items-center px-4 py-2 border border-blue-400 rounded-md text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Application Guide
                </a>
            </div>

            <!-- Course Verification Alert -->
            @if (!$hasCourseVerification)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-900">Course Verification Required</h3>
                            <p class="text-red-700">You must have an approved course verification before applying for internship placement.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Approved Change Request Alert -->
            @if ($hasApprovedChangeRequest)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-blue-400 text-xl">🔄</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-900">Change Request Approved</h3>
                            <p class="text-blue-700">Your placement change request has been approved! You can now submit a new placement application below.</p>
                            <p class="text-blue-600 text-sm mt-1">Note: Your current placement will remain active until you accept a new placement.</p>
                        </div>
                    </div>
                </div>
            @elseif ($hasAcceptedApplication && !$canApply)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-yellow-400 text-xl">🔒</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-yellow-900">Application Restriction</h3>
                            <p class="text-yellow-700">You already have an accepted placement application. To submit a new application, you must first request a placement change.</p>
                            <p class="text-yellow-600 text-sm mt-1">Use the "Request Change" button on your accepted application to begin the process.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Flash Messages -->
            @if (session()->has('message'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700">{{ session('message') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Main Content Card -->
            <div class="bg-white shadow rounded-lg">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">Your Applications</h2>
                        @if ($canApply)
                            <button wire:click="openForm" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                @if ($hasApprovedChangeRequest)
                                    🔄 New Application
                                @else
                                    ➕ New Application
                                @endif
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <input type="text" wire:model.live.debounce.300ms="search"
                               class="flex-1 border border-gray-300 rounded-md px-3 py-2"
                               placeholder="Search applications...">
                        <select wire:model.live="statusFilter" class="border border-gray-300 rounded-md px-3 py-2">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($applications as $application)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $application->applicationID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->companyName }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->position }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $application->applicationDate->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs space-y-1">
                                            <div>
                                                <span class="font-medium">Committee:</span>
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' :
                                                       ($application->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $application->committeeStatus }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Coordinator:</span>
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' :
                                                       ($application->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $application->coordinatorStatus }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($application->remarks)
                                            <div class="max-w-xs truncate" title="{{ $application->remarks }}">
                                                {{ $application->remarks }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <!-- Student Acceptance Actions -->
                                            @if ($application->can_accept && !$hasAcceptedApplication)
                                                <button wire:click="acceptApplication({{ $application->applicationID }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700 hover:bg-green-200 disabled:opacity-50"
                                                    wire:loading.attr="disabled" wire:target="acceptApplication({{ $application->applicationID }})">
                                                    <span wire:loading.remove wire:target="acceptApplication({{ $application->applicationID }})">
                                                        ✅ Accept
                                                    </span>
                                                    <span wire:loading wire:target="acceptApplication({{ $application->applicationID }})" class="flex items-center">
                                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-green-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Accepting...
                                                    </span>
                                                </button>
                                                <button wire:click="declineApplication({{ $application->applicationID }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700 hover:bg-red-200 disabled:opacity-50"
                                                    wire:loading.attr="disabled" wire:target="declineApplication({{ $application->applicationID }})">
                                                    <span wire:loading.remove wire:target="declineApplication({{ $application->applicationID }})">
                                                        ❌ Decline
                                                    </span>
                                                    <span wire:loading wire:target="declineApplication({{ $application->applicationID }})" class="flex items-center">
                                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-red-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Declining...
                                                    </span>
                                                </button>
                                            @elseif ($application->studentAcceptance)
                                                <span class="inline-flex px-2 py-1 text-xs rounded-full
                                                    {{ $application->studentAcceptance === 'Accepted' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $application->studentAcceptance }}
                                                </span>
                                            @endif

                                            <!-- View/Edit Actions -->
                                            @if ($application->committeeStatus === 'Pending' && $application->coordinatorStatus === 'Pending')
                                                <!-- Edit button - only when both are pending -->
                                                <button wire:click="edit({{ $application->applicationID }})"
                                                    class="text-blue-600 hover:text-blue-900" title="Edit application">
                                                    ✏️ Edit
                                                </button>
                                            @else
                                                <!-- View button - when any approval has been made -->
                                                <button wire:click="view({{ $application->applicationID }})"
                                                    class="text-gray-600 hover:text-gray-900" title="View details (read-only)">
                                                    👁️ View
                                                </button>
                                            @endif

                                            <!-- Change Request Actions - only for approved and accepted applications -->
                                            @if ($application->overall_status === 'Approved' && $application->studentAcceptance === 'Accepted')
                                                @php
                                                    $hasChangeRequests = $application->changeRequests->count() > 0;
                                                    $hasPendingChangeRequest = $application->changeRequests->where(function($cr) {
                                                        return $cr->committeeStatus === 'Pending' || $cr->coordinatorStatus === 'Pending';
                                                    })->count() > 0;
                                                @endphp

                                                @if (!$hasPendingChangeRequest)
                                                    <button wire:click="openChangeRequestForm({{ $application->applicationID }})"
                                                        class="inline-flex items-center px-2 py-1 text-xs rounded bg-orange-100 text-orange-700 hover:bg-orange-200"
                                                        title="Request changes to this application">
                                                        🔄 Request Change
                                                    </button>
                                                @endif

                                                @if ($hasChangeRequests)
                                                    <button wire:click="viewChangeRequests({{ $application->applicationID }})"
                                                        class="inline-flex items-center px-2 py-1 text-xs rounded bg-blue-100 text-blue-700 hover:bg-blue-200"
                                                        title="View change request history">
                                                        📋 View Changes
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <p class="text-lg font-medium mb-2">No applications found</p>
                                            <p class="text-sm">You haven't submitted any internship applications yet.</p>
                                            @if ($canApply)
                                                <button wire:click="openForm"
                                                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                    @if ($hasApprovedChangeRequest)
                                                        Submit New Application
                                                    @else
                                                        Submit Your First Application
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($applications->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Application Form Modal -->
    @if ($showForm)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
             wire:click="closeForm"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $editingId ? 'Edit Application' : 'New Internship Application' }}
                        </h3>
                        <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                            ✖
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 relative">
                    <!-- Loading Overlay -->
                    <div wire:loading wire:target="submit" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 rounded">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">{{ $editingId ? 'Updating application...' : 'Submitting application...' }}</span>
                        </div>
                    </div>

                    <!-- Information Box -->
                    @if (!$editingId)
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-semibold mb-2">📝 Before you start, make sure you have:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>Company's complete contact information and address</li>
                                        <li>Official offer letter or acceptance form</li>
                                        <li>Clear job scope and responsibilities</li>
                                        <li>Confirmed internship start and end dates</li>
                                        <li>Supporting documents in PDF or image format</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Company Information Section -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Company Information</h4>
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                            <input type="text" wire:model="companyName"
                                   placeholder="e.g., Tech Solutions Sdn Bhd"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('companyName')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Company Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Email *</label>
                            <input type="email" wire:model="companyEmail"
                                   placeholder="e.g., hr@company.com"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('companyEmail')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Company Address -->
                        <!-- Company Address Information -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-medium text-gray-700 mb-2">Company Address</h4>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Street Address *</label>
                            <input type="text" wire:model="companyAddressLine"
                                   placeholder="e.g., No. 123, Jalan Technology Park 2, Bukit Jalil"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('companyAddressLine')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" wire:model="companyCity"
                                   placeholder="e.g., Kuala Lumpur, Johor Bahru"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('companyCity')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                            <input type="text" wire:model="companyPostcode"
                                   placeholder="e.g., 50400"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('companyPostcode')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <select wire:model="companyState"
                                    class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
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
                            @error('companyState')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <select wire:model="companyCountry"
                                    class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
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
                            @error('companyCountry')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Automatic Geocoding Notice -->
                        <div class="md:col-span-2">
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>🗺️ Smart Geocoding:</strong> Company location coordinates will be automatically determined from the address using Google Maps API.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Phone *</label>
                            <input type="text" wire:model="companyNumber"
                                   placeholder="e.g., +603-1234 5678 or 011-1234 5678"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('companyNumber')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Monthly Allowance -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Allowance (RM)</label>
                            <input type="number" wire:model="allowance" step="0.01" min="0"
                                   placeholder="e.g., 800.00"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('allowance')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-gray-500 mt-1">Leave blank if no allowance provided</p>
                        </div>

                        <!-- Industry Supervisor Information Section -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Industry Supervisor Information</h4>
                        </div>

                        <!-- Industry Supervisor Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor Name *</label>
                            <input type="text" wire:model="industrySupervisorName"
                                   placeholder="e.g., Dr. Ahmad bin Abdullah"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('industrySupervisorName')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Industry Supervisor Contact -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor Contact Number *</label>
                            <input type="text" wire:model="industrySupervisorContact"
                                   placeholder="e.g., +603-1234 5678 or 011-1234 5678"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('industrySupervisorContact')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Industry Supervisor Email -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor Email *</label>
                            <input type="email" wire:model="industrySupervisorEmail"
                                   placeholder="e.g., ahmad.abdullah@company.com"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('industrySupervisorEmail')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Position Information Section -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Position Details</h4>
                        </div>

                        <!-- Position -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                            <input type="text" wire:model="position"
                                   placeholder="e.g., Software Developer Intern, Marketing Intern"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('position')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Method of Work -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Method of Work *</label>
                            <select wire:model="methodOfWork"
                                    class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select method of work</option>
                                <option value="WFO">🏢 Work From Office (WFO)</option>
                                <option value="WOS">🏗️ Work On Site (WOS)</option>
                                <option value="WOC">🎓 Work On Campus (WOC)</option>
                                <option value="WFH">🏠 Work From Home (WFH)</option>
                                <option value="WFO & WFH">🔄 Hybrid (Office & Home)</option>
                            </select>
                            @error('methodOfWork')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Job Scope -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Scope *</label>
                            <textarea wire:model="jobscope" rows="5"
                                      class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Describe your main responsibilities and tasks during the internship. For example:&#10;• Assist in developing web applications using Laravel and Vue.js&#10;• Collaborate with team members on software projects&#10;• Participate in code reviews and testing&#10;• Document development processes"></textarea>
                            @error('jobscope')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-gray-500 mt-1">Be specific and detailed about your responsibilities</p>
                        </div>

                        <!-- Duration Section -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Internship Duration</h4>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                            <input type="date" wire:model="startDate"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('startDate')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-gray-500 mt-1">Internship start date</p>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                            <input type="date" wire:model="endDate"
                                   class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('endDate')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-gray-500 mt-1">Internship end date (must be after start date)</p>
                        </div>

                        <!-- File Upload Section -->
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Supporting Documents</h4>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 transition-colors">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Upload Files (Offer Letter, Acceptance Form, etc.)
                                    </span>
                                </label>
                                <input type="file" wire:model="applicationFiles" multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <div class="mt-2 flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-xs text-gray-600">
                                        <p class="font-medium">Recommended documents:</p>
                                        <ul class="list-disc list-inside mt-1 space-y-0.5">
                                            <li>Offer letter from the company</li>
                                            <li>Acceptance form (if provided)</li>
                                            <li>Company profile or brochure</li>
                                            <li>Job description document</li>
                                        </ul>
                                        <p class="mt-2 text-gray-500">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG • Max: 10MB per file</p>
                                    </div>
                                </div>
                                @error('applicationFiles.*')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                            </div>

                            <!-- Show existing files if editing -->
                            @if ($editingId && !empty($existingFiles))
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current Files:</p>
                                    <div class="space-y-2">
                                        @foreach ($existingFiles as $file)
                                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                                <span>📄</span>
                                                <span>{{ $file['original_name'] ?? 'File' }}</span>
                                                <span class="text-gray-400">({{ number_format($file['file_size'] / 1024, 1) }} KB)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Note: Uploading new files will replace existing files.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Form Footer -->
                        <div class="md:col-span-2 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                            <button wire:click="closeForm" type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submit">
                                <span wire:loading.remove wire:target="submit">
                                    {{ $editingId ? 'Update Application' : 'Submit Application' }}
                                </span>
                                <span wire:loading wire:target="submit" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ $editingId ? 'Updating...' : 'Submitting...' }}
                                </span>
                            </button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Application Details Modal -->
    @if ($showViewModal && $viewingApplication)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
             wire:click="closeViewModal"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Application #{{ $viewingApplication->applicationID }} - Details
                        </h3>
                        <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">✖</button>
                    </div>
                    <div class="mt-2">
                        <span class="text-sm text-blue-600 bg-blue-50 px-2 py-1 rounded">
                            📖 Read-Only View
                        </span>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Company Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Company Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Company Name:</strong> {{ $viewingApplication->companyName }}</p>
                                <p><strong>Email:</strong> {{ $viewingApplication->companyEmail }}</p>
                                <p><strong>Phone:</strong> {{ $viewingApplication->companyNumber }}</p>
                                <p><strong>Address:</strong> {{ $viewingApplication->company_full_address }}</p>
                                @if($viewingApplication->has_geocoding)
                                    <p><strong>Coordinates:</strong> {{ $viewingApplication->companyLatitude }}, {{ $viewingApplication->companyLongitude }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Industry Supervisor Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Industry Supervisor Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Name:</strong> {{ $viewingApplication->industrySupervisorName ?? 'Not provided' }}</p>
                                <p><strong>Contact:</strong> {{ $viewingApplication->industrySupervisorContact ?? 'Not provided' }}</p>
                                <p><strong>Email:</strong> {{ $viewingApplication->industrySupervisorEmail ?? 'Not provided' }}</p>
                            </div>
                        </div>

                        <!-- Position Details -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Position Details</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Position:</strong> {{ $viewingApplication->position }}</p>
                                <p><strong>Method of Work:</strong> {{ $viewingApplication->methodOfWorkDisplay }}</p>
                                <p><strong>Allowance:</strong> {{ $viewingApplication->allowance ? 'RM ' . number_format($viewingApplication->allowance, 2) : 'Not specified' }}</p>
                                <p><strong>Duration:</strong> {{ $viewingApplication->startDate->format('M d, Y') }} - {{ $viewingApplication->endDate->format('M d, Y') }}</p>
                                <p><strong>Application Date:</strong> {{ $viewingApplication->applicationDate->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Approval Status -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Approval Status</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Committee:</span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->committeeStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Coordinator:</span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->coordinatorStatus }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">Overall Status:</span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->overall_status === 'Approved' ? 'bg-green-100 text-green-800' : ($viewingApplication->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $viewingApplication->overall_status }}
                                    </span>
                                </div>
                                @if($viewingApplication->studentAcceptance)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">Your Response:</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $viewingApplication->studentAcceptance === 'Accepted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $viewingApplication->studentAcceptance }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Reviewer Information -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Reviewers</h4>
                            <div class="space-y-2 text-sm">
                                @if($viewingApplication->committee)
                                    <p><strong>Committee Member:</strong> {{ $viewingApplication->committee->user->name }}</p>
                                @endif
                                @if($viewingApplication->coordinator)
                                    <p><strong>Coordinator:</strong> {{ $viewingApplication->coordinator->user->name }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Job Scope -->
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-3">Job Scope</h4>
                            <div class="bg-gray-50 p-3 rounded border text-sm">
                                {{ $viewingApplication->jobscope }}
                            </div>
                        </div>

                        <!-- Files -->
                        @if($viewingApplication->files->count() > 0)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Submitted Files</h4>
                                <div class="space-y-2">
                                    @foreach($viewingApplication->files as $file)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded border">
                                            <div class="flex items-center space-x-3">
                                                <span>📄</span>
                                                <div>
                                                    <p class="text-sm font-medium">{{ $file->original_name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $file->mime_type }} • {{ number_format($file->file_size / 1024, 1) }} KB</p>
                                                </div>
                                            </div>
                                            <a href="{{ $file->url }}" target="_blank"
                                               class="text-blue-600 hover:text-blue-900 text-sm">
                                                View File
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Remarks -->
                        @if($viewingApplication->remarks)
                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-gray-900 mb-3">Lecturer Remarks</h4>
                                <div class="bg-blue-50 p-3 rounded border text-sm">
                                    {{ $viewingApplication->remarks }}
                                </div>
                            </div>
                        @endif

                        <!-- Information Notice -->
                        <div class="md:col-span-2">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <div class="flex items-start space-x-2">
                                    <span class="text-yellow-600">ℹ️</span>
                                    <div class="text-sm text-yellow-800">
                                        <p class="font-medium mb-1">Editing Restrictions:</p>
                                        @if($viewingApplication->committeeStatus !== 'Pending' || $viewingApplication->coordinatorStatus !== 'Pending')
                                            <p>This application cannot be edited because it has been reviewed by committee or coordinator. You can only edit applications that are still pending review by both parties.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button wire:click="closeViewModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Change Request Form Modal -->
    @if ($showChangeRequestForm)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
             wire:click="closeChangeRequestForm"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Request Application Change
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Application #{{ $changeRequestApplicationID }} - {{ $selectedApplicationForChange?->companyName ?? '' }}
                    </p>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <form wire:submit.prevent="submitChangeRequest">
                        <!-- Current Application Info -->
                        @if($selectedApplicationForChange)
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Current Application Details</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><strong>Company:</strong> {{ $selectedApplicationForChange->companyName }}</div>
                                    <div><strong>Position:</strong> {{ $selectedApplicationForChange->position }}</div>
                                    <div><strong>Method:</strong> {{ $selectedApplicationForChange->methodOfWork }}</div>
                                    <div><strong>Duration:</strong> {{ $selectedApplicationForChange->startDate->format('M d, Y') }} - {{ $selectedApplicationForChange->endDate->format('M d, Y') }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Reason for Change -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Change Request *
                            </label>
                            <textarea wire:model="changeRequestReason" rows="4"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm @error('changeRequestReason') border-red-500 @enderror"
                                placeholder="Please provide a detailed justification for requesting changes to your approved application. Explain what changes you need and why they are necessary..."></textarea>
                            @error('changeRequestReason')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Minimum 20 characters, maximum 1000 characters</p>
                        </div>

                        <!-- Supporting Documents -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supporting Documents</label>
                            <input type="file" wire:model="changeRequestFiles" multiple
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm @error('changeRequestFiles.*') border-red-500 @enderror"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <p class="text-xs text-gray-500 mt-1">Upload documents supporting your change request (PDF, DOC, DOCX, JPG, PNG). Max 10MB each.</p>
                            @error('changeRequestFiles.*')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <!-- File upload progress -->
                            @if ($changeRequestFiles)
                                <div class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Files to upload:</p>
                                    <div class="space-y-1">
                                        @foreach ($changeRequestFiles as $file)
                                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                                <span>📄</span>
                                                <span>{{ $file->getClientOriginalName() }}</span>
                                                <span class="text-gray-400">({{ number_format($file->getSize() / 1024, 1) }} KB)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Important Notice -->
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <span class="text-yellow-600 text-lg mr-2">⚠️</span>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-medium mb-1">Important Notice:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Change requests require approval from both committee and coordinator</li>
                                        <li>If approved, you will need to submit a new placement application</li>
                                        <li>Your current application will remain active until the new one is approved</li>
                                        <li>Approval is not guaranteed - provide strong justification</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                    <button wire:click="closeChangeRequestForm" type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="submitChangeRequest" type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled" wire:target="submitChangeRequest">
                        <span wire:loading.remove wire:target="submitChangeRequest">
                            Submit Change Request
                        </span>
                        <span wire:loading wire:target="submitChangeRequest" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Change Requests Modal -->
    @if ($viewingChangeRequests && $selectedApplicationForChange)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50;"
             wire:click="closeChangeRequestsView"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 51; max-height: 90vh; overflow-y: auto;">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Change Request History
                            </h3>
                            <p class="text-sm text-gray-600">
                                Application #{{ $selectedApplicationForChange->applicationID }} - {{ $selectedApplicationForChange->companyName }}
                            </p>
                        </div>
                        <button wire:click="closeChangeRequestsView" class="text-gray-400 hover:text-gray-600">✖</button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    @if($selectedApplicationForChange->changeRequests->count() > 0)
                        <div class="space-y-4">
                            @foreach($selectedApplicationForChange->changeRequests as $changeRequest)
                                <div class="border rounded-lg p-4 {{ $changeRequest->overall_status === 'Approved' ? 'bg-green-50 border-green-200' : ($changeRequest->overall_status === 'Rejected' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Change Request #{{ $changeRequest->justificationID }}</h4>
                                            <p class="text-sm text-gray-600">Submitted: {{ $changeRequest->requestDate->format('F d, Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $changeRequest->overall_status === 'Approved' ? 'bg-green-100 text-green-800' : ($changeRequest->overall_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $changeRequest->overall_status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Reason -->
                                    <div class="mb-3">
                                        <h5 class="font-medium text-gray-900 mb-1">Reason:</h5>
                                        <p class="text-sm text-gray-700">{{ $changeRequest->reason }}</p>
                                    </div>

                                    <!-- Status Details -->
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Committee:</span>
                                            <span class="ml-2 inline-flex px-2 py-1 text-xs rounded-full {{ $changeRequest->committeeStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($changeRequest->committeeStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $changeRequest->committeeStatus }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Coordinator:</span>
                                            <span class="ml-2 inline-flex px-2 py-1 text-xs rounded-full {{ $changeRequest->coordinatorStatus === 'Approved' ? 'bg-green-100 text-green-800' : ($changeRequest->coordinatorStatus === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $changeRequest->coordinatorStatus }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Files -->
                                    @if($changeRequest->files->count() > 0)
                                        <div class="mb-3">
                                            <h5 class="font-medium text-gray-900 mb-1">Supporting Documents:</h5>
                                            <div class="space-y-1">
                                                @foreach($changeRequest->files as $file)
                                                    <div class="flex items-center space-x-2 text-sm">
                                                        <span>📄</span>
                                                        <a href="{{ $file->url }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                            {{ $file->original_name }}
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Remarks -->
                                    @if($changeRequest->remarks)
                                        <div class="mb-3">
                                            <h5 class="font-medium text-gray-900 mb-1">Reviewer Remarks:</h5>
                                            <div class="bg-blue-50 p-3 rounded border text-sm">
                                                {{ $changeRequest->remarks }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Decision Date -->
                                    @if($changeRequest->decisionDate)
                                        <div class="text-sm text-gray-600">
                                            Decision Date: {{ $changeRequest->decisionDate->format('F d, Y') }}
                                        </div>
                                    @endif

                                    <!-- New Application Notice -->
                                    @if($changeRequest->overall_status === 'Approved')
                                        <div class="mt-3 p-3 bg-green-100 border border-green-300 rounded">
                                            <div class="flex items-center">
                                                <span class="text-green-600 mr-2">✅</span>
                                                <div class="text-sm text-green-800">
                                                    <p class="font-medium">Change Request Approved!</p>
                                                    <p>You can now submit a new placement application with your requested changes.</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No change requests found for this application.</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button wire:click="closeChangeRequestsView"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

