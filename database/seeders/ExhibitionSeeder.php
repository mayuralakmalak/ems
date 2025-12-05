<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use App\Models\PaymentSchedule;
use App\Models\BadgeConfiguration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExhibitionSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitions = [
            [
                'name' => 'India Tech Expo 2024',
                'description' => 'The largest technology exhibition in India showcasing cutting-edge innovations, AI, IoT, and digital transformation solutions.',
                'venue' => 'Bombay Exhibition Centre',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(33),
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'price_per_sqft' => 500.00,
                'raw_price_per_sqft' => 400.00,
                'orphand_price_per_sqft' => 600.00,
                'side_1_open_percent' => 0,
                'side_2_open_percent' => 5,
                'side_3_open_percent' => 10,
                'side_4_open_percent' => 15,
                'premium_price' => 800.00,
                'standard_price' => 500.00,
                'economy_price' => 300.00,
                'addon_services_cutoff_date' => now()->addDays(20),
                'document_upload_deadline' => now()->addDays(25),
                'initial_payment_percent' => 10,
                'status' => 'active',
            ],
            [
                'name' => 'Global Business Summit 2024',
                'description' => 'International business conference and exhibition connecting global leaders, entrepreneurs, and innovators.',
                'venue' => 'Pragati Maidan',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'start_date' => now()->addDays(60),
                'end_date' => now()->addDays(63),
                'start_time' => '10:00:00',
                'end_time' => '19:00:00',
                'price_per_sqft' => 600.00,
                'raw_price_per_sqft' => 500.00,
                'orphand_price_per_sqft' => 700.00,
                'side_1_open_percent' => 0,
                'side_2_open_percent' => 5,
                'side_3_open_percent' => 10,
                'side_4_open_percent' => 15,
                'premium_price' => 1000.00,
                'standard_price' => 600.00,
                'economy_price' => 400.00,
                'addon_services_cutoff_date' => now()->addDays(50),
                'document_upload_deadline' => now()->addDays(55),
                'initial_payment_percent' => 15,
                'status' => 'active',
            ],
            [
                'name' => 'Startup Innovation Fest 2024',
                'description' => 'Celebrating innovation and entrepreneurship with startups, investors, and industry leaders.',
                'venue' => 'Bangalore International Exhibition Centre',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'country' => 'India',
                'start_date' => now()->addDays(90),
                'end_date' => now()->addDays(92),
                'start_time' => '09:30:00',
                'end_time' => '17:30:00',
                'price_per_sqft' => 450.00,
                'raw_price_per_sqft' => 350.00,
                'orphand_price_per_sqft' => 550.00,
                'side_1_open_percent' => 0,
                'side_2_open_percent' => 5,
                'side_3_open_percent' => 10,
                'side_4_open_percent' => 15,
                'premium_price' => 700.00,
                'standard_price' => 450.00,
                'economy_price' => 300.00,
                'addon_services_cutoff_date' => now()->addDays(80),
                'document_upload_deadline' => now()->addDays(85),
                'initial_payment_percent' => 10,
                'status' => 'active',
            ],
        ];

        foreach ($exhibitions as $exhibitionData) {
            $exhibition = Exhibition::create($exhibitionData);

            // Create payment schedules
            PaymentSchedule::create([
                'exhibition_id' => $exhibition->id,
                'part_number' => 1,
                'percentage' => 10,
                'due_date' => now()->addDays(10),
            ]);
            PaymentSchedule::create([
                'exhibition_id' => $exhibition->id,
                'part_number' => 2,
                'percentage' => 40,
                'due_date' => now()->addDays(20),
            ]);
            PaymentSchedule::create([
                'exhibition_id' => $exhibition->id,
                'part_number' => 3,
                'percentage' => 50,
                'due_date' => now()->addDays(30),
            ]);

            // Create badge configurations
            BadgeConfiguration::create([
                'exhibition_id' => $exhibition->id,
                'badge_type' => 'Primary',
                'quantity' => 2,
                'pricing_type' => 'Free',
                'price' => 0,
                'needs_admin_approval' => false,
            ]);
            BadgeConfiguration::create([
                'exhibition_id' => $exhibition->id,
                'badge_type' => 'Secondary',
                'quantity' => 3,
                'pricing_type' => 'Free',
                'price' => 0,
                'needs_admin_approval' => false,
            ]);
            BadgeConfiguration::create([
                'exhibition_id' => $exhibition->id,
                'badge_type' => 'Additional',
                'quantity' => 5,
                'pricing_type' => 'Paid',
                'price' => 500.00,
                'needs_admin_approval' => true,
                'access_permissions' => json_encode(['Entry Only', 'Lunch', 'Snacks']),
            ]);
        }
    }
}
