<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Get the authenticated user and redirect based on role
        $user = auth()->user();

        if ($user->role === 'student') {
            $this->redirect(route('student.dashboard'), navigate: true);
        } elseif ($user->role === 'lecturer') {
            $this->redirect(route('lecturer.dashboard'), navigate: true);
        } else {
            // Fallback for users without a role
            auth()->logout();
            $this->addError('form.email', 'Invalid user role.');
        }
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

                <!-- User Icon on Screen -->
                <circle cx="150" cy="100" r="20" fill="#9333ea" opacity="0.8"/>
                <path d="M150 85 C145 85, 140 90, 140 95 L140 105 C140 110, 145 115, 150 115 C155 115, 160 110, 160 105 L160 95 C160 90, 155 85, 150 85 Z" fill="#ffffff"/>

                <!-- Password Field on Screen -->
                <rect x="80" y="130" width="140" height="30" rx="4" fill="#ffffff" opacity="0.3"/>
                <circle cx="100" cy="145" r="3" fill="#1f2937"/>
                <circle cx="110" cy="145" r="3" fill="#1f2937"/>
                <circle cx="120" cy="145" r="3" fill="#1f2937"/>
                <circle cx="130" cy="145" r="3" fill="#1f2937"/>
                <circle cx="140" cy="145" r="3" fill="#1f2937"/>
            </svg>
        </div>

        <!-- Welcome Text -->
        <div class="text-center text-white">
            <p class="text-xl mb-2">Welcome to</p>
            <h1 class="text-4xl font-bold mb-2">
                <span class="text-yellow-300">InternLink</span>
            </h1>
            <p class="text-xl">Placement Management System</p>
        </div>
    </div>

    <!-- Right Section - White/Dark Background -->
    <div class="w-full lg:w-1/2 bg-white dark:bg-gray-900 flex flex-col items-center justify-center px-8 py-12 relative">
        <!-- Logo Section -->
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center mb-4">
                <img src="{{ asset('LOGO IL.png') }}" alt="InternLink Logo" class="h-40 w-auto dark:opacity-90" />
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-1">InternLink</h2>
        </div>

        <!-- Sign In Form -->
        <div class="w-full max-w-md">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Sign into Your Account</h3>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-5">
                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="mb-2 dark:text-gray-300" />
                    <input
                        wire:model="form.email"
                        id="email"
                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                        type="email"
                        name="email"
                        required
                        autofocus
                        maxlength="30"
                        autocomplete="username"
                        placeholder="Enter your email" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" class="mb-2 dark:text-gray-300" />
                    <input
                        wire:model="form.password"
                        id="password"
                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <!-- Forgot Password -->
                <div class="flex items-center justify-end">
                    @if (Route::has('password.request'))
                        <a class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="login"
                        class="w-full bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 flex items-center justify-center"
                    >
                        <span wire:loading.remove wire:target="login">
                            {{ __('Sign In') }}
                        </span>
                        <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span>{{ __('Signing in...') }}</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
