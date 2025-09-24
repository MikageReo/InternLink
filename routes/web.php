<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManageUserController;

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
    Route::view('/course-verification', 'student.dashboard.courseVerification')->name('courseVerification');
});

// Lecturer routes
Route::middleware(['auth', 'verified', 'role:lecturer'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::view('/dashboard', 'lecturer.dashboard.lecturerPortal')->name('dashboard');
    Route::view('/register-user', 'lecturer.dashboard.registerUser')->name('registerUser');
    Route::view('/course-verification-management', 'lecturer.dashboard.courseVerificationManagement')->name('courseVerificationManagement');

    Route::controller(ManageUserController::class)->group(function () {
        Route::post('/register-user', 'registerUsers')->name('registerUsers');
        Route::post('/register-student', 'registerStudent')->name('registerStudent');
        Route::post('/register-lecturer', 'registerLecturer')->name('registerLecturer');
        Route::get('/user-directory', 'showUserDirectory')->name('userDirectory');
        Route::post('/user-directory', 'filterUserDirectory')->name('userDirectory.filter');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
