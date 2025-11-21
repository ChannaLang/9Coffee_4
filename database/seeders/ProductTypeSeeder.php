<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\ProductType;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Drink', 'Food','Can'];

        foreach ($types as $type) {
            ProductType::firstOrCreate(['name' => $type]);
        }
    }
}
