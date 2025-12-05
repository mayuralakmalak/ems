<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitions = Exhibition::all();

        $services = [
            ['name' => 'Premium Chair', 'type' => 'chair', 'price' => 500, 'description' => 'Comfortable premium chair with armrest'],
            ['name' => 'Standard Chair', 'type' => 'chair', 'price' => 300, 'description' => 'Standard conference chair'],
            ['name' => 'Round Table', 'type' => 'table', 'price' => 800, 'description' => 'Round table for meetings'],
            ['name' => 'Rectangular Table', 'type' => 'table', 'price' => 1000, 'description' => 'Large rectangular table'],
            ['name' => 'High-Speed Internet', 'type' => 'internet', 'price' => 2000, 'description' => 'Dedicated high-speed internet connection'],
            ['name' => 'WiFi Access', 'type' => 'internet', 'price' => 500, 'description' => 'WiFi access for booth'],
            ['name' => 'Electrical Connection', 'type' => 'electricity', 'price' => 1500, 'description' => 'Electrical power supply'],
            ['name' => 'Premium Lighting', 'type' => 'lighting', 'price' => 1200, 'description' => 'Professional lighting setup'],
            ['name' => 'Catering Service', 'type' => 'catering', 'price' => 2500, 'description' => 'Full catering service for booth'],
            ['name' => 'Coffee Service', 'type' => 'catering', 'price' => 800, 'description' => 'Coffee and refreshments'],
        ];

        foreach ($exhibitions as $exhibition) {
            foreach ($services as $serviceData) {
                Service::create([
                    'exhibition_id' => $exhibition->id,
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'],
                    'type' => $serviceData['type'],
                    'price' => $serviceData['price'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
