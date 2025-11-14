<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Coffee', 'Tea', 'Snack'];

        foreach ($types as $type) {
            ProductType::firstOrCreate(['name' => $type]);
        }
    }
}
