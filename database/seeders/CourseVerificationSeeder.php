<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseVerification;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CourseVerificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing students and lecturers
        $students = Student::with('user')->take(10)->get();
        $lecturer = Lecturer::first();

        if ($students->isEmpty()) {
            $this->command->error('No students found! Please seed students first.');
            return;
        }

        if (!$lecturer) {
            $this->command->error('No lecturers found! Please seed lecturers first.');
            return;
        }

        $this->command->info('Creating course verification test data...');

        // Create dummy PDF directory if it doesn't exist
        $dummyPath = 'course-verification-files';
        if (!Storage::disk('public')->exists($dummyPath)) {
            Storage::disk('public')->makeDirectory($dummyPath);
        }

        $statuses = ['pending', 'pending', 'pending', 'pending', 'approved', 'rejected'];
        $credits = [90, 95, 100, 105, 110, 115, 120, 125, 85, 80];

        foreach ($students as $index => $student) {
            $status = $statuses[$index % count($statuses)];
            
            // Create course verification
            $verification = CourseVerification::create([
                'studentID' => $student->studentID,
                'lecturerID' => $lecturer->lecturerID,
                'currentCredit' => $credits[$index],
                'status' => $status,
                'applicationDate' => Carbon::now()->subDays(rand(1, 30)),
                'remarks' => $status === 'approved' 
                    ? 'Approved - Credit requirements met' 
                    : ($status === 'rejected' ? 'Rejected - Insufficient credits' : null),
            ]);

            // Create a dummy PDF file
            $fileName = "course_verification_{$student->studentID}_" . time() . "_" . $index . ".pdf";
            $filePath = $dummyPath . '/' . $fileName;
            
            // Create a simple PDF content
            $pdfContent = $this->generateDummyPdfContent($student, $verification);
            Storage::disk('public')->put($filePath, $pdfContent);

            // Create file record
            File::create([
                'fileable_id' => $verification->courseVerificationID,
                'fileable_type' => 'App\Models\CourseVerification',
                'file_path' => $filePath,
                'original_name' => "Course_Verification_{$student->studentID}.pdf",
                'file_size' => strlen($pdfContent),
                'mime_type' => 'application/pdf',
            ]);

            $this->command->info("Created verification for {$student->user->name} ({$student->studentID}) - Status: {$status}");
        }

        $this->command->info('✅ Course verification test data created successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Total verifications: ' . CourseVerification::count());
        $this->command->info('   - Pending: ' . CourseVerification::where('status', 'pending')->count());
        $this->command->info('   - Approved: ' . CourseVerification::where('status', 'approved')->count());
        $this->command->info('   - Rejected: ' . CourseVerification::where('status', 'rejected')->count());
    }

    /**
     * Generate dummy PDF content
     */
    private function generateDummyPdfContent($student, $verification): string
    {
        // Simple PDF-like content (not a real PDF, but works for testing)
        // In production, you'd use a proper PDF library
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
/Length 200
>>
stream
BT
/F1 12 Tf
50 700 Td
(COURSE VERIFICATION DOCUMENT) Tj
0 -30 Td
(Student: {$student->user->name}) Tj
0 -20 Td
(Student ID: {$student->studentID}) Tj
0 -20 Td
(Current Credits: {$verification->currentCredit}) Tj
0 -20 Td
(Application Date: {$verification->applicationDate->format('Y-m-d')}) Tj
0 -20 Td
(Status: {$verification->status}) Tj
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
585
%%EOF";
    }
}





