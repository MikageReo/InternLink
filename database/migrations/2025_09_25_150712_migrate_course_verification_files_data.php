<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing course verification files to the new files table
        $courseVerifications = DB::table('course_verifications')
            ->whereNotNull('submittedFile')
            ->where('submittedFile', '!=', '')
            ->get();

        foreach ($courseVerifications as $verification) {
            // Get file information
            $filePath = $verification->submittedFile;
            $originalName = basename($filePath);
            $fileSize = null;
            $mimeType = null;

            // Try to get file info if file exists
            if (Storage::disk('public')->exists($filePath)) {
                $fileSize = Storage::disk('public')->size($filePath);
                $mimeType = Storage::disk('public')->mimeType($filePath);
            }

            // Create file record
            DB::table('files')->insert([
                'fileable_id' => $verification->courseVerificationID,
                'fileable_type' => 'App\\Models\\CourseVerification',
                'file_path' => $filePath,
                'original_name' => $originalName,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all file records related to course verifications
        DB::table('files')
            ->where('fileable_type', 'App\\Models\\CourseVerification')
            ->delete();
    }
};
