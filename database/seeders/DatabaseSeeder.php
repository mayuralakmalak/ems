<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create core roles
        $roles = [
            'Admin',
            'Exhibitor',
            'Staff',
            'Sub Admin',
            'Visitor',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Create test admin user
        $admin = User::firstOrCreate(
            ['email' => 'asadm@alakmalak.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('123456'),
                'phone' => '+919876543210',
                'company_name' => 'Alakmalak Technologies',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
            ]
        );

        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        // Create test exhibitor users
        $exhibitors = [
            [
                'name' => 'Rajesh Kumar',
                'email' => 'rajesh@techcorp.com',
                'phone' => '+919876543211',
                'company_name' => 'TechCorp Solutions',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya@innovate.com',
                'phone' => '+919876543212',
                'company_name' => 'Innovate Industries',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'Amit Patel',
                'email' => 'amit@globaltech.com',
                'phone' => '+919876543213',
                'company_name' => 'Global Tech Solutions',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'country' => 'India',
                'password' => bcrypt('123456'),
            ],
        ];

        foreach ($exhibitors as $exhibitorData) {
            $exhibitor = User::firstOrCreate(
                ['email' => $exhibitorData['email']],
                $exhibitorData
            );
            if (!$exhibitor->hasRole('Exhibitor')) {
                $exhibitor->assignRole('Exhibitor');
            }
        }

        // Call other seeders
        $this->call([
            ExhibitionSeeder::class,
            BoothSeeder::class,
            ServiceSeeder::class,
        ]);
    }
}
