<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test student
        User::create([
            'name' => 'John Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // Create a test lecturer
        User::create([
            'name' => 'Dr. Jane Lecturer',
            'email' => 'lecturer@example.com',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
        ]);

        // Call other seeders
        $this->call([
            SupervisorLecturerSeeder::class,
            PlacementApplicationSeeder::class,
        ]);
    }
}
