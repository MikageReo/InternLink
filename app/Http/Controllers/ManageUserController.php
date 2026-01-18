<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Notifications\UserRegistrationNotification;

class ManageUserController extends Controller
{
    /**
     * Show the view users page
     */
    public function showViewUsers()
    {
        return view('lecturer.dashboard.viewUsers');
    }

    /**
     * Handle filtering and displaying users
     */
    public function filterUsers(Request $request)
    {
        $request->validate([
            'role' => 'required|in:student,lecturer',
            'semester' => 'required|in:1,2',
            'year' => 'required|integer|min:2020|max:2040',
        ]);

        $role = $request->role;
        $semester = $request->semester;
        $year = $request->year;

        if ($role === 'student') {
            $users = User::where('role', 'student')
                ->with(['student' => function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                }])
                ->whereHas('student', function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                })
                ->get();
        } else {
            $users = User::where('role', 'lecturer')
                ->with(['lecturer' => function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                }])
                ->whereHas('lecturer', function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                })
                ->get();
        }

        return view('lecturer.dashboard.viewUsers', compact('users', 'role', 'semester', 'year'));
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
        // Increase execution time for large CSV files (5 minutes)
        set_time_limit(300);

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
            // Parse CSV properly handling multi-line fields
            $csvData = [];
            $csvFile = fopen($file->getRealPath(), 'r');

            if ($csvFile === false) {
                return redirect()->route('lecturer.userDirectory')
                    ->with('error', 'CSV file could not be opened.');
            }

            // Read header row
            $header = fgetcsv($csvFile);
            if ($header === false) {
                fclose($csvFile);
                return redirect()->route('lecturer.userDirectory')
                    ->with('error', 'CSV file is empty or could not be read.');
            }
            $header = array_map('trim', $header);
            $headerCount = count($header);

            // Read data rows
            while (($row = fgetcsv($csvFile)) !== false) {
                // Skip completely empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                $csvData[] = $row;
            }
            fclose($csvFile);

            if (empty($csvData)) {
                return redirect()->route('lecturer.userDirectory')
                    ->with('error', 'CSV file contains no data rows.');
            }

            // 🔹 Auto-detect file type by header
            if (in_array('studentID', $header)) {
                $roleType = 'student';
            } elseif (in_array('lecturerID', $header)) {
                $roleType = 'lecturer';
            } else {
                return redirect()->route('lecturer.userDirectory')
                    ->with('error', "CSV must contain either 'studentID' or 'lecturerID' column.");
            }
        } catch (\Exception $e) {
            return redirect()->route('lecturer.userDirectory')
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

                // Check if row has same number of columns as header
                $rowCount = count($row);
                if ($rowCount !== $headerCount) {
                    $errorCount++;
                    $errors[] = "Row " . ($index + 2) . ": Column count mismatch. Expected {$headerCount} columns but found {$rowCount}. This may be due to multi-line fields in the CSV.";
                    continue;
                }

                // Map CSV columns to user data
                $userData = array_combine($header, $row);

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
                        'supervisor_quota' => $userData['supervisor_quota'] ?? $userData['studentQuota'] ?? 0,
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

        return redirect()->route('lecturer.userDirectory')
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

            return redirect()->route('lecturer.userDirectory')
                ->with('success', 'Student registered successfully!' . $emailMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('lecturer.userDirectory')
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
            'supervisor_quota' => 'nullable|integer|min:0',
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
                'supervisor_quota' => $request->supervisor_quota ?? 0,
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

            return redirect()->route('lecturer.userDirectory')
                ->with('success', 'Lecturer registered successfully!' . $emailMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('lecturer.userDirectory')
                ->with('error', 'Failed to register lecturer: ' . $e->getMessage());
        }
    }

    /**
     * Show the user directory page (combined view and registration)
     */
    public function showUserDirectory()
    {
        return view('lecturer.dashboard.userDirectory');
    }

    /**
     * Handle filtering and displaying users in the user directory
     */
    public function filterUserDirectory(Request $request)
    {
        $request->validate([
            'role' => 'required|in:student,lecturer',
            'semester' => 'required|in:1,2',
            'year' => 'required|integer|min:2020|max:2040',
        ]);

        $role = $request->role;
        $semester = $request->semester;
        $year = $request->year;

        if ($role === 'student') {
            $users = User::where('role', 'student')
                ->with(['student' => function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                }])
                ->whereHas('student', function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                })
                ->get();
        } else {
            $users = User::where('role', 'lecturer')
                ->with(['lecturer' => function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                }])
                ->whereHas('lecturer', function ($query) use ($semester, $year) {
                    $query->where('semester', $semester)
                        ->where('year', $year);
                })
                ->get();
        }

        return view('lecturer.dashboard.userDirectory', compact('users', 'role', 'semester', 'year'));
    }
}
