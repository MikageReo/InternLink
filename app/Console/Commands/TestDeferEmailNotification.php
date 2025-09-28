<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\RequestDefer;
use App\Mail\RequestDeferStatusNotification;

class TestDeferEmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:defer-email {request_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test defer request email notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requestId = $this->argument('request_id');

        if ($requestId) {
            // Test with specific request
            $request = RequestDefer::with(['student.user', 'committee', 'coordinator'])
                ->find($requestId);

            if (!$request) {
                $this->error("Defer request with ID {$requestId} not found.");
                return 1;
            }
        } else {
            // Test with first available request or create a mock one
            $request = RequestDefer::with(['student.user', 'committee', 'coordinator'])->first();

            if (!$request) {
                $this->error('No defer requests found in the database. Please create a defer request first.');
                return 1;
            }
        }

        $this->info("Testing email notification for defer request #{$request->deferID}");
        $this->info("Student: {$request->student->user->name} ({$request->student->user->email})");
        $this->info("Status: Committee={$request->committeeStatus}, Coordinator={$request->coordinatorStatus}");

        try {
            // Send the email
            Mail::to($request->student->user->email)
                ->send(new RequestDeferStatusNotification($request));

            $this->info('✅ Email notification sent successfully!');
            $this->info('📧 Check your mail logs or configured mail service for the email.');

            if (config('mail.default') === 'log') {
                $this->warn('📝 Mail is configured to use "log" driver. Check storage/logs/laravel.log for the email content.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email notification:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
