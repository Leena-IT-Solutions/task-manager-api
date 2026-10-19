<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin / Director
        $admin = User::updateOrCreate(
            ['email' => 'sandeep198558@gmail.com'],
            [
                'name' => 'Sandeep Rathod',
                'mobile' => '9664588677',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'designation' => 'Managing Director & Super Admin',
                'department' => 'Executive',
                'reporting_to_id' => null,
                'is_active' => true,
            ]
        );

        // 2. Manager reporting to Sandeep Rathod
        $manager = User::updateOrCreate(
            ['email' => 'leenaadam28@gmail.com'],
            [
                'name' => 'Leena Adam',
                'mobile' => '9769409405',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'designation' => 'Operations Manager',
                'department' => 'Operations',
                'reporting_to_id' => $admin->id,
                'is_active' => true,
            ]
        );

        // 3. Team Lead reporting to Leena Adam
        $lead = User::updateOrCreate(
            ['email' => 'lead@taskmanager.local'],
            [
                'name' => 'Rahul Sharma',
                'mobile' => '9876543210',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'designation' => 'Team Lead',
                'department' => 'Operations',
                'reporting_to_id' => $manager->id,
                'is_active' => true,
            ]
        );

        // 4. Staff reporting to Team Lead
        User::updateOrCreate(
            ['email' => 'staff1@taskmanager.local'],
            [
                'name' => 'Pooja Verma',
                'mobile' => '9876543211',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'designation' => 'Field Executive',
                'department' => 'Operations',
                'reporting_to_id' => $lead->id,
                'is_active' => true,
            ]
        );

        // 5. Staff reporting to Leena Adam
        User::updateOrCreate(
            ['email' => 'staff2@taskmanager.local'],
            [
                'name' => 'Amit Patel',
                'mobile' => '9876543212',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'designation' => 'Senior Associate',
                'department' => 'Operations',
                'reporting_to_id' => $manager->id,
                'is_active' => true,
            ]
        );
    }
}
