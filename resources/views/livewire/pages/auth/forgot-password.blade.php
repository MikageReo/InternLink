<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
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
                
                <!-- Email Icon on Screen -->
                <rect x="110" y="90" width="80" height="60" rx="4" fill="#ffffff" opacity="0.3"/>
                <path d="M120 100 L150 120 L180 100" stroke="#9333ea" stroke-width="2" fill="none" opacity="0.8"/>
                <line x1="120" y1="100" x2="120" y2="140" stroke="#9333ea" stroke-width="2" opacity="0.8"/>
                <line x1="180" y1="100" x2="180" y2="140" stroke="#9333ea" stroke-width="2" opacity="0.8"/>
                <line x1="120" y1="140" x2="180" y2="140" stroke="#9333ea" stroke-width="2" opacity="0.8"/>
                
                <!-- Key/Lock Icon -->
                <circle cx="150" cy="110" r="8" fill="#9333ea" opacity="0.6"/>
                <rect x="145" y="118" width="10" height="15" rx="2" fill="#9333ea" opacity="0.6"/>
            </svg>
        </div>
        
        <!-- Welcome Text -->
        <div class="text-center text-white">
            <p class="text-xl mb-2">Reset Your Password</p>
            <h1 class="text-4xl font-bold mb-2">
                <span class="text-yellow-300">InternLink</span>
            </h1>
            <p class="text-xl">We'll help you get back in</p>
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
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-1">INTERNLINK</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Where Learning Never Ends...</p>
        </div>

        <!-- Forgot Password Form -->
        <div class="w-full max-w-md">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Forgot Password?</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                {{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="sendPasswordResetLink" class="space-y-5">
                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="mb-2 dark:text-gray-300" />
                    <input 
                        wire:model="email" 
                        id="email" 
                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400" 
                        type="email" 
                        name="email" 
                        required 
                        autofocus 
                        placeholder="Enter your email address" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        {{ __('Email Password Reset Link') }}
                    </button>
                </div>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium inline-flex items-center" wire:navigate>
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Back to Login') }}
                </a>
            </div>
        </div>
    </div>
</div>
