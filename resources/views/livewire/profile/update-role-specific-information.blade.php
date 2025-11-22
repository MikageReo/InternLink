<?php

use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

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
    public string $industrySupervisorName = '';
    public $profilePhoto;
    public ?string $currentProfilePhoto = null;

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
    public array $preferredCoursework = [];
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

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if ($user->isStudent() && $user->student) {
            $student = $user->student->load('academicAdvisor.user');
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
            $this->industrySupervisorName = $student->industrySupervisorName ?? '';
            $this->currentProfilePhoto = $student->profilePhoto;

            // Get academic advisor name
            if ($student->academicAdvisor && $student->academicAdvisor->user) {
                $this->academicAdvisorName = $student->academicAdvisor->user->name;
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
            // Parse preferred coursework from comma-separated string to array
            $this->preferredCoursework = $lecturer->preferred_coursework ? array_map('trim', explode(',', $lecturer->preferred_coursework)) : [];
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
        $this->profilePhoto = null;

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
        $this->dispatch('role-profile-updated');
    }

    /**
     * Update student profile information.
     */
    private function updateStudentProfile(User $user): void
    {
        $validated = $this->validate([
            'studentEmail' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'studentAddress' => ['nullable', 'string', 'max:500'],
            'studentCity' => ['nullable', 'string', 'max:100'],
            'studentPostcode' => ['nullable', 'string', 'max:20'],
            'studentState' => ['nullable', 'string', 'max:100'],
            'studentCountry' => ['nullable', 'string', 'max:100'],
            'industrySupervisorName' => ['nullable', 'string', 'max:255'],
            'profilePhoto' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $student = $user->student;
        if (!$student) {
            return;
        }

        // Update user email
        $user->email = $validated['studentEmail'];
        $user->save();

        // Handle profile photo upload
        if ($this->profilePhoto) {
            // Delete old photo if exists
            if ($student->profilePhoto) {
                Storage::disk('public')->delete($student->profilePhoto);
            }

            // Store new photo
            $photoPath = $this->profilePhoto->store('profile-photos', 'public');
            $student->profilePhoto = $photoPath;
            $this->currentProfilePhoto = $photoPath;
        }

        // Update student address fields
        $student->phone = $validated['phone'];
        $student->address = $validated['studentAddress'];
        $student->city = $validated['studentCity'];
        $student->postcode = $validated['studentPostcode'];
        $student->state = $validated['studentState'];
        $student->country = $validated['studentCountry'];
        $student->industrySupervisorName = $validated['industrySupervisorName'];

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

        // Reset the file input
        $this->profilePhoto = null;
    }

    /**
     * Update lecturer profile information.
     */
    private function updateLecturerProfile(User $user): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'lecturerAddress' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'preferredCoursework' => ['nullable', 'array'],
            'preferredCoursework.*' => ['string', 'max:100'],
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

        // Update preferred coursework (store as comma-separated string)
        $lecturer->preferred_coursework = !empty($validated['preferredCoursework']) ? implode(', ', $validated['preferredCoursework']) : null;

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

    /**
     * Remove the current profile photo.
     */
    public function removeProfilePhoto(): void
    {
        $user = Auth::user();

        if ($user->isStudent() && $user->student && $user->student->profilePhoto) {
            // Delete the file
            Storage::disk('public')->delete($user->student->profilePhoto);

            // Update database
            $user->student->profilePhoto = null;
            $user->student->save();

            // Update component state
            $this->currentProfilePhoto = null;

            $this->dispatch('role-profile-updated');
        }
    }
}; ?>

<section>
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
            @endphp

            <!-- Profile Header -->
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <!-- Avatar with Initials or Photo -->
                    <div
                        class="w-16 h-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-white text-xl font-semibold overflow-hidden">
                        @if ($currentProfilePhoto)
                            <img src="{{ asset('storage/' . $currentProfilePhoto) }}" alt="Profile Photo"
                                class="w-full h-full object-cover"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-white text-xl font-semibold"
                                style="display: none;">
                                {{ $initials }}
                            </div>
                        @else
                            {{ $initials }}
                        @endif
                    </div>
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
                </div>
                @if (!$editMode)
                    <button type="button" wire:click="enableEditMode"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        {{ __('Edit Profile') }}
                    </button>
                @endif
            </div>

            @if ($editMode)
                <!-- Profile Photo Upload Section -->
                <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <x-input-label for="profilePhoto" :value="__('Profile Photo')" class="text-base font-medium mb-2" />
                    <input wire:model="profilePhoto" id="profilePhoto" name="profilePhoto" type="file"
                        accept="image/*"
                        class="block w-full max-w-md text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 focus:outline-none">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('PNG, JPG or JPEG (MAX. 2MB)') }}
                    </p>
                    @if ($currentProfilePhoto)
                        <button type="button" wire:click="removeProfilePhoto"
                            class="mt-2 px-3 py-1 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200">
                            {{ __('Remove Photo') }}
                        </button>
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get('profilePhoto')" />
                    @if ($profilePhoto)
                        <div class="mt-2">
                            <p class="text-sm text-green-600 dark:text-green-400">
                                {{ __('New photo selected: ') . $profilePhoto->getClientOriginalName() }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Contact Details Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Contact Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Email:</span>
                        @if ($editMode)
                            <x-text-input wire:model="studentEmail" id="studentEmail" name="studentEmail" type="email"
                                class="mt-1 block w-full" placeholder="your.email@example.com" />
                            <x-input-error class="mt-2" :messages="$errors->get('studentEmail')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $studentEmail }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Phone:</span>
                        @if ($editMode)
                            <x-text-input wire:model="phone" id="phone" name="phone" type="text"
                                class="mt-1 block w-full" placeholder="e.g., +60123456789" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $phone ?: 'Not provided' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">State:</span>
                        @if ($editMode)
                            <x-text-input wire:model="studentState" id="studentState" name="studentState" type="text"
                                class="mt-1 block w-full" placeholder="e.g., Pahang" />
                            <x-input-error class="mt-2" :messages="$errors->get('studentState')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $studentState ?: 'Not provided' }}</span>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Address:</span>
                        @if ($editMode)
                            <x-text-input wire:model="studentAddress" id="studentAddress" name="studentAddress"
                                type="text" class="mt-1 block w-full" placeholder="Street address" />
                            <x-input-error class="mt-2" :messages="$errors->get('studentAddress')" />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <x-input-label for="studentCity" :value="__('City')" />
                                    <x-text-input wire:model="studentCity" id="studentCity" name="studentCity"
                                        type="text" class="mt-1 block w-full" placeholder="City" />
                                    <x-input-error class="mt-2" :messages="$errors->get('studentCity')" />
                                </div>
                                <div>
                                    <x-input-label for="studentPostcode" :value="__('Postcode')" />
                                    <x-text-input wire:model="studentPostcode" id="studentPostcode"
                                        name="studentPostcode" type="text" class="mt-1 block w-full"
                                        placeholder="Postcode" />
                                    <x-input-error class="mt-2" :messages="$errors->get('studentPostcode')" />
                                </div>
                                <div>
                                    <x-input-label for="studentCountry" :value="__('Country')" />
                                    <x-text-input wire:model="studentCountry" id="studentCountry" name="studentCountry"
                                        type="text" class="mt-1 block w-full" placeholder="Country" />
                                    <x-input-error class="mt-2" :messages="$errors->get('studentCountry')" />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <span class="font-semibold">🗺️ Note:</span> Your address will be automatically geocoded
                                to update your location coordinates when you save.
                            </p>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $fullAddress }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Academic Data Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Academic Data</h3>
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
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Industry
                            Supervisor:</span>
                        @if ($editMode)
                            <x-text-input wire:model="industrySupervisorName" id="industrySupervisorName"
                                name="industrySupervisorName" type="text" class="mt-1 block w-full"
                                placeholder="e.g., John Doe" />
                            <x-input-error class="mt-2" :messages="$errors->get('industrySupervisorName')" />
                        @else
                            <span
                                class="text-gray-600 dark:text-gray-400">{{ $industrySupervisorName ?: 'Not assigned' }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- System Information Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">System Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                // Parse preferred coursework for display
                $displayCoursework = !empty($preferredCoursework) ? $preferredCoursework : [];
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
                                class="mt-1 block w-full" placeholder="your.email@example.com" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $email }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">State:</span>
                        @if ($editMode)
                            <x-text-input wire:model="state" id="state" name="state" type="text"
                                class="mt-1 block w-full" placeholder="e.g., Pahang" />
                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $state ?: 'Not provided' }}</span>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Address:</span>
                        @if ($editMode)
                            <x-text-input wire:model="lecturerAddress" id="lecturerAddress" name="lecturerAddress"
                                type="text" class="mt-1 block w-full" placeholder="Street address" />
                            <x-input-error class="mt-2" :messages="$errors->get('lecturerAddress')" />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <x-input-label for="city" :value="__('City')" />
                                    <x-text-input wire:model="city" id="city" name="city" type="text"
                                        class="mt-1 block w-full" placeholder="City" />
                                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                                </div>
                                <div>
                                    <x-input-label for="postcode" :value="__('Postcode')" />
                                    <x-text-input wire:model="postcode" id="postcode" name="postcode"
                                        type="text" class="mt-1 block w-full" placeholder="Postcode" />
                                    <x-input-error class="mt-2" :messages="$errors->get('postcode')" />
                                </div>
                                <div>
                                    <x-input-label for="country" :value="__('Country')" />
                                    <x-text-input wire:model="country" id="country" name="country" type="text"
                                        class="mt-1 block w-full" placeholder="Country" />
                                    <x-input-error class="mt-2" :messages="$errors->get('country')" />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <span class="font-semibold">🗺️ Note:</span> Your address will be automatically
                                geocoded to update your location coordinates when you save.
                            </p>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $fullAddress }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Academic Data Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Academic Data</h3>
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
                </div>
            </div>

            <!-- System Information Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">System Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Preferred
                            Coursework:</span>
                        @if ($editMode)
                            <div class="mt-1">
                                <select wire:model="preferredCoursework" multiple
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm min-h-[120px]"
                                    size="6">
                                    <option value="Software Engineering">Software Engineering</option>
                                    <option value="Computer Systems & Networking">Computer Systems & Networking
                                    </option>
                                    <option value="Graphics & Multimedia Technology">Graphics & Multimedia Technology
                                    </option>
                                    <option value="Cyber Security">Cyber Security</option>
                                    <option value="Data Science & Analytics">Data Science & Analytics</option>
                                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                                    <option value="Information Systems">Information Systems</option>
                                    <option value="Database Systems">Database Systems</option>
                                    <option value="Cloud & Distributed Computing">Cloud & Distributed Computing
                                    </option>
                                    <option value="General Computing">General Computing</option>

                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Hold Ctrl (Windows) or Cmd (Mac) to select multiple options
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('preferredCoursework')" />
                            </div>
                        @else
                            @if (!empty($displayCoursework))
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach ($displayCoursework as $coursework)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ trim($coursework) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">Not specified</span>
                            @endif
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
                <x-primary-button>{{ __('Save Changes') }}</x-primary-button>

                <button type="button" wire:click="cancelEdit"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    {{ __('Cancel') }}
                </button>

                <x-action-message class="me-3" on="role-profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        @endif
    </form>
</section>
