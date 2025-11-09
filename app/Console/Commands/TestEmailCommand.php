<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email : The email address to send test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info('Testing email configuration...');
        $this->newLine();

        // Check mail configuration
        $mailer = config('mail.default');
        $host = config("mail.mailers.{$mailer}.host");
        $port = config("mail.mailers.{$mailer}.port");
        $from = config('mail.from.address');

        $this->info("Mail Configuration:");
        $this->line("  Mailer: {$mailer}");
        $this->line("  Host: {$host}");
        $this->line("  Port: {$port}");
        $this->line("  From: {$from}");
        $this->newLine();

        if ($mailer === 'log') {
            $this->warn('⚠️  Mail is set to LOG mode. Emails will not be sent!');
            $this->warn('   Please configure SMTP settings in your .env file.');
            $this->newLine();
            $this->info('See EMAIL_CONFIGURATION_GUIDE.md for setup instructions.');
            return self::FAILURE;
        }

        $this->info("Sending test email to: {$email}");

        try {
            Mail::raw('This is a test email from Internlink system. If you received this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email from Internlink');
            });

            $this->newLine();
            $this->info('✅ Email sent successfully!');
            $this->info("Please check {$email} inbox (and spam folder).");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->info('Troubleshooting:');
            $this->line('1. Check your SMTP credentials in .env file');
            $this->line('2. Run: php artisan config:clear');
            $this->line('3. Verify your email provider allows SMTP');
            $this->line('4. Check firewall/antivirus settings');
            $this->newLine();
            $this->info('See EMAIL_CONFIGURATION_GUIDE.md for detailed setup.');

            return self::FAILURE;
        }
    }
}
