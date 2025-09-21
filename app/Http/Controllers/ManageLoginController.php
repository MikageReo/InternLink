<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;

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

    /**
     * Handle bulk user registration from CSV file
     */
    public function registerUsers(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'semester' => 'required|in:1,2',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $file = $request->file('csv_file');
        $semester = $request->semester;
        $year = $request->year;

        // Read CSV file
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData); // Remove header row

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map CSV columns to user data
                $userData = array_combine($header, $row);

                // Validate required fields
                $validator = Validator::make($userData, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'role' => 'required|in:student,lecturer',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Generate default password
                $defaultPassword = 'password123';

                // Start database transaction
                \DB::beginTransaction();

                try {
                    if ($userData['role'] === 'student') {
                        // Validate student-specific fields
                        $studentValidator = Validator::make($userData, [
                            'studentID' => 'required|string|unique:students,studentID',
                            'phone' => 'nullable|string',
                            'address' => 'nullable|string',
                            'nationality' => 'nullable|string',
                            'program' => 'nullable|string',
                        ]);

                        if ($studentValidator->fails()) {
                            throw new \Exception(implode(', ', $studentValidator->errors()->all()));
                        }

                        // Create student record
                        $student = Student::create([
                            'studentID' => $userData['studentID'],
                            'phone' => $userData['phone'] ?? null,
                            'address' => $userData['address'] ?? null,
                            'nationality' => $userData['nationality'] ?? null,
                            'program' => $userData['program'] ?? null,
                            'status' => 'active',
                        ]);

                        // Create user record
                        $user = User::create([
                            'name' => $userData['name'],
                            'email' => $userData['email'],
                            'password' => Hash::make($defaultPassword),
                            'role' => 'student',
                            'studentID' => $student->studentID,
                        ]);
                    } else { // lecturer
                        // Validate lecturer-specific fields
                        $lecturerValidator = Validator::make($userData, [
                            'lecturerID' => 'required|string|unique:lecturers,lecturerID',
                            'staffGrade' => 'nullable|string',
                            'role' => 'nullable|string',
                            'position' => 'nullable|string',
                            'state' => 'nullable|string',
                            'researchGroup' => 'nullable|string',
                            'department' => 'nullable|string',
                        ]);

                        if ($lecturerValidator->fails()) {
                            throw new \Exception(implode(', ', $lecturerValidator->errors()->all()));
                        }

                        // Create lecturer record
                        $lecturer = Lecturer::create([
                            'lecturerID' => $userData['lecturerID'],
                            'staffGrade' => $userData['staffGrade'] ?? null,
                            'role' => $userData['role'] ?? null,
                            'position' => $userData['position'] ?? null,
                            'state' => $userData['state'] ?? null,
                            'researchGroup' => $userData['researchGroup'] ?? null,
                            'department' => $userData['department'] ?? null,
                            'studentQuota' => 0,
                            'isAcademicAdvisor' => false,
                            'isSupervisorFaculty' => false,
                            'isCommittee' => false,
                            'isCoordinator' => false,
                            'isAdmin' => false,
                        ]);

                        // Create user record
                        $user = User::create([
                            'name' => $userData['name'],
                            'email' => $userData['email'],
                            'password' => Hash::make($defaultPassword),
                            'role' => 'lecturer',
                            'lecturerID' => $lecturer->lecturerID,
                        ]);
                    }

                    \DB::commit();
                    $successCount++;
                } catch (\Exception $e) {
                    \DB::rollback();
                    throw $e;
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        // Prepare response message
        $message = "Registration completed! Successfully registered {$successCount} users.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} users failed to register.";
        }

        return redirect()->route('lecturer.registerUser')
            ->with('success', $message)
            ->with('errors', $errors);
    }

    /**
     * Handle individual student registration
     */
    public function registerStudent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'studentID' => 'required|string|unique:students,studentID',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'nationality' => 'nullable|string',
            'program' => 'nullable|string',
        ]);

        try {
            \DB::beginTransaction();

            // Create student record
            $student = Student::create([
                'studentID' => $request->studentID,
                'phone' => $request->phone,
                'address' => $request->address,
                'nationality' => $request->nationality,
                'program' => $request->program,
                'status' => 'active',
            ]);

            // Create user record
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Default password
                'role' => 'student',
                'studentID' => $student->studentID,
            ]);

            \DB::commit();

            return redirect()->route('lecturer.registerUser')
                ->with('success', 'Student registered successfully! Default password: password123');

        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->route('lecturer.registerUser')
                ->with('error', 'Failed to register student: ' . $e->getMessage());
        }
    }

    /**
     * Handle individual lecturer registration
     */
    public function registerLecturer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'lecturerID' => 'required|string|unique:lecturers,lecturerID',
            'staffGrade' => 'nullable|string',
            'position' => 'nullable|string',
            'state' => 'nullable|string',
            'researchGroup' => 'nullable|string',
            'department' => 'nullable|string',
            'studentQuota' => 'nullable|integer|min:0',
        ]);

        try {
            \DB::beginTransaction();

            // Create lecturer record
            $lecturer = Lecturer::create([
                'lecturerID' => $request->lecturerID,
                'staffGrade' => $request->staffGrade,
                'position' => $request->position,
                'state' => $request->state,
                'researchGroup' => $request->researchGroup,
                'department' => $request->department,
                'studentQuota' => $request->studentQuota ?? 0,
                'isAcademicAdvisor' => $request->has('isAcademicAdvisor'),
                'isSupervisorFaculty' => $request->has('isSupervisorFaculty'),
                'isCommittee' => $request->has('isCommittee'),
                'isCoordinator' => $request->has('isCoordinator'),
                'isAdmin' => $request->has('isAdmin'),
            ]);

            // Create user record
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Default password
                'role' => 'lecturer',
                'lecturerID' => $lecturer->lecturerID,
            ]);

            \DB::commit();

            return redirect()->route('lecturer.registerUser')
                ->with('success', 'Lecturer registered successfully! Default password: password123');

        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->route('lecturer.registerUser')
                ->with('error', 'Failed to register lecturer: ' . $e->getMessage());
        }
    }
}
