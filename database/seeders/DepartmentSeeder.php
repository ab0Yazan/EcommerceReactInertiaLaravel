<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            "Fashion & Apparel",
            "Home & Living",
            "Electronics & Gadgets",
            "Health & Beauty",
            "Baby & Kids",
            "Food & Beverage",
            "Fitness & Sports",
            "Travel & Outdoor",
            "Gifts & Novelty",
            "Tools & DIY",
            "Pet Supplies",
            "Books & Learning"
        ];

        $data=[];
        foreach ($departments as $department) {
            $data[]=[
                'name' => $department,
                'slug' => Str::slug($department),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('departments')->insert($data);

    }
}
