<?php

use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;
new class extends Component {

    // Student fields
    public string $studentEmail = '';
    public string $phone = '';
    public string $studentAddress = '';
    public string $studentCity = '';
    public string $studentPostcode = '';
    public string $studentState = '';
    public string $studentCountry = '';
    public string $nationality = '';
    public string $program = '';
    public string $semester = '';
    public string $year = '';
    public string $status = '';
    public string $academicAdvisorID = '';

    // Industry supervisor info from accepted placement application (read-only)
    public ?string $industrySupervisorName = null;
    public ?string $industrySupervisorContact = null;
    public ?string $industrySupervisorEmail = null;

    // Supervisor faculty info from supervisor assignment (read-only)
    public ?string $supervisorFacultyName = null;
    public ?string $supervisorFacultyEmail = null;
    public ?string $supervisorFacultyID = null;

    // Lecturer fields
    public string $email = '';
    public string $lecturerAddress = '';
    public string $city = '';
    public string $postcode = '';
    public string $country = '';
    public string $staffGrade = '';
    public string $role = '';
    public string $position = '';
    public string $state = '';
    public string $researchGroup = '';
    public string $department = '';
    public string $lecturerSemester = '';
    public string $lecturerYear = '';
    public string $supervisorQuota = '';
    public string $lecturerProgram = '';
    public string $travelPreference = 'local';
    public bool $isAcademicAdvisor = false;
    public bool $isSupervisorFaculty = false;
    public bool $isCommittee = false;
    public bool $isCoordinator = false;
    public bool $isAdmin = false;

    // Geocoding service (lazy-loaded)
    protected ?GeocodingService $geocodingService = null;

    // Academic advisor name for display
    public string $academicAdvisorName = '';

    // Edit mode state
    public bool $editMode = false;

    // Success message state
    public bool $showSuccessMessage = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if ($user->isStudent() && $user->student) {
            $student = $user->student->load(['academicAdvisor.user', 'acceptedPlacementApplication', 'supervisorAssignment.supervisor.user']);
            $this->studentEmail = $user->email ?? '';
            $this->phone = $student->phone ?? '';
            $this->studentAddress = $student->address ?? '';
            $this->studentCity = $student->city ?? '';
            $this->studentPostcode = $student->postcode ?? '';
            $this->studentState = $student->state ?? '';
            $this->studentCountry = $student->country ?? '';
            $this->nationality = $student->nationality ?? '';
            $this->program = $student->program ?? '';
            $this->semester = $student->semester ?? '';
            $this->year = $student->year ?? '';
            $this->status = $student->status ?? '';
            $this->academicAdvisorID = $student->academicAdvisorID ?? '';

            // Get academic advisor name
            if ($student->academicAdvisor && $student->academicAdvisor->user) {
                $this->academicAdvisorName = $student->academicAdvisor->user->name;
            }

            // Get industry supervisor info from accepted placement application
            if ($student->acceptedPlacementApplication) {
                $this->industrySupervisorName = $student->acceptedPlacementApplication->industrySupervisorName ?? null;
                $this->industrySupervisorContact = $student->acceptedPlacementApplication->industrySupervisorContact ?? null;
                $this->industrySupervisorEmail = $student->acceptedPlacementApplication->industrySupervisorEmail ?? null;
            }

            // Get supervisor faculty info from supervisor assignment
            if ($student->supervisorAssignment && $student->supervisorAssignment->supervisor) {
                $supervisor = $student->supervisorAssignment->supervisor;
                $this->supervisorFacultyID = $supervisor->lecturerID ?? null;
                if ($supervisor->user) {
                    $this->supervisorFacultyName = $supervisor->user->name ?? null;
                    $this->supervisorFacultyEmail = $supervisor->user->email ?? null;
                }
            }
        } elseif ($user->isLecturer() && $user->lecturer) {
            $lecturer = $user->lecturer;
            $this->email = $user->email ?? '';
            $this->lecturerAddress = $lecturer->address ?? '';
            $this->city = $lecturer->city ?? '';
            $this->postcode = $lecturer->postcode ?? '';
            $this->country = $lecturer->country ?? '';
            $this->staffGrade = $lecturer->staffGrade ?? '';
            $this->role = $lecturer->role ?? '';
            $this->position = $lecturer->position ?? '';
            $this->state = $lecturer->state ?? '';
            $this->researchGroup = $lecturer->researchGroup ?? '';
            $this->department = $lecturer->department ?? '';
            $this->lecturerSemester = $lecturer->semester ?? '';
            $this->lecturerYear = $lecturer->year ?? '';
            $this->supervisorQuota = $lecturer->supervisor_quota ?? '';
            $this->lecturerProgram = $lecturer->program ?? '';
            $this->travelPreference = $lecturer->travel_preference ?? 'local';
            $this->isAcademicAdvisor = $lecturer->isAcademicAdvisor ?? false;
            $this->isSupervisorFaculty = $lecturer->isSupervisorFaculty ?? false;
            $this->isCommittee = $lecturer->isCommittee ?? false;
            $this->isCoordinator = $lecturer->isCoordinator ?? false;
            $this->isAdmin = $lecturer->isAdmin ?? false;
        }

        // Initialize geocoding service
        $this->geocodingService = app(GeocodingService::class);
    }

    /**
     * Enable edit mode
     */
    public function enableEditMode(): void
    {
        $this->editMode = true;
    }

    /**
     * Cancel edit mode and revert changes
     */
    public function cancelEdit(): void
    {
        $this->editMode = false;
        $this->showSuccessMessage = false;

        // Reset to original values
        $this->mount();
    }

    /**
     * Update the role-specific profile information.
     */
    public function updateRoleSpecificInformation(): void
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            $this->updateStudentProfile($user);
        } elseif ($user->isLecturer()) {
            $this->updateLecturerProfile($user);
        }

        $this->editMode = false;
        $this->showSuccessMessage = true;
        $this->dispatch('role-profile-updated');

        // Hide success message after 3 seconds
        $this->dispatch('hide-success-message');
    }

    /**
     * Update student profile information.
     */
    private function updateStudentProfile(User $user): void
    {
        $validated = $this->validate([
            'studentEmail' => ['required', 'email', 'max:30', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'studentAddress' => ['required', 'string', 'max:500'],
            'studentCity' => ['required', 'string', 'max:30'],
            'studentPostcode' => ['required', 'string', 'max:10', 'regex:/^[0-9]+$/'],
            'studentState' => ['required', 'string', 'max:20'],
            'studentCountry' => ['required', 'string', 'max:100'],
        ]);

        $student = $user->student;
        if (!$student) {
            return;
        }

        // Update user email
        $user->email = $validated['studentEmail'];
        $user->save();

        // Update student address fields
        $student->phone = $validated['phone'];
        $student->address = $validated['studentAddress'];
        $student->city = $validated['studentCity'];
        $student->postcode = $validated['studentPostcode'];
        $student->state = $validated['studentState'];
        $student->country = $validated['studentCountry'];

        // Geocode the address if it's provided
        if (!empty($validated['studentAddress']) || !empty($validated['studentCity']) || !empty($validated['studentState'])) {
            if (!$this->geocodingService) {
                $this->geocodingService = app(GeocodingService::class);
            }
            $geocodeResult = $this->geocodingService->geocodeStructuredAddress([
                'street' => $validated['studentAddress'] ?? '',
                'city' => $validated['studentCity'] ?? '',
                'postcode' => $validated['studentPostcode'] ?? '',
                'state' => $validated['studentState'] ?? '',
                'country' => $validated['studentCountry'] ?? '',
            ]);

            if ($geocodeResult) {
                $student->latitude = $geocodeResult['latitude'];
                $student->longitude = $geocodeResult['longitude'];
            } else {
                // If geocoding fails, set coordinates to null
                $student->latitude = null;
                $student->longitude = null;
            }
        }

        $student->save();
    }

    /**
     * Update lecturer profile information.
     */
    private function updateLecturerProfile(User $user): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email', 'max:30', 'unique:users,email,' . $user->id],
            'lecturerAddress' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:30'],
            'postcode' => ['required', 'string', 'max:10', 'regex:/^[0-9]+$/'],
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:20'],
            'travelPreference' => ['required', 'in:local,nationwide'],
        ]);

        $lecturer = $user->lecturer;
        if (!$lecturer) {
            return;
        }

        // Update user email
        $user->email = $validated['email'];
        $user->save();

        // Update lecturer address fields
        $lecturer->address = $validated['lecturerAddress'];
        $lecturer->city = $validated['city'];
        $lecturer->postcode = $validated['postcode'];
        $lecturer->country = $validated['country'];
        $lecturer->state = $validated['state'];

        // Program is not editable - it's set during registration only

        // Update travel preference
        $lecturer->travel_preference = $validated['travelPreference'];

        // Geocode the address if it's provided
        if (!empty($validated['lecturerAddress']) || !empty($validated['city']) || !empty($validated['state'])) {
            if (!$this->geocodingService) {
                $this->geocodingService = app(GeocodingService::class);
            }
            $geocodeResult = $this->geocodingService->geocodeStructuredAddress([
                'street' => $validated['lecturerAddress'] ?? '',
                'city' => $validated['city'] ?? '',
                'postcode' => $validated['postcode'] ?? '',
                'state' => $validated['state'] ?? '',
                'country' => $validated['country'] ?? '',
            ]);

            if ($geocodeResult) {
                $lecturer->latitude = $geocodeResult['latitude'];
                $lecturer->longitude = $geocodeResult['longitude'];
            } else {
                // If geocoding fails, set coordinates to null
                $lecturer->latitude = null;
                $lecturer->longitude = null;
            }
        }

        $lecturer->save();
    }

}; ?>

<section>
    @if ($showSuccessMessage)
        <div x-data="{ show: @entangle('showSuccessMessage') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             x-init="setTimeout(() => { show = false }, 3000)"
             class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600 dark:text-green-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ __('Profile updated successfully!') }}</span>
        </div>
    @endif

    <form wire:submit="updateRoleSpecificInformation" class="space-y-6">
        @if (auth()->user()->isStudent())
            @php
                $user = auth()->user();
                $student = $user->student;
                $name = $user->name;
                $nameParts = array_filter(explode(' ', trim($name)));
                if (count($nameParts) >= 2) {
                    $initials = strtoupper(
                        substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1),
                    );
                } else {
                    $initials = strtoupper(substr($name, 0, min(2, strlen($name))));
                }
                // Build full address from component properties
                $addressParts = array_filter([
                    $studentAddress,
                    $studentCity,
                    $studentPostcode,
                    $studentState,
                    $studentCountry,
                ]);
                $fullAddress = !empty($addressParts)
                    ? implode(', ', $addressParts)
                    : ($student && $student->full_address
                        ? $student->full_address
                        : 'Not provided');
                $countries = [
                    'Malaysia','Afghanistan','Albania','Algeria','Argentina','Australia','Austria',
                    'Bangladesh','Belgium','Brazil','Brunei','Cambodia','Canada','Chile','China',
                    'Colombia','Denmark','Egypt','Finland','France','Germany','Greece','Hong Kong',
                    'Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy','Japan',
                    'Jordan','Kenya','Kuwait','Laos','Lebanon','Libya','Mexico','Morocco','Myanmar',
                    'Nepal','Netherlands','New Zealand','Nigeria','Norway','Oman','Pakistan',
                    'Palestine','Philippines','Poland','Portugal','Qatar','Russia','Saudi Arabia',
                    'Singapore','South Africa','South Korea','Spain','Sri Lanka','Sudan','Sweden',
                    'Switzerland','Syria','Taiwan','Thailand','Turkey','United Arab Emirates',
                    'United Kingdom','United States','Vietnam','Yemen'
                ];
            @endphp

            <!-- Profile Header -->
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $name }}
                    </h2>
                    @if ($program)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $program }}
                        </p>
                    @endif
                </div>
                @if (!$editMode)
                    <button type="button" wire:click="enableEditMode"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        {{ __('Edit Profile') }}
                    </button>
                @endif
            </div>

            <!-- Contact Details Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Contact Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Email:</span>
                        @if ($editMode)
                            <x-text-input wire:model="studentEmail" id="studentEmail" name="studentEmail" type="email"
                                class="mt-1 block w-full" placeholder="your.email@example.com" required maxlength="30" />
                            <x-input-error class="mt-2" :messages="$errors->get('studentEmail')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $studentEmail }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Phone:</span>
                        @if ($editMode)
                            <x-text-input wire:model="phone" id="phone" name="phone" type="text"
                                class="mt-1 block w-full" placeholder="e.g., 0123456789" required maxlength="20"
                                inputmode="numeric" pattern="[0-9]*"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'')" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $phone ?: 'Not provided' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">State:</span>
                        @if ($editMode)
                            <x-text-input wire:model="studentState" id="studentState" name="studentState" type="text"
                                class="mt-1 block w-full" placeholder="e.g., Pahang" required maxlength="20" />
                            <x-input-error class="mt-2" :messages="$errors->get('studentState')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $studentState ?: 'Not provided' }}</span>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Address:</span>
                        @if ($editMode)
                            <x-text-input wire:model="studentAddress" id="studentAddress" name="studentAddress"
                                type="text" class="mt-1 block w-full" placeholder="Street address" required maxlength="255" />
                            <x-input-error class="mt-2" :messages="$errors->get('studentAddress')" />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <x-input-label for="studentCity" :value="__('City')" />
                                    <x-text-input wire:model="studentCity" id="studentCity" name="studentCity"
                                        type="text" class="mt-1 block w-full" placeholder="City" required maxlength="30" />
                                    <x-input-error class="mt-2" :messages="$errors->get('studentCity')" />
                                </div>
                                <div>
                                    <x-input-label for="studentPostcode" :value="__('Postcode')" />
                                    <x-text-input wire:model="studentPostcode" id="studentPostcode"
                                        name="studentPostcode" type="text" class="mt-1 block w-full"
                                        placeholder="Postcode" required maxlength="10" inputmode="numeric" pattern="[0-9]*"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('studentPostcode')" />
                                </div>
                                <div>
                                    <x-input-label for="studentCountry" :value="__('Country')" />
                                    <select wire:model="studentCountry" id="studentCountry" name="studentCountry"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        required>
                                        <option value="">{{ __('Select Country') }}</option>
                                        @foreach ($countries as $countryName)
                                            <option value="{{ $countryName }}">{{ $countryName }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('studentCountry')" />
                                </div>
                            </div>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $fullAddress }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Academic Data Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Academic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Student ID:</span>
                        <span
                            class="text-gray-600 dark:text-gray-400">{{ $student ? $student->studentID : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Program:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $program ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Nationality:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $nationality ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Academic
                            Advisor:</span>
                        <span
                            class="text-gray-600 dark:text-gray-400">{{ $academicAdvisorName ?: 'Not Assigned' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Semester:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $semester ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Year:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $year ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Status:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $status ?: 'Not provided' }}</span>
                    </div>
                </div>
            </div>

        @elseif(auth()->user()->isLecturer())
            @php
                $user = auth()->user();
                $lecturer = $user->lecturer;
                $name = $user->name;
                $nameParts = array_filter(explode(' ', trim($name)));
                if (count($nameParts) >= 2) {
                    $initials = strtoupper(
                        substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1),
                    );
                } else {
                    $initials = strtoupper(substr($name, 0, min(2, strlen($name))));
                }
                $title = trim(($position ?: '') . ($position && $staffGrade ? ' | ' : '') . ($staffGrade ?: ''));
                // Build full address from component properties
                $addressParts = array_filter([$lecturerAddress, $city, $postcode, $state, $country]);
                $fullAddress = !empty($addressParts)
                    ? implode(', ', $addressParts)
                    : ($lecturer && $lecturer->full_address
                        ? $lecturer->full_address
                        : 'Not provided');
                // Get program display name
                $programNames = [
                    'BCS' => 'Bachelor of Computer Science (Software Engineering) with Honours',
                    'BCN' => 'Bachelor of Computer Science (Computer Systems & Networking) with Honours',
                    'BCM' => 'Bachelor of Computer Science (Multimedia Software) with Honours',
                    'BCY' => 'Bachelor of Computer Science (Cyber Security) with Honours',
                    'DRC' => 'Diploma in Computer Science'
                ];
                $displayProgram = null;
                if (!empty($this->lecturerProgram) && is_string($this->lecturerProgram) && isset($programNames[$this->lecturerProgram])) {
                    $displayProgram = $programNames[$this->lecturerProgram];
                } elseif (!empty($this->lecturerProgram)) {
                    $displayProgram = $this->lecturerProgram;
                }
                $countries = [
                    'Malaysia','Afghanistan','Albania','Algeria','Argentina','Australia','Austria',
                    'Bangladesh','Belgium','Brazil','Brunei','Cambodia','Canada','Chile','China',
                    'Colombia','Denmark','Egypt','Finland','France','Germany','Greece','Hong Kong',
                    'Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy','Japan',
                    'Jordan','Kenya','Kuwait','Laos','Lebanon','Libya','Mexico','Morocco','Myanmar',
                    'Nepal','Netherlands','New Zealand','Nigeria','Norway','Oman','Pakistan',
                    'Palestine','Philippines','Poland','Portugal','Qatar','Russia','Saudi Arabia',
                    'Singapore','South Africa','South Korea','Spain','Sri Lanka','Sudan','Sweden',
                    'Switzerland','Syria','Taiwan','Thailand','Turkey','United Arab Emirates',
                    'United Kingdom','United States','Vietnam','Yemen'
                ];
                $activeRoles = [];
                if ($isAcademicAdvisor) {
                    $activeRoles[] = 'Academic Advisor';
                }
                if ($isCoordinator) {
                    $activeRoles[] = 'Coordinator';
                }
                if ($isSupervisorFaculty) {
                    $activeRoles[] = 'Supervisor Faculty';
                }
                if ($isAdmin) {
                    $activeRoles[] = 'Admin';
                }
                if ($isCommittee) {
                    $activeRoles[] = 'Committee';
                }
            @endphp

            <!-- Profile Header -->
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <!-- Avatar with Initials -->
                    <div
                        class="w-16 h-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-white text-xl font-semibold">
                        {{ $initials }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $name }}
                        </h2>
                        @if ($title)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $title }}
                            </p>
                        @endif
                    </div>
                </div>
                @if (!$editMode)
                    <button type="button" wire:click="enableEditMode"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        {{ __('Edit Profile') }}
                    </button>
                @endif
            </div>

            <!-- Contact Details Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Contact Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Email:</span>
                        @if ($editMode)
                            <x-text-input wire:model="email" id="email" name="email" type="email"
                                class="mt-1 block w-full" placeholder="your.email@example.com" required maxlength="30" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $email }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">State:</span>
                        @if ($editMode)
                            <x-text-input wire:model="state" id="state" name="state" type="text"
                                class="mt-1 block w-full" placeholder="e.g., Pahang" required maxlength="20" />
                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $state ?: 'Not provided' }}</span>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Address:</span>
                        @if ($editMode)
                            <x-text-input wire:model="lecturerAddress" id="lecturerAddress" name="lecturerAddress"
                                type="text" class="mt-1 block w-full" placeholder="Street address" required maxlength="255" />
                            <x-input-error class="mt-2" :messages="$errors->get('lecturerAddress')" />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <x-input-label for="city" :value="__('City')" />
                                    <x-text-input wire:model="city" id="city" name="city" type="text"
                                        class="mt-1 block w-full" placeholder="City" required maxlength="30" />
                                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                                </div>
                                <div>
                                    <x-input-label for="postcode" :value="__('Postcode')" />
                                    <x-text-input wire:model="postcode" id="postcode" name="postcode"
                                        type="text" class="mt-1 block w-full" placeholder="Postcode" required maxlength="10"
                                        inputmode="numeric" pattern="[0-9]*"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('postcode')" />
                                </div>
                                <div>
                                    <x-input-label for="country" :value="__('Country')" />
                                    <select wire:model="country" id="country" name="country"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        required>
                                        <option value="">{{ __('Select Country') }}</option>
                                        @foreach ($countries as $countryName)
                                            <option value="{{ $countryName }}">{{ $countryName }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('country')" />
                                </div>
                            </div>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $fullAddress }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Academic Data Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Academic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Lecturer ID:</span>
                        <span
                            class="text-gray-600 dark:text-gray-400">{{ $lecturer ? $lecturer->lecturerID : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Department:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $department ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Research Group:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $researchGroup ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Role:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $role ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Semester:</span>
                        <span
                            class="text-gray-600 dark:text-gray-400">{{ $lecturerSemester ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Year:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $lecturerYear ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Supervisor
                            Quota:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $supervisorQuota ?: 'Not provided' }}</span>
                    </div>
                </div>
            </div>

            <!-- Supervisor Preferences Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Supervisor Preferences</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Program:</span>
                        @if ($displayProgram)
                            <span class="text-gray-700 dark:text-gray-400">{{ $displayProgram }}</span>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">Not specified</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Travel
                            Preference:</span>
                        @if ($editMode)
                            <div class="mt-1">
                                <select wire:model="travelPreference"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="local">Local (within 50km)</option>
                                    <option value="nationwide">Nationwide (any distance)</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Select your preferred travel range for supervision visits
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('travelPreference')" />
                            </div>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ $travelPreference === 'local' ? 'Local (within 50km)' : 'Nationwide (any distance)' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Active Roles Section -->
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Active Roles</h3>
                @if (count($activeRoles) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeRoles as $roleName)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500 text-white">
                                {{ $roleName }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No active roles assigned.</p>
                @endif
            </div>
        @endif

        @if ($editMode)
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-primary-button wire:loading.attr="disabled" wire:target="updateRoleSpecificInformation">
                    <span wire:loading wire:target="updateRoleSpecificInformation" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>{{ __('Saving...') }}</span>
                    </span>
                    <span wire:loading.remove wire:target="updateRoleSpecificInformation">
                        {{ __('Save Changes') }}
                    </span>
                </x-primary-button>

                <button type="button" wire:click="cancelEdit"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    {{ __('CANCEL') }}
                </button>
            </div>
        @endif
    </form>
</section>
