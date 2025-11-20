<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create vendor users
        $vendor1 = User::create([
            'name' => 'Vendor One',
            'email' => 'vendor1@example.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        $vendor2 = User::create([
            'name' => 'Vendor Two',
            'email' => 'vendor2@example.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        // Create customer users
        User::create([
            'name' => 'Customer One',
            'email' => 'customer1@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        User::create([
            'name' => 'Customer Two',
            'email' => 'customer2@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // Create categories
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Home & Garden', 'description' => 'Home improvement and garden supplies'],
            ['name' => 'Sports', 'description' => 'Sports equipment and accessories'],
            ['name' => 'Books', 'description' => 'Books and educational materials'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create products
        $products = [
            [
                'vendor_id' => $vendor1->id,
                'category_id' => 1,
                'name' => 'Smartphone X',
                'description' => 'Latest smartphone with advanced features',
                'price' => 699.99,
                'stock_quantity' => 50,
                'low_stock_threshold' => 5,
                'is_active' => true,
            ],
            [
                'vendor_id' => $vendor1->id,
                'category_id' => 1,
                'name' => 'Wireless Headphones',
                'description' => 'Noise-cancelling wireless headphones',
                'price' => 199.99,
                'stock_quantity' => 100,
                'low_stock_threshold' => 10,
                'is_active' => true,
            ],
            [
                'vendor_id' => $vendor2->id,
                'category_id' => 2,
                'name' => 'Cotton T-Shirt',
                'description' => 'Comfortable cotton t-shirt',
                'price' => 24.99,
                'stock_quantity' => 200,
                'low_stock_threshold' => 20,
                'is_active' => true,
            ],
            [
                'vendor_id' => $vendor2->id,
                'category_id' => 2,
                'name' => 'Denim Jeans',
                'description' => 'Classic denim jeans',
                'price' => 59.99,
                'stock_quantity' => 75,
                'low_stock_threshold' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Vendor: vendor1@example.com / password');
        $this->command->info('Customer: customer1@example.com / password');
    }
}
