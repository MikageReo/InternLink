<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Hash;

class SupervisorLecturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating supervisor lecturers with proper attributes...');

        // Define 20 lecturers with complete attributes matching registration form
        $lecturers = [
            // CS Department (Computer Science) - 8 lecturers
            [
                'name' => 'Dr. Ahmad Fadzli bin Hassan',
                'email' => 'ahmad.fadzli@university.edu.my',
                'staffGrade' => 'DS54-A',
                'role' => 'management',
                'position' => 'Dean',
                'department' => 'CS',
                'researchGroup' => 'SERG',
                'hometown' => ['address' => 'No. 23, Jalan Melati 3/2, Taman Melati', 'city' => 'Shah Alam', 'state' => 'Selangor', 'postcode' => '40100', 'lat' => 3.0733, 'lng' => 101.5185],
            ],
            [
                'name' => 'Prof. Siti Hajar binti Abdullah',
                'email' => 'siti.hajar@university.edu.my',
                'staffGrade' => 'DS53-A',
                'role' => 'management',
                'position' => 'Deputy Dean(R)',
                'department' => 'CS',
                'researchGroup' => 'DSSIM',
                'hometown' => ['address' => 'No. 15, Jalan Putra 5/1, Bandar Baru Bangi', 'city' => 'Bangi', 'state' => 'Selangor', 'postcode' => '43650', 'lat' => 2.9912, 'lng' => 101.7719],
            ],
            [
                'name' => 'Dr. Lim Wei Jian',
                'email' => 'lim.weijian@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'CS',
                'researchGroup' => 'CSRG',
                'hometown' => ['address' => 'No. 88, Jalan Permas 10, Taman Permas Jaya', 'city' => 'Johor Bahru', 'state' => 'Johor', 'postcode' => '81750', 'lat' => 1.4927, 'lng' => 103.7414],
            ],
            [
                'name' => 'Assoc. Prof. Rajesh Kumar',
                'email' => 'rajesh.kumar@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'CS',
                'researchGroup' => 'VISIC',
                'hometown' => ['address' => 'No. 42, Lorong Cempaka 12, Taman Cempaka', 'city' => 'Kota Bharu', 'state' => 'Kelantan', 'postcode' => '15150', 'lat' => 6.1334, 'lng' => 102.2386],
            ],
            [
                'name' => 'Dr. Tan Mei Ling',
                'email' => 'tan.meiling@university.edu.my',
                'staffGrade' => 'DS45-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'CS',
                'researchGroup' => 'DBIS',
                'hometown' => ['address' => 'No. 67, Jalan Raja Uda, Butterworth', 'city' => 'Butterworth', 'state' => 'Penang', 'postcode' => '12300', 'lat' => 5.4164, 'lng' => 100.3327],
            ],
            [
                'name' => 'Dr. Muhammad Irfan bin Ismail',
                'email' => 'muhammad.irfan@university.edu.my',
                'staffGrade' => 'VK7-A',
                'role' => 'management',
                'position' => 'Coordinator (s)',
                'department' => 'CS',
                'researchGroup' => 'EDU-TECH',
                'hometown' => ['address' => 'No. 19, Jalan Telawi 3, Bangsar', 'city' => 'Kuala Lumpur', 'state' => 'Kuala Lumpur', 'postcode' => '59100', 'lat' => 3.1302, 'lng' => 101.6737],
            ],
            [
                'name' => 'Dr. Wong Kar Wai',
                'email' => 'wong.karwai@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'management',
                'position' => 'Head of Programs',
                'department' => 'CS',
                'researchGroup' => 'ISP',
                'hometown' => ['address' => 'No. 56, Jalan Dato Onn, Taman Tun Dr Ismail', 'city' => 'Kuala Lumpur', 'state' => 'Kuala Lumpur', 'postcode' => '60000', 'lat' => 3.1471, 'lng' => 101.6439],
            ],
            [
                'name' => 'Dr. Nurul Aini binti Mohd Salleh',
                'email' => 'nurul.aini@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'CS',
                'researchGroup' => 'SCORE',
                'hometown' => ['address' => 'No. 34, Jalan Seri Austin 1, Taman Seri Austin', 'city' => 'Johor Bahru', 'state' => 'Johor', 'postcode' => '81100', 'lat' => 1.5102, 'lng' => 103.7414],
            ],

            // SN Department (Systems & Networking) - 5 lecturers
            [
                'name' => 'Prof. Mohd Azlan bin Othman',
                'email' => 'mohd.azlan@university.edu.my',
                'staffGrade' => 'DS54-A',
                'role' => 'management',
                'position' => 'Deputy Dean(A)',
                'department' => 'SN',
                'researchGroup' => 'CNRG',
                'hometown' => ['address' => 'No. 91, Jalan BPP 8/1, Bandar Putra Permai', 'city' => 'Seri Kembangan', 'state' => 'Selangor', 'postcode' => '43300', 'lat' => 3.0233, 'lng' => 101.6968],
            ],
            [
                'name' => 'Dr. Lee Seng Huat',
                'email' => 'lee.senghuat@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'SN',
                'researchGroup' => 'CNRG',
                'hometown' => ['address' => 'No. 12, Jalan Gambang, Taman Indah', 'city' => 'Kuantan', 'state' => 'Pahang', 'postcode' => '25150', 'lat' => 3.8077, 'lng' => 103.3260],
            ],
            [
                'name' => 'Assoc. Prof. Nurul Huda binti Rahman',
                'email' => 'nurul.huda@university.edu.my',
                'staffGrade' => 'DS53-A',
                'role' => 'management',
                'position' => 'Coordinator (s)',
                'department' => 'SN',
                'researchGroup' => 'KECL',
                'hometown' => ['address' => 'No. 45, Jalan Sultan Zainal Abidin', 'city' => 'Kuala Terengganu', 'state' => 'Terengganu', 'postcode' => '20000', 'lat' => 5.3302, 'lng' => 103.1408],
            ],
            [
                'name' => 'Dr. Kumar Selvam',
                'email' => 'kumar.selvam@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'SN',
                'researchGroup' => 'ISP',
                'hometown' => ['address' => 'No. 78, Jalan Bukit Kecil 1', 'city' => 'Ipoh', 'state' => 'Perak', 'postcode' => '30100', 'lat' => 4.5975, 'lng' => 101.0901],
            ],
            [
                'name' => 'Dr. Azizah binti Ahmad',
                'email' => 'azizah.ahmad@university.edu.my',
                'staffGrade' => 'VK6-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'SN',
                'researchGroup' => 'SCORE',
                'hometown' => ['address' => 'No. 21, Jalan Mawar 5, Taman Mawar', 'city' => 'Seremban', 'state' => 'Negeri Sembilan', 'postcode' => '70100', 'lat' => 2.7258, 'lng' => 101.9424],
            ],

            // GMM Department (Games & Multimedia) - 4 lecturers
            [
                'name' => 'Dr. Chan Sook Ling',
                'email' => 'chan.sookling@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'management',
                'position' => 'Head of Programs',
                'department' => 'GMM',
                'researchGroup' => 'VISIC',
                'hometown' => ['address' => 'No. 33, Jalan Tun Abdul Razak', 'city' => 'Melaka', 'state' => 'Melaka', 'postcode' => '75000', 'lat' => 2.1896, 'lng' => 102.2501],
            ],
            [
                'name' => 'Assoc. Prof. Kamal Ariffin bin Ibrahim',
                'email' => 'kamal.ariffin@university.edu.my',
                'staffGrade' => 'DS53-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'GMM',
                'researchGroup' => 'MIRG',
                'hometown' => ['address' => 'No. 101, Jalan Bukit Jambul', 'city' => 'Bayan Lepas', 'state' => 'Penang', 'postcode' => '11900', 'lat' => 5.3410, 'lng' => 100.2783],
            ],
            [
                'name' => 'Dr. Vijay Anand',
                'email' => 'vijay.anand@university.edu.my',
                'staffGrade' => 'DS45-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'GMM',
                'researchGroup' => 'EDU-TECH',
                'hometown' => ['address' => 'No. 58, Jalan Stampin, Kuching', 'city' => 'Kuching', 'state' => 'Sarawak', 'postcode' => '93350', 'lat' => 1.5535, 'lng' => 110.3479],
            ],
            [
                'name' => 'Dr. Farah Nadia binti Ismail',
                'email' => 'farah.nadia@university.edu.my',
                'staffGrade' => 'VK7-A',
                'role' => 'management',
                'position' => 'Coordinator (s)',
                'department' => 'GMM',
                'researchGroup' => 'VISIC',
                'hometown' => ['address' => 'No. 7, Jalan Bundusan, Penampang', 'city' => 'Kota Kinabalu', 'state' => 'Sabah', 'postcode' => '88300', 'lat' => 5.9788, 'lng' => 116.0753],
            ],

            // CY Department (Cybersecurity) - 3 lecturers
            [
                'name' => 'Prof. Dr. Zainal Abidin bin Mohamed',
                'email' => 'zainal.abidin@university.edu.my',
                'staffGrade' => 'DS54-A',
                'role' => 'management',
                'position' => 'Head of Programs',
                'department' => 'CY',
                'researchGroup' => 'Cy-SIG',
                'hometown' => ['address' => 'No. 64, Jalan Setia Tropika 1/1', 'city' => 'Johor Bahru', 'state' => 'Johor', 'postcode' => '81200', 'lat' => 1.5397, 'lng' => 103.6360],
            ],
            [
                'name' => 'Dr. Liew Chin Yee',
                'email' => 'liew.chinyee@university.edu.my',
                'staffGrade' => 'DS52-A',
                'role' => 'non-management',
                'position' => 'Committee',
                'department' => 'CY',
                'researchGroup' => 'Cy-SIG',
                'hometown' => ['address' => 'No. 29, Jalan SS15/4d, Subang Jaya', 'city' => 'Subang Jaya', 'state' => 'Selangor', 'postcode' => '47500', 'lat' => 3.0738, 'lng' => 101.5881],
            ],
            [
                'name' => 'Dr. Chong Wei Ming',
                'email' => 'chong.weiming@university.edu.my',
                'staffGrade' => 'DS51-A',
                'role' => 'management',
                'position' => 'Coordinator (s)',
                'department' => 'CY',
                'researchGroup' => 'SCORE',
                'hometown' => ['address' => 'No. 82, Jalan Bunga Raya, Taman Sri Indah', 'city' => 'Alor Setar', 'state' => 'Kedah', 'postcode' => '05100', 'lat' => 6.1184, 'lng' => 100.3681],
            ],
        ];

        $count = 0;
        foreach ($lecturers as $index => $data) {
            // Generate lecturer ID (starting from LC2001)
            $lecturerID = 'LC' . str_pad($index + 2001, 4, '0', STR_PAD_LEFT);

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

            // Assign roles based on position
            $isCoordinator = in_array($data['position'], ['Coordinator (s)', 'Dean', 'Deputy Dean(R)', 'Deputy Dean(A)']);
            $isCommittee = in_array($data['position'], ['Committee', 'Dean', 'Deputy Dean(R)']);
            $isAcademicAdvisor = $index % 3 === 0; // 33% are academic advisors

            // Random semester (1 or 2) and year 2025
            $semester = rand(1, 2);
            $year = 2025;

            // Get program based on department and research group
            $program = $this->getProgram($data['department'], $data['researchGroup']);

            // Create lecturer profile
            $lecturer = Lecturer::create([
                'lecturerID' => $lecturerID,
                'user_id' => $user->id,
                'staffGrade' => $data['staffGrade'],
                'role' => $data['role'],
                'position' => $data['position'],
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
                'status' => Lecturer::STATUS_ACTIVE,
                'isSupervisorFaculty' => true,
                'supervisor_quota' => $quota,
                'current_assignments' => 0,
                'isAcademicAdvisor' => $isAcademicAdvisor,
                'isCommittee' => $isCommittee,
                'isCoordinator' => $isCoordinator,
                'isAdmin' => false,
                'travel_preference' => ['local', 'nationwide'][rand(0, 1)],
                'program' => $program,
            ]);

            $rolesText = [];
            if ($lecturer->isSupervisorFaculty) $rolesText[] = 'Supervisor';
            if ($lecturer->isCommittee) $rolesText[] = 'Committee';
            if ($lecturer->isCoordinator) $rolesText[] = 'Coordinator';
            if ($lecturer->isAcademicAdvisor) $rolesText[] = 'Academic Advisor';

            $this->command->info(
                "Created {$lecturerID}: {$data['name']} | " .
                "Grade: {$data['staffGrade']} | " .
                "Role: {$data['role']} | " .
                "Position: {$data['position']} | " .
                "Dept: {$data['department']} | " .
                "Research: {$data['researchGroup']} | " .
                "Hometown: {$data['hometown']['city']}, {$data['hometown']['state']} | " .
                "Semester: {$semester}/{$year} | " .
                "Quota: {$quota} | " .
                "Roles: " . implode(', ', $rolesText)
            );
            $count++;
        }

        $this->command->info('');
        $this->command->info('✅ Supervisor lecturers created successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Total supervisors created: ' . $count);
        $this->command->info('   - Total supervisors in system: ' . Lecturer::where('isSupervisorFaculty', true)->count());
        $this->command->info('   - Average quota per supervisor: ' . round(Lecturer::where('isSupervisorFaculty', true)->avg('supervisor_quota'), 1));
        $this->command->info('   - Total available slots: ' . Lecturer::where('isSupervisorFaculty', true)->sum('supervisor_quota'));

        $this->command->info('');
        $this->command->info('📋 Breakdown by Department:');
        $deptCounts = Lecturer::where('isSupervisorFaculty', true)
            ->selectRaw('department, COUNT(*) as count')
            ->groupBy('department')
            ->get();
        foreach ($deptCounts as $dept) {
            $deptName = match($dept->department) {
                'CS' => 'Computer Science',
                'SN' => 'Systems & Networking',
                'GMM' => 'Games & Multimedia',
                'CY' => 'Cybersecurity',
                default => $dept->department,
            };
            $this->command->info("   - {$deptName} ({$dept->department}): {$dept->count} supervisors");
        }

        $this->command->info('');
        $this->command->info('🔑 Test Credentials (password: password):');
        $this->command->info('   - ahmad.fadzli@university.edu.my (CS - SERG)');
        $this->command->info('   - siti.hajar@university.edu.my (CS - DSSIM)');
        $this->command->info('   - lim.weijian@university.edu.my (CS - CSRG)');
        $this->command->info('   - mohd.azlan@university.edu.my (SN - CNRG)');
        $this->command->info('   - zainal.abidin@university.edu.my (CY - Cy-SIG)');
    }

    /**
     * Get program based on department and research group
     */
    private function getProgram(string $department, string $researchGroup): ?string
    {
        // Map research groups to programs
        // BCS - Software Engineering
        // BCN - Computer Systems & Networking
        // BCM - Multimedia Software
        // BCY - Cyber Security
        // DRC - Diploma in Computer Science
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
