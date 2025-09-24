<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    // Student fields
    public string $phone = '';
    public string $address = '';
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
    public string $staffGrade = '';
    public string $role = '';
    public string $position = '';
    public string $state = '';
    public string $researchGroup = '';
    public string $department = '';
    public string $lecturerSemester = '';
    public string $lecturerYear = '';
    public string $studentQuota = '';
    public bool $isAcademicAdvisor = false;
    public bool $isSupervisorFaculty = false;
    public bool $isCommittee = false;
    public bool $isCoordinator = false;
    public bool $isAdmin = false;

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
            $this->phone = $student->phone ?? '';
            $this->address = $student->address ?? '';
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
            $this->staffGrade = $lecturer->staffGrade ?? '';
            $this->role = $lecturer->role ?? '';
            $this->position = $lecturer->position ?? '';
            $this->state = $lecturer->state ?? '';
            $this->researchGroup = $lecturer->researchGroup ?? '';
            $this->department = $lecturer->department ?? '';
            $this->lecturerSemester = $lecturer->semester ?? '';
            $this->lecturerYear = $lecturer->year ?? '';
            $this->studentQuota = $lecturer->studentQuota ?? '';
            $this->isAcademicAdvisor = $lecturer->isAcademicAdvisor ?? false;
            $this->isSupervisorFaculty = $lecturer->isSupervisorFaculty ?? false;
            $this->isCommittee = $lecturer->isCommittee ?? false;
            $this->isCoordinator = $lecturer->isCoordinator ?? false;
            $this->isAdmin = $lecturer->isAdmin ?? false;
        }
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
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'industrySupervisorName' => ['nullable', 'string', 'max:255'],
            'profilePhoto' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $student = $user->student;
        if (!$student) {
            return;
        }

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

        // Only update editable fields
        $student->phone = $validated['phone'];
        $student->address = $validated['address'];
        $student->industrySupervisorName = $validated['industrySupervisorName'];
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
            'state' => ['nullable', 'string', 'max:100'],
        ]);

        $lecturer = $user->lecturer;
        if (!$lecturer) {
            return;
        }

        // Only update the state field
        $lecturer->state = $validated['state'];
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
    <header class="flex justify-between items-start">
        <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Profile Information') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __("View and update your profile information.") }}
            </p>
        </div>

        @if(!$editMode)
            <button type="button" wire:click="enableEditMode"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                {{ __('Edit Profile') }}
            </button>
        @endif
    </header>

    <form wire:submit="updateRoleSpecificInformation" class="mt-6 space-y-6">
        @if(auth()->user()->isStudent())
            <!-- Profile Photo Section - Centered and Small -->
            <div class="flex flex-col items-center pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex-shrink-0 mb-4">
                    @if($currentProfilePhoto)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $currentProfilePhoto) }}" alt="Profile Photo"
                                 class="w-24 h-24 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600 shadow-md"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 text-xs font-medium shadow-md" style="display: none;">
                                No Image
                            </div>
                        </div>
                    @else
                        <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 text-xs font-medium shadow-md">
                            No Photo
                        </div>
                    @endif
                </div>

                @if($editMode)
                    <div class="text-center">
                        <x-input-label for="profilePhoto" :value="__('Profile Photo')" class="text-base font-medium mb-2" />
                        <input wire:model="profilePhoto" id="profilePhoto" name="profilePhoto" type="file" accept="image/*"
                               class="block w-full max-w-xs mx-auto text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 focus:outline-none">

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('PNG, JPG or JPEG (MAX. 2MB)') }}
                        </p>

                        @if($currentProfilePhoto)
                            <button type="button" wire:click="removeProfilePhoto"
                                    class="mt-2 px-3 py-1 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200">
                                {{ __('Remove Photo') }}
                            </button>
                        @endif

                        <x-input-error class="mt-2" :messages="$errors->get('profilePhoto')" />

                        @if($profilePhoto)
                            <div class="mt-2">
                                <p class="text-sm text-green-600 dark:text-green-400">
                                    {{ __('New photo selected: ') . $profilePhoto->getClientOriginalName() }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Student Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student ID (Read-only) -->
                <div>
                    <x-input-label for="studentID" :value="__('Student ID')" />
                    <x-text-input id="studentID" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ auth()->user()->student->studentID ?? 'N/A' }}" readonly />
                </div>

                <!-- Name (Read-only) -->
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ auth()->user()->name }}" readonly />
                </div>

                <!-- Email (Read-only) -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ auth()->user()->email }}" readonly />
                </div>
                <!-- Phone Number -->
                <div>
                    <x-input-label for="phone" :value="__('Phone Number')" />
                    @if($editMode)
                        <x-text-input wire:model="phone" id="phone" name="phone" type="text" class="mt-1 block w-full" placeholder="e.g., +60123456789" />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    @else
                        <x-text-input id="phone" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                      value="{{ $phone ?: 'Not provided' }}" readonly />
                    @endif
                </div>

                <!-- Nationality (Read-only) -->
                <div>
                    <x-input-label for="nationality" :value="__('Nationality')" />
                    <x-text-input id="nationality" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $nationality ?: 'Not provided' }}" readonly />
                </div>

                <!-- Program (Read-only) -->
                <div>
                    <x-input-label for="program" :value="__('Program')" />
                    <x-text-input id="program" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $program ?: 'Not provided' }}" readonly />
                </div>

                <!-- Semester (Read-only) -->
                <div>
                    <x-input-label for="semester" :value="__('Semester')" />
                    <x-text-input id="semester" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $semester ?: 'Not provided' }}" readonly />
                </div>

                <!-- Year (Read-only) -->
                <div>
                    <x-input-label for="year" :value="__('Year')" />
                    <x-text-input id="year" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $year ?: 'Not provided' }}" readonly />
                </div>

                <!-- Status (Read-only) -->
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <x-text-input id="status" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $status ?: 'Not provided' }}" readonly />
                </div>

                <!-- Academic Advisor ID (Read-only) -->
                <div>
                    <x-input-label for="academicAdvisorID" :value="__('Academic Advisor ID')" />
                    <x-text-input id="academicAdvisorID" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $academicAdvisorID ?: 'Not Assigned' }}" readonly />
                </div>

                <!-- Academic Advisor Name (Read-only) -->
                <div>
                    <x-input-label for="academicAdvisorName" :value="__('Academic Advisor Name')" />
                    <x-text-input id="academicAdvisorName" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $academicAdvisorName ?: 'Not Assigned' }}" readonly />
                </div>

                <!-- Industry Supervisor Name -->
                <div>
                    <x-input-label for="industrySupervisorName" :value="__('Industry Supervisor Name')" />
                    @if($editMode)
                        <x-text-input wire:model="industrySupervisorName" id="industrySupervisorName" name="industrySupervisorName" type="text" class="mt-1 block w-full" placeholder="e.g., John Doe" />
                        <x-input-error class="mt-2" :messages="$errors->get('industrySupervisorName')" />
                    @else
                        <x-text-input id="industrySupervisorName" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                      value="{{ $industrySupervisorName ?: 'Not assigned' }}" readonly />
                    @endif
                </div>
            </div>

            <!-- Address (Full width) -->
            <div>
                <x-input-label for="address" :value="__('Address')" />
                @if($editMode)
                    <textarea wire:model="address" id="address" name="address" rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        placeholder="Enter your full address"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                @else
                    <textarea id="address" name="address" rows="3" readonly
                        class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-700 dark:text-gray-300 rounded-md shadow-sm">{{ $address ?: 'Not provided' }}</textarea>
                @endif
            </div>

        @elseif(auth()->user()->isLecturer())
            <!-- Lecturer Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Lecturer ID (Read-only) -->
                <div>
                    <x-input-label for="lecturerID" :value="__('Lecturer ID')" />
                    <x-text-input id="lecturerID" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ auth()->user()->lecturer->lecturerID ?? 'N/A' }}" readonly />
                </div>

                <!-- Name (Read-only) -->
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ auth()->user()->name }}" readonly />
                </div>

                <!-- Email (Read-only) -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ auth()->user()->email }}" readonly />
                </div>

                <!-- Staff Grade (Read-only) -->
                <div>
                    <x-input-label for="staffGrade" :value="__('Staff Grade')" />
                    <x-text-input id="staffGrade" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $staffGrade ?: 'Not provided' }}" readonly />
                </div>

                <!-- Role (Read-only) -->
                <div>
                    <x-input-label for="role" :value="__('Role')" />
                    <x-text-input id="role" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $role ?: 'Not provided' }}" readonly />
                </div>

                <!-- Position (Read-only) -->
                <div>
                    <x-input-label for="position" :value="__('Position')" />
                    <x-text-input id="position" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $position ?: 'Not provided' }}" readonly />
                </div>

                <!-- State -->
                <div>
                    <x-input-label for="state" :value="__('State')" />
                    @if($editMode)
                        <x-text-input wire:model="state" id="state" name="state" type="text" class="mt-1 block w-full" placeholder="e.g., Selangor" />
                        <x-input-error class="mt-2" :messages="$errors->get('state')" />
                    @else
                        <x-text-input id="state" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                      value="{{ $state ?: 'Not provided' }}" readonly />
                    @endif
                </div>

                <!-- Research Group (Read-only) -->
                <div>
                    <x-input-label for="researchGroup" :value="__('Research Group')" />
                    <x-text-input id="researchGroup" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $researchGroup ?: 'Not provided' }}" readonly />
                </div>

                <!-- Department (Read-only) -->
                <div>
                    <x-input-label for="department" :value="__('Department')" />
                    <x-text-input id="department" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $department ?: 'Not provided' }}" readonly />
                </div>

                <!-- Semester (Read-only) -->
                <div>
                    <x-input-label for="lecturerSemester" :value="__('Semester')" />
                    <x-text-input id="lecturerSemester" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $lecturerSemester ?: 'Not provided' }}" readonly />
                </div>

                <!-- Year (Read-only) -->
                <div>
                    <x-input-label for="lecturerYear" :value="__('Year')" />
                    <x-text-input id="lecturerYear" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $lecturerYear ?: 'Not provided' }}" readonly />
                </div>

                <!-- Student Quota (Read-only) -->
                <div>
                    <x-input-label for="studentQuota" :value="__('Student Quota')" />
                    <x-text-input id="studentQuota" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                  value="{{ $studentQuota ?: 'Not provided' }}" readonly />
                </div>
            </div>

            <!-- Special Roles Section -->
            <div>
                <x-input-label :value="__('Special Roles')" class="text-base font-medium mb-3" />
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $isAcademicAdvisor ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                            @if($isAcademicAdvisor)
                                ✓ Academic Advisor
                            @else
                                Academic Advisor
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $isSupervisorFaculty ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                            @if($isSupervisorFaculty)
                                ✓ Supervisor Faculty
                            @else
                                Supervisor Faculty
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $isCommittee ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                            @if($isCommittee)
                                ✓ Committee
                            @else
                                Committee
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $isCoordinator ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                            @if($isCoordinator)
                                ✓ Coordinator
                            @else
                                Coordinator
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $isAdmin ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                            @if($isAdmin)
                                ✓ Admin
                            @else
                                Admin
                            @endif
                        </span>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Special roles are managed by system administrators and cannot be modified here.') }}
                </p>
            </div>
        @endif

        @if($editMode)
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
