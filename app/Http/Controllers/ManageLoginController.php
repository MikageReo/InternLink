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

    /**
     * Handle bulk user registration from CSV file
     */
    public function registerUsers(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'semester' => 'required|in:1,2',
            'year' => 'required|integer|min:2020|max:2040',
        ]);

        $file = $request->file('csv_file');
        $semester = $request->semester;
        $year = $request->year;

        // Read CSV file
        try {
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            if (empty($csvData)) {
                return redirect()->route('lecturer.registerUser')
                    ->with('error', 'CSV file is empty or could not be read.');
            }

            $header = array_shift($csvData); // Get header row
            $header = array_map('trim', $header);

            // 🔹 Auto-detect file type by header
            if (in_array('studentID', $header)) {
                $roleType = 'student';
            } elseif (in_array('lecturerID', $header)) {
                $roleType = 'lecturer';
            } else {
                return redirect()->route('lecturer.registerUser')
                    ->with('error', "CSV must contain either 'studentID' or 'lecturerID' column.");
            }
        } catch (\Exception $e) {
            return redirect()->route('lecturer.registerUser')
                ->with('error', 'Error reading CSV file: ' . $e->getMessage());
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $createdUsers = []; // Track created users for email notifications

        foreach ($csvData as $index => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Trim whitespace from row data
                $row = array_map('trim', $row);

                // Map CSV columns to user data
                $userData = array_combine($header, $row);

                // Check if array_combine failed
                if ($userData === false) {
                    $errorCount++;
                    $errors[] = "Row " . ($index + 2) . ": Invalid number of columns.";
                    continue;
                }

                DB::beginTransaction();

                if ($roleType === 'student') {
                    // Validate student
                    $studentValidator = Validator::make($userData, [
                        'studentID' => 'required|string|unique:students,studentID',
                        'name' => 'required|string|max:255',
                        'email' => 'required|email|unique:users,email',
                    ]);
                    if ($studentValidator->fails()) {
                        throw new \Exception(implode(', ', $studentValidator->errors()->all()));
                    }

                    // Create User
                    $defaultPassword = uniqid();
                    $user = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => Hash::make($defaultPassword),
                        'role' => 'student',
                    ]);

                    // Store user info for email notification
                    $createdUsers[] = [
                        'user' => $user,
                        'password' => $defaultPassword,
                        'role' => 'student'
                    ];

                    // Create Student
                    Student::create([
                        'studentID' => $userData['studentID'],
                        'user_id'   => $user->id,
                        'phone'     => $userData['phone'] ?? null,
                        'address'   => $userData['address'] ?? null,
                        'nationality' => $userData['nationality'] ?? null,
                        'program'   => $userData['program'] ?? null,
                        'semester'  => $semester,
                        'year'      => $year,
                        'status'    => 'active',
                    ]);
                } else {
                    // Validate lecturer
                    $lecturerValidator = Validator::make($userData, [
                        'lecturerID' => 'required|string|unique:lecturers,lecturerID',
                        'name'       => 'required|string|max:255',
                        'email'      => 'required|email|unique:users,email',
                    ]);
                    if ($lecturerValidator->fails()) {
                        throw new \Exception(implode(', ', $lecturerValidator->errors()->all()));
                    }

                    // Create User
                    $defaultPassword = uniqid();
                    $user = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => Hash::make($defaultPassword),
                        'role' => 'lecturer',
                    ]);

                    // Store user info for email notification
                    $createdUsers[] = [
                        'user' => $user,
                        'password' => $defaultPassword,
                        'role' => 'lecturer'
                    ];

                    // Create Lecturer
                    Lecturer::create([
                        'lecturerID' => $userData['lecturerID'],
                        'user_id'    => $user->id,
                        'staffGrade' => $userData['staffGrade'] ?? null,
                        'role'       => $userData['role'] ?? null,
                        'position'   => $userData['position'] ?? null,
                        'state'      => $userData['state'] ?? null,
                        'researchGroup' => $userData['researchGroup'] ?? null,
                        'department' => $userData['department'] ?? null,
                        'semester'   => $semester,
                        'year'       => $year,
                        'studentQuota' => 0,
                        'isAcademicAdvisor'   => $userData['isAcademicAdvisor'] ?? 0,
                        'isSupervisorFaculty' => $userData['isSupervisorFaculty'] ?? 0,
                        'isCommittee'         => $userData['isCommittee'] ?? 0,
                        'isCoordinator'       => $userData['isCoordinator'] ?? 0,
                        'isAdmin'             => $userData['isAdmin'] ?? 0,
                    ]);
                }

                DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                DB::rollback();
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        // Send email notifications to all successfully created users
        $emailCount = 0;
        if (!empty($createdUsers)) {
            foreach ($createdUsers as $userInfo) {
                try {
                    $userInfo['user']->notify(new UserRegistrationNotification(
                        $userInfo['password'],
                        $userInfo['role']
                    ));
                    $emailCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to send email to ' . $userInfo['user']->email . ': ' . $e->getMessage());
                }
            }
        }

        // Prepare response message
        $message = "Registration completed! Successfully registered {$successCount} users.";
        if ($emailCount > 0) {
            $message .= " Email notifications sent to {$emailCount} users.";
        }
        if ($errorCount > 0) {
            $message .= " {$errorCount} users failed to register.";
        }

        return redirect()->route('lecturer.registerUser')
            ->with('success', $message)
            ->with('csvErrors', $errors);
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
            DB::beginTransaction();

            // Create user record
            $defaultPassword = uniqid();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($defaultPassword),
                'role' => 'student',
            ]);

            // Create student record
            $student = Student::create([
                'studentID' => $request->studentID,
                'user_id' => $user->id,
                'phone' => $request->phone,
                'address' => $request->address,
                'nationality' => $request->nationality,
                'program' => $request->program,
                'semester' => $request->semester,
                'year' => $request->year,
                'status' => 'active',
            ]);



            DB::commit();

            // Send email notification
            try {
                $user->notify(new UserRegistrationNotification($defaultPassword, 'student'));
                $emailMessage = ' Email notification sent to ' . $user->email . '.';
            } catch (\Exception $e) {
                Log::error('Failed to send email to ' . $user->email . ': ' . $e->getMessage());
                $emailMessage = ' Note: Email notification failed to send.';
            }

            return redirect()->route('lecturer.registerUser')
                ->with('success', 'Student registered successfully!' . $emailMessage);
        } catch (\Exception $e) {
            DB::rollback();
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
            'role' => 'nullable|string',
            'position' => 'nullable|string',
            'state' => 'nullable|string',
            'researchGroup' => 'nullable|string',
            'department' => 'nullable|string',
            'studentQuota' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create user record
            $defaultPassword = uniqid();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($defaultPassword),
                'role' => 'lecturer',
            ]);

            // Create lecturer record
            $lecturer = Lecturer::create([
                'lecturerID' => $request->lecturerID,
                'staffGrade' => $request->staffGrade,
                'user_id' => $user->id,
                'role' => $request->role,
                'position' => $request->position,
                'state' => $request->state,
                'researchGroup' => $request->researchGroup,
                'department' => $request->department,
                'semester' => $request->semester,
                'year' => $request->year,
                'studentQuota' => $request->studentQuota ?? 0,
                'isAcademicAdvisor' => $request->has('isAcademicAdvisor'),
                'isSupervisorFaculty' => $request->has('isSupervisorFaculty'),
                'isCommittee' => $request->has('isCommittee'),
                'isCoordinator' => $request->has('isCoordinator'),
                'isAdmin' => $request->has('isAdmin'),
            ]);

            DB::commit();

            // Send email notification
            try {
                $user->notify(new UserRegistrationNotification($defaultPassword, 'lecturer'));
                $emailMessage = ' Email notification sent to ' . $user->email . '.';
            } catch (\Exception $e) {
                Log::error('Failed to send email to ' . $user->email . ': ' . $e->getMessage());
                $emailMessage = ' Note: Email notification failed to send.';
            }

            return redirect()->route('lecturer.registerUser')
                ->with('success', 'Lecturer registered successfully!' . $emailMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('lecturer.registerUser')
                ->with('error', 'Failed to register lecturer: ' . $e->getMessage());
        }
    }
}
