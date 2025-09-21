<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes using Livewire components
Route::middleware('guest')->group(function () {
    Route::view('/login', 'livewire.pages.auth.login')->name('login');
    Route::view('/register', 'livewire.pages.auth.register')->name('register');
});

// Redirect authenticated users to their role-specific dashboard
Route::get('/dashboard', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        } else {
            return redirect()->route('lecturer.dashboard');
        }
    }
    return redirect()->route('login');
})->middleware(['auth', 'verified'])->name('dashboard');

// Student routes
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::view('/dashboard', 'student.dashboard.studentPortal')->name('dashboard');
});

// Lecturer routes
Route::middleware(['auth', 'verified', 'role:lecturer'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::view('/dashboard', 'lecturer.dashboard.lecturerPortal')->name('dashboard');
    Route::view('/register-user', 'lecturer.dashboard.registerUser')->name('registerUser');
    Route::post('/register-user', [App\Http\Controllers\ManageLoginController::class, 'registerUsers'])->name('registerUser.store');
    Route::post('/register-student', [App\Http\Controllers\ManageLoginController::class, 'registerStudent'])->name('registerStudent');
    Route::post('/register-lecturer', [App\Http\Controllers\ManageLoginController::class, 'registerLecturer'])->name('registerLecturer');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
