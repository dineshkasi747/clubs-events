<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@college.edu',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 20 realistic club names
        $clubNames = [
            'Coding Club',
            'Robotics Club',
            'Music Society',
            'Astronomy Club',
            'Debate Club',
            'Drama Society',
            'Green Earth Club',
            'Finance & Investment Club',
            'Art & Photography Club',
            'Literature Club',
            'Chess Guild',
            'Film & Media Society',
            'Biotech Association',
            'Sports Club',
            'Gaming League',
            'Dance Troupe',
            'Culinary Arts Club',
            'Fashion Design Club',
            'Aerospace Club',
            'Mathematics Circle'
        ];

        // 2. Create 20 Presidents and Clubs
        foreach ($clubNames as $index => $name) {
            $num = $index + 1;
            
            // Create President
            $president = User::create([
                'name' => "President $num ($name)",
                'email' => "president$num@college.edu",
                'password' => Hash::make('password'),
                'role' => 'president',
            ]);

            // Create Club
            $club = Club::create([
                'name' => $name,
                'description' => "Welcome to the $name. Join us for weekly workshops, guest lectures, and exciting college events!",
                'logo' => "/storage/logos/club_$num.png",
                'president_id' => $president->id,
            ]);

            // Update President with their Club ID
            $president->update([
                'club_id' => $club->id,
            ]);
        }

        // 3. Create some dummy students for registration testing
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Student Student$i",
                'email' => "student$i@college.edu",
                'password' => Hash::make('password'),
                'role' => 'student',
            ]);
        }
    }
}
