<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlacementApplication;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\CourseVerification;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PlacementApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get students who have approved course verification
        $students = Student::with('user')
            ->whereHas('courseVerifications', function($query) {
                $query->where('status', 'approved');
            })
            ->take(20)
            ->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students with approved course verification found!');
            $this->command->info('Creating course verifications first...');
            
            // Get any students
            $students = Student::with('user')->take(20)->get();
            
            if ($students->isEmpty()) {
                $this->command->error('No students found! Please seed students first.');
                return;
            }
            
            // Create approved course verifications for them
            $lecturer = Lecturer::first();
            if (!$lecturer) {
                $this->command->error('No lecturers found! Please seed lecturers first.');
                return;
            }
            
            foreach ($students as $student) {
                CourseVerification::create([
                    'studentID' => $student->studentID,
                    'lecturerID' => $lecturer->lecturerID,
                    'currentCredit' => rand(130, 140),
                    'status' => 'approved',
                    'applicationDate' => Carbon::now()->subDays(rand(30, 60)),
                    'remarks' => 'Approved - Credit requirements met',
                ]);
            }
            $this->command->info('Created course verifications for students.');
        }

        // Get committee members and coordinators
        $committeeMembers = Lecturer::where('isCommittee', true)->get();
        $coordinators = Lecturer::where('isCoordinator', true)->get();

        if ($committeeMembers->isEmpty()) {
            $this->command->warn('No committee members found! Using first lecturer as committee.');
            $committeeMembers = collect([Lecturer::first()]);
        }

        if ($coordinators->isEmpty()) {
            $this->command->warn('No coordinators found! Using first lecturer as coordinator.');
            $coordinators = collect([Lecturer::first()]);
        }

        $this->command->info('Creating placement application test data...');

        // Create dummy files directory
        $dummyPath = 'placement-application-files';
        if (!Storage::disk('public')->exists($dummyPath)) {
            Storage::disk('public')->makeDirectory($dummyPath);
        }

        // Realistic Malaysian companies
        $companies = [
            ['name' => 'Maybank Berhad', 'email' => 'hr@maybank.com', 'phone' => '+603-2070 8833'],
            ['name' => 'CIMB Bank Berhad', 'email' => 'recruitment@cimb.com', 'phone' => '+603-2261 8888'],
            ['name' => 'Petronas Digital Sdn Bhd', 'email' => 'careers@petronas.com', 'phone' => '+603-2331 3000'],
            ['name' => 'Grab Malaysia', 'email' => 'jobs@grab.com', 'phone' => '+603-2770 6800'],
            ['name' => 'Shopee Malaysia', 'email' => 'hr.my@shopee.com', 'phone' => '+603-7890 3000'],
            ['name' => 'Axiata Group Berhad', 'email' => 'careers@axiata.com', 'phone' => '+603-2263 8888'],
            ['name' => 'Fusionex International', 'email' => 'hr@fusionex-international.com', 'phone' => '+603-7803 1188'],
            ['name' => 'Intel Malaysia', 'email' => 'recruitment.malaysia@intel.com', 'phone' => '+604-6438 000'],
            ['name' => 'Dell Technologies Malaysia', 'email' => 'careers.malaysia@dell.com', 'phone' => '+603-7953 8000'],
            ['name' => 'Accenture Malaysia', 'email' => 'malaysia.recruitment@accenture.com', 'phone' => '+603-2714 2000'],
            ['name' => 'DHL Express Malaysia', 'email' => 'hr.malaysia@dhl.com', 'phone' => '+603-7884 1888'],
            ['name' => 'PwC Malaysia', 'email' => 'careers.my@pwc.com', 'phone' => '+603-2173 1188'],
            ['name' => 'Deloitte Malaysia', 'email' => 'myrecruit@deloitte.com', 'phone' => '+603-7610 8888'],
            ['name' => 'KPMG Malaysia', 'email' => 'recruitment@kpmg.com.my', 'phone' => '+603-7721 3388'],
            ['name' => 'IBM Malaysia', 'email' => 'hr.malaysia@ibm.com', 'phone' => '+603-2301 8000'],
        ];

        $positions = [
            ['title' => 'Software Developer Intern', 'scope' => "• Assist in developing web applications using modern frameworks\n• Collaborate with senior developers on software projects\n• Participate in code reviews and testing activities\n• Document development processes and create technical documentation\n• Learn and implement best practices in software development"],
            ['title' => 'Data Analyst Intern', 'scope' => "• Analyze business data and create reports using Excel and BI tools\n• Assist in data collection, cleaning, and validation\n• Create visualizations and dashboards for stakeholders\n• Support data-driven decision making processes\n• Learn SQL and data analytics tools"],
            ['title' => 'Network Engineer Intern', 'scope' => "• Assist in network infrastructure setup and maintenance\n• Monitor network performance and troubleshoot issues\n• Configure routers, switches, and firewalls\n• Document network configurations and procedures\n• Support IT team in network security implementations"],
            ['title' => 'UI/UX Designer Intern', 'scope' => "• Design user interfaces for web and mobile applications\n• Conduct user research and create user personas\n• Develop wireframes, mockups, and prototypes\n• Collaborate with developers on design implementation\n• Perform usability testing and gather feedback"],
            ['title' => 'Cybersecurity Intern', 'scope' => "• Assist in security audits and vulnerability assessments\n• Monitor security systems and respond to incidents\n• Learn about security protocols and best practices\n• Support in security awareness training programs\n• Document security procedures and policies"],
            ['title' => 'Digital Marketing Intern', 'scope' => "• Assist in social media content creation and management\n• Support SEO and SEM campaigns\n• Analyze digital marketing metrics and create reports\n• Collaborate on email marketing campaigns\n• Research market trends and competitor analysis"],
            ['title' => 'IT Support Intern', 'scope' => "• Provide technical support to users via phone, email, and in-person\n• Troubleshoot hardware and software issues\n• Install and configure computer systems and applications\n• Maintain IT inventory and documentation\n• Escalate complex issues to senior technicians"],
            ['title' => 'Mobile App Developer Intern', 'scope' => "• Assist in developing mobile applications for iOS/Android\n• Learn mobile development frameworks (Flutter, React Native)\n• Test applications on various devices\n• Fix bugs and optimize app performance\n• Collaborate with design team on UI implementation"],
            ['title' => 'Database Administrator Intern', 'scope' => "• Assist in database design and optimization\n• Perform database backups and recovery procedures\n• Monitor database performance and troubleshoot issues\n• Write SQL queries for data extraction and reporting\n• Support in data migration projects"],
            ['title' => 'DevOps Intern', 'scope' => "• Learn CI/CD pipeline setup and maintenance\n• Assist in server deployment and configuration\n• Monitor application performance and uptime\n• Support containerization using Docker\n• Document deployment processes and procedures"],
        ];

        $addresses = [
            ['street' => 'Menara Maybank, 100 Jalan Tun Perak', 'city' => 'Kuala Lumpur', 'postcode' => '50050', 'state' => 'Kuala Lumpur'],
            ['street' => 'Menara CIMB, Jalan Stesen Sentral 2', 'city' => 'Kuala Lumpur', 'postcode' => '50470', 'state' => 'Kuala Lumpur'],
            ['street' => 'Tower 1, Petronas Twin Towers', 'city' => 'Kuala Lumpur', 'postcode' => '50088', 'state' => 'Kuala Lumpur'],
            ['street' => 'Bangsar South, No. 8, Jalan Kerinchi', 'city' => 'Kuala Lumpur', 'postcode' => '59200', 'state' => 'Kuala Lumpur'],
            ['street' => 'Menara Lien Hoe, Persiaran Tropicana', 'city' => 'Petaling Jaya', 'postcode' => '47410', 'state' => 'Selangor'],
            ['street' => 'Axiata Tower, 9 Jalan Stesen Sentral 5', 'city' => 'Kuala Lumpur', 'postcode' => '50470', 'state' => 'Kuala Lumpur'],
            ['street' => 'Level 21, Menara OBYU, No. 4, Jalan PJU 8/8A', 'city' => 'Petaling Jaya', 'postcode' => '47820', 'state' => 'Selangor'],
            ['street' => 'Bayan Lepas Free Industrial Zone', 'city' => 'Bayan Lepas', 'postcode' => '11900', 'state' => 'Penang'],
            ['street' => 'Menara TH Uptown 5, Jalan SS 21/39', 'city' => 'Petaling Jaya', 'postcode' => '47400', 'state' => 'Selangor'],
            ['street' => 'Level 8, Plaza 33, No. 1, Jalan Kemajuan', 'city' => 'Petaling Jaya', 'postcode' => '46200', 'state' => 'Selangor'],
        ];

        $methods = ['WFO', 'WFH', 'WFO & WFH', 'WOS', 'WOC'];
        $allowances = [800, 1000, 1200, 1500, 0, 600, 900];

        // Status combinations for variety
        $statusCombinations = [
            // Pending applications
            ['committee' => 'Pending', 'coordinator' => 'Pending', 'student' => null],
            ['committee' => 'Pending', 'coordinator' => 'Pending', 'student' => null],
            ['committee' => 'Pending', 'coordinator' => 'Pending', 'student' => null],
            ['committee' => 'Pending', 'coordinator' => 'Pending', 'student' => null],
            ['committee' => 'Pending', 'coordinator' => 'Pending', 'student' => null],
            
            // Committee approved, coordinator pending
            ['committee' => 'Approved', 'coordinator' => 'Pending', 'student' => null],
            ['committee' => 'Approved', 'coordinator' => 'Pending', 'student' => null],
            ['committee' => 'Approved', 'coordinator' => 'Pending', 'student' => null],
            
            // Fully approved, waiting student acceptance
            ['committee' => 'Approved', 'coordinator' => 'Approved', 'student' => null],
            ['committee' => 'Approved', 'coordinator' => 'Approved', 'student' => null],
            
            // Approved and accepted by student
            ['committee' => 'Approved', 'coordinator' => 'Approved', 'student' => 'Accepted'],
            ['committee' => 'Approved', 'coordinator' => 'Approved', 'student' => 'Accepted'],
            
            // Approved but declined by student
            ['committee' => 'Approved', 'coordinator' => 'Approved', 'student' => 'Declined'],
            
            // Committee rejected
            ['committee' => 'Rejected', 'coordinator' => 'Rejected', 'student' => null],
            ['committee' => 'Rejected', 'coordinator' => 'Rejected', 'student' => null],
            
            // Coordinator rejected
            ['committee' => 'Approved', 'coordinator' => 'Rejected', 'student' => null],
        ];

        $count = 0;
        foreach ($students as $index => $student) {
            // Each student may have 1-2 applications
            $numApplications = rand(1, 2);
            
            for ($i = 0; $i < $numApplications; $i++) {
                $companyIndex = ($index + $i) % count($companies);
                $positionIndex = ($index + $i) % count($positions);
                $addressIndex = ($companyIndex) % count($addresses);
                $statusIndex = $count % count($statusCombinations);
                
                $company = $companies[$companyIndex];
                $position = $positions[$positionIndex];
                $address = $addresses[$addressIndex];
                $status = $statusCombinations[$statusIndex];
                
                $startDate = Carbon::now()->addDays(rand(30, 90));
                $endDate = (clone $startDate)->addMonths(rand(3, 6));
                
                // Create placement application
                $application = PlacementApplication::create([
                    'studentID' => $student->studentID,
                    'companyName' => $company['name'],
                    'companyAddressLine' => $address['street'],
                    'companyCity' => $address['city'],
                    'companyPostcode' => $address['postcode'],
                    'companyState' => $address['state'],
                    'companyCountry' => 'Malaysia',
                    'companyLatitude' => 3.1390 + (rand(-100, 100) / 1000), // Approximate KL area
                    'companyLongitude' => 101.6869 + (rand(-100, 100) / 1000),
                    'companyEmail' => $company['email'],
                    'companyNumber' => $company['phone'],
                    'allowance' => $allowances[array_rand($allowances)],
                    'position' => $position['title'],
                    'jobscope' => $position['scope'],
                    'methodOfWork' => $methods[array_rand($methods)],
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'applicationDate' => Carbon::now()->subDays(rand(1, 45)),
                    'committeeStatus' => $status['committee'],
                    'coordinatorStatus' => $status['coordinator'],
                    'studentAcceptance' => $status['student'],
                    'committeeID' => $status['committee'] !== 'Pending' ? $committeeMembers->random()->lecturerID : null,
                    'coordinatorID' => $status['coordinator'] !== 'Pending' ? $coordinators->random()->lecturerID : null,
                    'remarks' => $this->getRemarks($status),
                    'applyCount' => 1,
                ]);

                // Create 1-3 dummy files for each application
                $numFiles = rand(1, 3);
                $fileTypes = ['offer_letter.pdf', 'acceptance_form.pdf', 'company_profile.pdf', 'job_description.pdf'];
                
                for ($f = 0; $f < $numFiles; $f++) {
                    $fileName = "placement_{$application->applicationID}_" . time() . "_{$f}.pdf";
                    $filePath = $dummyPath . '/' . $fileName;
                    
                    // Create dummy PDF content
                    $pdfContent = $this->generateDummyPdfContent($student, $application, $company, $position);
                    Storage::disk('public')->put($filePath, $pdfContent);

                    // Create file record
                    File::create([
                        'fileable_id' => $application->applicationID,
                        'fileable_type' => 'App\Models\PlacementApplication',
                        'file_path' => $filePath,
                        'original_name' => $fileTypes[$f % count($fileTypes)],
                        'file_size' => strlen($pdfContent),
                        'mime_type' => 'application/pdf',
                    ]);
                }

                $statusText = "{$status['committee']} / {$status['coordinator']}";
                if ($status['student']) {
                    $statusText .= " / {$status['student']}";
                }
                
                $this->command->info("Created application #{$application->applicationID} for {$student->user->name} at {$company['name']} - Status: {$statusText}");
                $count++;
            }
        }

        $this->command->info('');
        $this->command->info('✅ Placement application test data created successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Total applications: ' . PlacementApplication::count());
        $this->command->info('   - Committee pending: ' . PlacementApplication::where('committeeStatus', 'Pending')->count());
        $this->command->info('   - Committee approved: ' . PlacementApplication::where('committeeStatus', 'Approved')->count());
        $this->command->info('   - Committee rejected: ' . PlacementApplication::where('committeeStatus', 'Rejected')->count());
        $this->command->info('   - Coordinator pending: ' . PlacementApplication::where('coordinatorStatus', 'Pending')->count());
        $this->command->info('   - Coordinator approved: ' . PlacementApplication::where('coordinatorStatus', 'Approved')->count());
        $this->command->info('   - Coordinator rejected: ' . PlacementApplication::where('coordinatorStatus', 'Rejected')->count());
        $this->command->info('   - Student accepted: ' . PlacementApplication::where('studentAcceptance', 'Accepted')->count());
        $this->command->info('   - Student declined: ' . PlacementApplication::where('studentAcceptance', 'Declined')->count());
    }

    /**
     * Get appropriate remarks based on status
     */
    private function getRemarks($status): ?string
    {
        if ($status['committee'] === 'Rejected') {
            $rejectionReasons = [
                'Company not recognized or not suitable for internship program.',
                'Job scope does not match program requirements.',
                'Insufficient company information provided.',
                'Position requires more experience than intern level.',
                'Company location too far from campus without proper accommodation arrangements.',
            ];
            return $rejectionReasons[array_rand($rejectionReasons)];
        }
        
        if ($status['coordinator'] === 'Rejected' && $status['committee'] === 'Approved') {
            return 'Duration of internship does not meet program minimum requirements.';
        }
        
        if ($status['committee'] === 'Approved' && $status['coordinator'] === 'Approved') {
            return 'Approved';
        }
        
        return null;
    }

    /**
     * Generate dummy PDF content
     */
    private function generateDummyPdfContent($student, $application, $company, $position): string
    {
        return "%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/Resources <<
/Font <<
/F1 4 0 R
>>
>>
/MediaBox [0 0 612 792]
/Contents 5 0 R
>>
endobj
4 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj
5 0 obj
<<
/Length 350
>>
stream
BT
/F1 14 Tf
50 750 Td
(INTERNSHIP PLACEMENT APPLICATION) Tj
0 -40 Td
/F1 12 Tf
(Student Information:) Tj
0 -25 Td
(Name: {$student->user->name}) Tj
0 -20 Td
(Student ID: {$student->studentID}) Tj
0 -20 Td
(Email: {$student->user->email}) Tj
0 -40 Td
(Company Information:) Tj
0 -25 Td
(Company: {$company['name']}) Tj
0 -20 Td
(Position: {$position['title']}) Tj
0 -20 Td
(Email: {$company['email']}) Tj
0 -20 Td
(Phone: {$company['phone']}) Tj
0 -40 Td
(Internship Period:) Tj
0 -25 Td
(Start Date: {$application->startDate->format('Y-m-d')}) Tj
0 -20 Td
(End Date: {$application->endDate->format('Y-m-d')}) Tj
0 -20 Td
(Method: {$application->methodOfWork}) Tj
0 -20 Td
(Allowance: RM {$application->allowance}) Tj
ET
endstream
endobj
xref
0 6
0000000000 65535 f
0000000015 00000 n
0000000074 00000 n
0000000131 00000 n
0000000252 00000 n
0000000333 00000 n
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
750
%%EOF";
    }
}

