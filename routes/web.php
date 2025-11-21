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
    Route::view('/course-verification', 'student.dashboard.courseVerification')->name('courseVerification');
    Route::view('/placement-applications', 'student.dashboard.placementApplications')->name('placementApplications');
    Route::view('/request-defer', 'student.dashboard.requestDefer')->name('requestDefer');
    Route::view('/change-request-history', 'student.dashboard.changeRequestHistory')->name('changeRequestHistory');
});

// Lecturer routes
Route::middleware(['auth', 'verified', 'role:lecturer'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::view('/dashboard', 'lecturer.dashboard.lecturerPortal')->name('dashboard');
    Route::view('/register-user', 'lecturer.dashboard.registerUser')->name('registerUser');
    Route::view('/course-verification-management', 'lecturer.dashboard.courseVerificationManagement')->name('courseVerificationManagement');
    Route::view('/user-directory', 'lecturer.dashboard.userDirectory')->name('userDirectory');

    // Placement applications - restricted to committee and coordinator only
    Route::middleware(['committee.coordinator'])->group(function () {
        Route::view('/placement-applications', 'lecturer.dashboard.placementApplications')->name('placementApplications');
        Route::view('/request-defer', 'lecturer.dashboard.requestDefer')->name('requestDefer');
        Route::view('/change-requests', 'lecturer.dashboard.changeRequests')->name('changeRequests');
    });

    // Supervisor assignment - restricted to coordinators only
    Route::middleware(['coordinator'])->group(function () {
        Route::view('/supervisor-assignments', 'lecturer.dashboard.supervisorAssignments')->name('supervisorAssignments');
        Route::view('/auto-supervisor-assignments', 'lecturer.dashboard.autoSupervisorAssignments')->name('autoSupervisorAssignments');
    });

    // AHP Weight Calculator - restricted to admins and coordinators
    Route::middleware(['admin.coordinator'])->group(function () {
        Route::view('/ahp-calculator', 'lecturer.dashboard.ahpCalculator')->name('ahpCalculator');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
