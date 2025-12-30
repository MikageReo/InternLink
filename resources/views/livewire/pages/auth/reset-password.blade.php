<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email', 'max:191'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Rules\Password::min(8)->letters()->numbers()->symbols(),
            ],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="min-h-screen flex w-full">
    <!-- Left Section - Purple Background -->
    <div class="hidden lg:flex lg:w-1/2 bg-purple-500 dark:bg-purple-600 items-center justify-center flex-col px-12">
        <!-- Computer Monitor Illustration -->
        <div class="mb-8">
            <svg width="300" height="250" viewBox="0 0 300 250" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Monitor Base -->
                <rect x="100" y="200" width="100" height="15" rx="3" fill="#ffffff" opacity="0.3"/>
                <rect x="120" y="215" width="60" height="8" rx="2" fill="#ffffff" opacity="0.2"/>

                <!-- Monitor Screen -->
                <rect x="50" y="50" width="200" height="150" rx="8" fill="#ffffff" opacity="0.2" stroke="#ffffff" stroke-width="2"/>
                <rect x="60" y="60" width="180" height="130" rx="4" fill="#ffffff" opacity="0.1"/>

                <!-- Lock Icon on Screen -->
                <circle cx="150" cy="110" r="20" fill="#9333ea" opacity="0.8"/>
                <rect x="142" y="110" width="16" height="16" rx="3" fill="#ffffff"/>
                <path d="M145 110 C145 104, 155 104, 155 110" stroke="#ffffff" stroke-width="2" fill="none"/>
            </svg>
        </div>

        <!-- Welcome Text -->
        <div class="text-center text-white">
            <p class="text-xl mb-2">Reset Your Password</p>
            <h1 class="text-4xl font-bold mb-2">
                <span class="text-yellow-300">InternLink</span>
            </h1>
            <p class="text-xl">Set a new password to access your account</p>
        </div>
    </div>

    <!-- Right Section - White/Dark Background -->
    <div class="w-full lg:w-1/2 bg-white dark:bg-gray-900 flex flex-col items-center justify-center px-8 py-12 relative">
        <!-- Logo Section -->
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center mb-4">
                <!-- Book Logo with Stylized Design -->
                <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="dark:opacity-90">
                    <!-- Stylized Book -->
                    <rect x="20" y="25" width="40" height="50" rx="2" fill="#3b82f6" class="dark:fill-blue-400"/>
                    <rect x="25" y="30" width="30" height="40" fill="#ffffff" class="dark:fill-gray-800"/>
                    <line x1="45" y1="30" x2="45" y2="70" stroke="#3b82f6" stroke-width="1" class="dark:stroke-blue-400"/>
                    <line x1="30" y1="45" x2="55" y2="45" stroke="#3b82f6" stroke-width="1" class="dark:stroke-blue-400"/>

                    <!-- Stylized Crest/Flower above book -->
                    <circle cx="40" cy="15" r="8" fill="#fbbf24" class="dark:fill-yellow-400"/>
                    <path d="M40 7 L42 12 L47 12 L43 16 L45 21 L40 17 L35 21 L37 16 L33 12 L38 12 Z" fill="#ffffff" class="dark:fill-gray-800"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-1">InternLink</h2>
        </div>

        <!-- Reset Password Form -->
        <div class="w-full max-w-md">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Reset Password</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                {{ __('Enter your new password below to regain access to your account.') }}
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="resetPassword" class="space-y-5">
                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="mb-2 dark:text-gray-300" />
                    <input
                        wire:model="email"
                        id="email"
                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-sm bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="username"
                        maxlength="191"
                        readonly
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('New Password')" class="mb-2 dark:text-gray-300" />
                    <input
                        wire:model="password"
                        id="password"
                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Enter your new password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-2 dark:text-gray-300" />
                    <input
                        wire:model="password_confirmation"
                        id="password_confirmation"
                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm your new password"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="resetPassword"
                        class="w-full bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 flex items-center justify-center"
                    >
                        <span wire:loading.remove wire:target="resetPassword">
                            {{ __('Reset Password') }}
                        </span>
                        <span wire:loading wire:target="resetPassword" class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span>{{ __('Resetting...') }}</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
