<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Medicine Purchase', 'description' => 'Purchase of medicines and pharmaceuticals'],
            ['name' => 'Medical Supplies', 'description' => 'Medical consumables and supplies'],
            ['name' => 'Equipment', 'description' => 'Medical and office equipment'],
            ['name' => 'Salary', 'description' => 'Staff salaries and wages'],
            ['name' => 'Rent', 'description' => 'Facility rent and lease payments'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet, phone'],
            ['name' => 'Maintenance', 'description' => 'Facility and equipment maintenance'],
            ['name' => 'Office Supplies', 'description' => 'Stationery and office consumables'],
            ['name' => 'Transportation', 'description' => 'Travel and delivery costs'],
            ['name' => 'Other', 'description' => 'Miscellaneous expenses'],
        ];

        foreach ($categories as $i => $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => \Illuminate\Support\Str::slug($category['name']),
                    'description' => $category['description'],
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }
    }
}
