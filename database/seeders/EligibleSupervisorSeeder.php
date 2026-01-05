<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Hash;

class EligibleSupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates lecturers who are eligible to supervise (non-administrative positions)
     */
    public function run(): void
    {
        $this->command->info('Creating eligible supervisor lecturers...');

        // Define lecturers with NON-administrative positions
        // These positions are NOT in the administrative list: 'Lecturer', 'Senior Lecturer', 'Associate Professor', 'Professor', etc.
        $lecturers = [
            // CS Department - Eligible supervisors
            [
                'name' => 'Dr. Sarah Tan Mei Ling',
                'email' => 'sarah.tan@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Senior Lecturer', // NOT administrative
                'department' => 'CS',
                'researchGroup' => 'SERG',
                'hometown' => ['address' => 'No. 45, Jalan SS2/24, Petaling Jaya', 'city' => 'Petaling Jaya', 'state' => 'Selangor', 'postcode' => '47300', 'lat' => 3.1073, 'lng' => 101.6067],
            ],
            [
                'name' => 'Dr. Ahmad Zaki bin Mohd Ali',
                'email' => 'ahmad.zaki@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Lecturer', // NOT administrative
                'department' => 'CS',
                'researchGroup' => 'CSRG',
                'hometown' => ['address' => 'No. 12, Jalan Bukit Bintang', 'city' => 'Kuala Lumpur', 'state' => 'Kuala Lumpur', 'postcode' => '50000', 'lat' => 3.1489, 'lng' => 101.7030],
            ],
            [
                'name' => 'Assoc. Prof. Dr. Lim Wei Jie',
                'email' => 'lim.weijie@university.edu.my',
                'staffGrade' => 'DS53-A',
                'role' => 'non-management',
                'position' => 'Associate Professor', // NOT administrative
                'department' => 'CS',
                'researchGroup' => 'VISIC',
                'hometown' => ['address' => 'No. 88, Jalan Permas 10, Taman Permas Jaya', 'city' => 'Johor Bahru', 'state' => 'Johor', 'postcode' => '81750', 'lat' => 1.4927, 'lng' => 103.7414],
            ],
            [
                'name' => 'Dr. Nurul Aina binti Hassan',
                'email' => 'nurul.aina@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Senior Lecturer', // NOT administrative
                'department' => 'CS',
                'researchGroup' => 'DBIS',
                'hometown' => ['address' => 'No. 23, Jalan Melati 3/2, Taman Melati', 'city' => 'Shah Alam', 'state' => 'Selangor', 'postcode' => '40100', 'lat' => 3.0733, 'lng' => 101.5185],
            ],
            [
                'name' => 'Dr. Wong Chee Keong',
                'email' => 'wong.cheekeong@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Lecturer', // NOT administrative
                'department' => 'CS',
                'researchGroup' => 'SCORE',
                'hometown' => ['address' => 'No. 67, Jalan Raja Uda, Butterworth', 'city' => 'Butterworth', 'state' => 'Penang', 'postcode' => '12300', 'lat' => 5.4164, 'lng' => 100.3327],
            ],

            // SN Department - Eligible supervisors
            [
                'name' => 'Dr. Lee Ming Chuan',
                'email' => 'lee.mingchuan@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Senior Lecturer', // NOT administrative
                'department' => 'SN',
                'researchGroup' => 'CNRG',
                'hometown' => ['address' => 'No. 12, Jalan Gambang, Taman Indah', 'city' => 'Kuantan', 'state' => 'Pahang', 'postcode' => '25150', 'lat' => 3.8077, 'lng' => 103.3260],
            ],
            [
                'name' => 'Dr. Siti Nurul Aina binti Abdullah',
                'email' => 'siti.nurul@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Lecturer', // NOT administrative
                'department' => 'SN',
                'researchGroup' => 'KECL',
                'hometown' => ['address' => 'No. 78, Jalan Bukit Kecil 1', 'city' => 'Ipoh', 'state' => 'Perak', 'postcode' => '30100', 'lat' => 4.5975, 'lng' => 101.0901],
            ],
            [
                'name' => 'Assoc. Prof. Dr. Kumar Selvam',
                'email' => 'kumar.selvam@university.edu.my',
                'staffGrade' => 'DS53-A',
                'role' => 'non-management',
                'position' => 'Associate Professor', // NOT administrative
                'department' => 'SN',
                'researchGroup' => 'ISP',
                'hometown' => ['address' => 'No. 21, Jalan Mawar 5, Taman Mawar', 'city' => 'Seremban', 'state' => 'Negeri Sembilan', 'postcode' => '70100', 'lat' => 2.7258, 'lng' => 101.9424],
            ],

            // GMM Department - Eligible supervisors
            [
                'name' => 'Dr. Chan Mei Yee',
                'email' => 'chan.meiyee@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Senior Lecturer', // NOT administrative
                'department' => 'GMM',
                'researchGroup' => 'VISIC',
                'hometown' => ['address' => 'No. 33, Jalan Tun Abdul Razak', 'city' => 'Melaka', 'state' => 'Melaka', 'postcode' => '75000', 'lat' => 2.1896, 'lng' => 102.2501],
            ],
            [
                'name' => 'Dr. Vijay Anand',
                'email' => 'vijay.anand2@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Lecturer', // NOT administrative
                'department' => 'GMM',
                'researchGroup' => 'MIRG',
                'hometown' => ['address' => 'No. 101, Jalan Bukit Jambul', 'city' => 'Bayan Lepas', 'state' => 'Penang', 'postcode' => '11900', 'lat' => 5.3410, 'lng' => 100.2783],
            ],

            // CY Department - Eligible supervisors
            [
                'name' => 'Dr. Liew Chin Yee',
                'email' => 'liew.chinyee2@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Senior Lecturer', // NOT administrative
                'department' => 'CY',
                'researchGroup' => 'Cy-SIG',
                'hometown' => ['address' => 'No. 29, Jalan SS15/4d, Subang Jaya', 'city' => 'Subang Jaya', 'state' => 'Selangor', 'postcode' => '47500', 'lat' => 3.0738, 'lng' => 101.5881],
            ],
            [
                'name' => 'Dr. Chong Wei Ming',
                'email' => 'chong.weiming2@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Lecturer', // NOT administrative
                'department' => 'CY',
                'researchGroup' => 'SCORE',
                'hometown' => ['address' => 'No. 82, Jalan Bunga Raya, Taman Sri Indah', 'city' => 'Alor Setar', 'state' => 'Kedah', 'postcode' => '05100', 'lat' => 6.1184, 'lng' => 100.3681],
            ],
        ];

        $count = 0;
        $startID = 3001; // Start from LC3001 to avoid conflicts

        foreach ($lecturers as $index => $data) {
            // Generate lecturer ID
            $lecturerID = 'LC' . str_pad($startID + $index, 4, '0', STR_PAD_LEFT);

            // Check if user already exists
            if (User::where('email', $data['email'])->exists()) {
                $this->command->warn("Skipping {$data['email']} - User already exists");
                continue;
            }

            // Check if lecturer ID already exists
            if (Lecturer::where('lecturerID', $lecturerID)->exists()) {
                $this->command->warn("Skipping {$lecturerID} - Lecturer ID already exists");
                continue;
            }

            // Create user account
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'lecturer',
            ]);

            // Determine quota based on staff grade
            $quota = match($data['staffGrade']) {
                'DS54-A' => rand(8, 10),  // Professor - higher quota
                'DS53-A' => rand(6, 8),   // Associate Professor
                'DS52-A' => rand(5, 7),   // Senior Lecturer
                'DS51-A' => rand(4, 6),   // Lecturer
                'DS45-A' => rand(4, 5),   // Senior Lecturer
                'VK7-A' => rand(5, 7),    // Administrative staff with academic role
                'VK6-A' => rand(4, 5),    // Administrative staff with academic role
                default => 5,
            };

            // Random semester (1 or 2) and year 2025
            $semester = rand(1, 2);
            $year = 2025;

            // Get program based on department and research group
            $program = $this->getProgram($data['department'], $data['researchGroup']);

            // Create lecturer profile - IMPORTANT: position is NOT administrative
            $lecturer = Lecturer::create([
                'lecturerID' => $lecturerID,
                'user_id' => $user->id,
                'staffGrade' => $data['staffGrade'],
                'role' => $data['role'],
                'position' => $data['position'], // Non-administrative position
                'department' => $data['department'],
                'researchGroup' => $data['researchGroup'],
                'semester' => $semester,
                'year' => $year,
                'address' => $data['hometown']['address'],
                'city' => $data['hometown']['city'],
                'state' => $data['hometown']['state'],
                'postcode' => $data['hometown']['postcode'],
                'country' => 'Malaysia',
                'latitude' => $data['hometown']['lat'],
                'longitude' => $data['hometown']['lng'],
                'status' => Lecturer::STATUS_ACTIVE, // Uses 'Active' from database ENUM
                'isSupervisorFaculty' => true, // ✅ Can supervise
                'supervisor_quota' => $quota,
                'current_assignments' => 0, // ✅ Has available quota
                'isAcademicAdvisor' => $index % 3 === 0, // Some are academic advisors
                'isCommittee' => false, // NOT committee
                'isCoordinator' => false, // NOT coordinator
                'isAdmin' => false,
                'travel_preference' => ['local', 'nationwide'][rand(0, 1)],
                'program' => $program,
            ]);

            // Verify they can supervise
            $canSupervise = $lecturer->canSupervise();
            $hasQuota = $lecturer->hasAvailableQuota();

            $this->command->info(
                "✅ Created {$lecturerID}: {$data['name']} | " .
                "Position: {$data['position']} | " .
                "Dept: {$data['department']} | " .
                "Quota: {$quota} | " .
                "Can Supervise: " . ($canSupervise ? 'YES ✅' : 'NO ❌') . " | " .
                "Has Quota: " . ($hasQuota ? 'YES ✅' : 'NO ❌')
            );
            $count++;
        }

        $this->command->info('');
        $this->command->info('✅ Eligible supervisor lecturers created successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Total eligible supervisors created: ' . $count);
        
        // Count how many can actually supervise
        $eligibleCount = Lecturer::where('isSupervisorFaculty', true)
            ->where('status', Lecturer::STATUS_ACTIVE)
            ->get()
            ->filter(function($lecturer) {
                return $lecturer->canSupervise() && $lecturer->hasAvailableQuota();
            })
            ->count();
        
        $this->command->info('   - Total eligible supervisors in system (can supervise + has quota): ' . $eligibleCount);
        $this->command->info('   - Average quota per supervisor: ' . round(Lecturer::where('isSupervisorFaculty', true)->avg('supervisor_quota'), 1));
        $this->command->info('   - Total available slots: ' . Lecturer::where('isSupervisorFaculty', true)->sum('supervisor_quota'));

        $this->command->info('');
        $this->command->info('🔑 Test Credentials (password: password):');
        $this->command->info('   - sarah.tan@university.edu.my (CS - Senior Lecturer)');
        $this->command->info('   - ahmad.zaki@university.edu.my (CS - Lecturer)');
        $this->command->info('   - lee.mingchuan@university.edu.my (SN - Senior Lecturer)');
    }

    /**
     * Get program based on department and research group
     */
    private function getProgram(string $department, string $researchGroup): ?string
    {
        // Map research groups to programs
        return match($researchGroup) {
            'SERG', 'CSRG', 'SCORE' => 'BCS', // Software Engineering related
            'CNRG', 'KECL' => 'BCN', // Networking related
            'VISIC', 'MIRG' => 'BCM', // Multimedia related
            'Cy-SIG' => 'BCY', // Cybersecurity related
            'DSSIM', 'DBIS', 'EDU-TECH', 'ISP' => 'BCS', // General computing -> Software Engineering
            default => ['BCS', 'BCN', 'BCM', 'BCY', 'DRC'][rand(0, 4)], // Random program for unknown groups
        };
    }
}

