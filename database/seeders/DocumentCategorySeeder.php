<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentCategory;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Certificates', 'slug' => 'certificate', 'status' => 'active', 'order' => 1],
            ['name' => 'Company registration documents', 'slug' => 'proof', 'status' => 'active', 'order' => 2],
            ['name' => 'Booth design files', 'slug' => 'design', 'status' => 'active', 'order' => 3],
            ['name' => 'Catalogs', 'slug' => 'catalog', 'status' => 'active', 'order' => 4],
            ['name' => 'Other required documents', 'slug' => 'other', 'status' => 'active', 'order' => 5],
        ];

        foreach ($categories as $category) {
            DocumentCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
