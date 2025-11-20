<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_order(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $token = auth()->login($customer);

        $orderData = [
            'shipping_address' => [
                'street' => '123 Main St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Test Country',
            ],
            'billing_address' => [
                'street' => '123 Main St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Test Country',
            ],
            'customer_email' => $customer->email,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Order created successfully',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'order_number', 'total_amount', 'status']
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 8,
        ]);
    }

    public function test_cannot_create_order_with_insufficient_stock(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $token = auth()->login($customer);

        $orderData = [
            'shipping_address' => [
                'street' => '123 Main St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Test Country',
            ],
            'billing_address' => [
                'street' => '123 Main St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Test Country',
            ],
            'customer_email' => $customer->email,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_customer_can_view_own_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $token = auth()->login($customer);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'order_number', 'status', 'total_amount']
                ]
            ]);
    }

    public function test_customer_can_cancel_pending_order(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $token = auth()->login($customer);

        $orderData = [
            'shipping_address' => [
                'street' => '123 Main St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Test Country',
            ],
            'billing_address' => [
                'street' => '123 Main St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Test Country',
            ],
            'customer_email' => $customer->email,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ];

        $createResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/orders', $orderData);

        $orderId = $createResponse->json('data.id');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/v1/orders/{$orderId}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order cancelled successfully',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 10,
        ]);
    }
}
