<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use App\Models\Booth;
use Illuminate\Database\Seeder;

class BoothSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitions = Exhibition::all();

        foreach ($exhibitions as $exhibition) {
            // Create booths for each exhibition
            $boothNames = ['A1', 'A2', 'A3', 'A4', 'A5', 'B1', 'B2', 'B3', 'B4', 'B5', 'C1', 'C2', 'C3', 'D1', 'D2', 'D3', 'E1', 'E2', 'E3'];
            
            foreach ($boothNames as $index => $name) {
                $size = [9, 12, 15, 18, 20][$index % 5]; // Varying sizes
                $sidesOpen = [1, 2, 3, 4][$index % 4]; // Varying sides
                $category = ['Premium', 'Standard', 'Economy'][$index % 3];
                $boothType = ['Raw', 'Orphand'][$index % 2];
                
                // Calculate price based on exhibition pricing
                $basePrice = $boothType === 'Raw' ? $exhibition->raw_price_per_sqft : $exhibition->orphand_price_per_sqft;
                $sideMultiplier = 1 + ($exhibition->{'side_' . $sidesOpen . '_open_percent'} / 100);
                $categoryPrice = $exhibition->{strtolower($category) . '_price'};
                $finalPrice = ($basePrice * $size * $sideMultiplier) + ($categoryPrice ?? 0);

                Booth::create([
                    'exhibition_id' => $exhibition->id,
                    'name' => $name,
                    'category' => $category,
                    'booth_type' => $boothType,
                    'size_sqft' => $size,
                    'sides_open' => $sidesOpen,
                    'price' => round($finalPrice, 2),
                    'is_free' => false,
                    'is_available' => true,
                    'is_booked' => false,
                    'coordinates' => json_encode(['x' => ($index % 5) * 100, 'y' => floor($index / 5) * 100]),
                ]);
            }
        }
    }
}
