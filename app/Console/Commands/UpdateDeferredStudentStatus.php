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
     * 
     * NOTE: This command has been disabled. Defer status should not be automatically changed to Active.
     * Students with defer status will be handled during next semester student creation process.
     */
    public function handle()
    {
        $this->info("This command has been disabled. Defer status changes are now handled during next semester student creation.");
        $this->warn("Students with defer status will be created in the special student database for next semester, but their defer records will be preserved.");
        
        return Command::SUCCESS;
    }
}
