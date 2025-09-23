<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use App\Notifications\UserRegistrationNotification;

class ManageLoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt with automatic role detection
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // First, try to authenticate without role
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Regenerate session for security
            $request->session()->regenerate();

            // Redirect based on user's role
            if ($user->role === 'student') {
                return redirect()->intended(route('student.dashboard'));
            } elseif ($user->role === 'lecturer') {
                return redirect()->intended(route('lecturer.dashboard'));
            } else {
                // Fallback for users without a role
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Invalid user role.',
                ]);
            }
        }

        // If authentication fails
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration with role selection
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student,lecturer',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        // Redirect based on user's role
        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        } else {
            return redirect()->route('lecturer.dashboard');
        }
    }
}
