<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'stock_quantity']
                ]
            ]);
    }

    public function test_vendor_can_create_product(): void
    {
        $vendor = User::factory()->vendor()->create();
        $token = auth()->login($vendor);

        $productData = [
            'category_id' => 1,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Product created successfully',
                'data' => [
                    'name' => 'Test Product',
                    'price' => 99.99,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ]
            ]);
    }

    public function test_vendor_can_update_own_product(): void
    {
        $vendor = User::factory()->vendor()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $token = auth()->login($vendor);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/v1/products/{$product->id}", [
                'name' => 'Updated Product Name',
                'description' => $product->description,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'category_id' => $product->category_id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product updated successfully',
                'data' => [
                    'name' => 'Updated Product Name',
                ]
            ]);
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'iPhone 15']);
        Product::factory()->create(['name' => 'Samsung Galaxy']);

        $response = $this->getJson('/api/v1/products/search?q=iPhone');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'iPhone 15'])
            ->assertJsonMissing(['name' => 'Samsung Galaxy']);
    }
}
