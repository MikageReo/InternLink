<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestDefer;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class UpdateDeferredStudentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:update-deferred-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update student status from Deferred to Active after defer end date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        
        // Find all approved defer requests where end date has passed
        $expiredDeferRequests = RequestDefer::where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved')
            ->whereDate('endDate', '<', $today)
            ->with('student')
            ->get();

        $updatedCount = 0;

        foreach ($expiredDeferRequests as $request) {
            if ($request->student && $request->student->status === 'Deferred') {
                // Check if student has any other active (not expired) defer requests
                $hasActiveDeferRequest = RequestDefer::where('studentID', $request->studentID)
                    ->where('committeeStatus', 'Approved')
                    ->where('coordinatorStatus', 'Approved')
                    ->whereDate('endDate', '>=', $today)
                    ->exists();
                
                // Only update to Active if there are no other active defer requests
                if (!$hasActiveDeferRequest) {
                    $request->student->update(['status' => 'Active']);
                    $updatedCount++;
                    
                    Log::info("Updated student {$request->student->studentID} status from Deferred to Active. Defer request ID: {$request->deferID}, End date: {$request->endDate}");
                } else {
                    Log::info("Student {$request->student->studentID} still has active defer requests. Status remains Deferred. Expired defer request ID: {$request->deferID}");
                }
            }
        }

        if ($updatedCount > 0) {
            $this->info("Updated {$updatedCount} student(s) from Deferred to Active status.");
        } else {
            $this->info("No students needed status update.");
        }
        
        return Command::SUCCESS;
    }
}
