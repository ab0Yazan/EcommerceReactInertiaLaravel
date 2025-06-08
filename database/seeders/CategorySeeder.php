<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesByDepartment = [
            "Fashion & Apparel" => [
                "Men's Clothing" => ["T-Shirts", "Jeans", "Jackets"],
                "Women's Clothing" => ["Dresses", "Blouses", "Skirts"],
                "Shoes" => ["Sneakers", "Boots", "Sandals"],
                "Accessories" => ["Watches", "Bags", "Belts"]
            ],
            "Home & Living" => [
                "Furniture" => ["Sofas", "Chairs", "Tables"],
                "Kitchen & Dining" => ["Cookware", "Dinnerware", "Utensils"]
            ],
            "Electronics & Gadgets" => [
                "Smartphones & Accessories" => ["Chargers", "Phone Cases", "Screen Protectors"],
                "Laptops & Tablets" => ["MacBooks", "Windows Laptops", "iPads"]
            ],
            "Health & Beauty" => [
                "Skincare" => ["Cleansers", "Moisturizers", "Serums"],
                "Makeup" => ["Foundation", "Lipstick", "Mascara"]
            ],
            "Baby & Kids" => [
                "Toys" => ["Educational Toys", "Stuffed Animals", "Building Blocks"],
                "Feeding" => ["Bottles", "High Chairs", "Sippy Cups"]
            ],
            "Food & Beverage" => [
                "Beverages" => ["Coffee", "Tea", "Energy Drinks"],
                "Snacks" => ["Chips", "Protein Bars", "Cookies"]
            ],
            "Fitness & Sports" => [
                "Fitness Equipment" => ["Treadmills", "Dumbbells", "Resistance Bands"],
                "Supplements" => ["Protein Powder", "Creatine", "Pre-Workout"]
            ],
            "Travel & Outdoor" => [
                "Camping Gear" => ["Tents", "Sleeping Bags", "Camp Stoves"],
                "Luggage & Bags" => ["Suitcases", "Backpacks", "Duffel Bags"]
            ],
            "Gifts & Novelty" => [
                "Personalized Gifts" => ["Custom Mugs", "Engraved Jewelry", "Photo Frames"],
                "Gag Gifts" => ["Funny T-Shirts", "Prank Kits", "Novelty Toys"]
            ],
            "Tools & DIY" => [
                "Hand Tools" => ["Hammers", "Screwdrivers", "Wrenches"],
                "Power Tools" => ["Drills", "Saws", "Grinders"]
            ],
            "Pet Supplies" => [
                "Pet Food" => ["Dog Food", "Cat Food", "Treats"],
                "Grooming" => ["Brushes", "Shampoos", "Nail Clippers"]
            ],
            "Books & Learning" => [
                "Fiction" => ["Sci-Fi", "Mystery", "Romance"],
                "Textbooks" => ["Math", "Science", "History"]
            ]
        ];

        foreach ($categoriesByDepartment as $departmentName => $categories) {
            $department = Department::where('name', $departmentName)->first();

            if (!$department) {
                continue;
            }

            foreach ($categories as $parentName => $subcategories) {
                $parent = Category::create([
                    'department_id' => $department->id,
                    'parent_id' => null,
                    'name' => $parentName,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($subcategories as $childName) {
                    Category::create([
                        'department_id' => $department->id,
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
