<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $orderData = [
                'customer_id' => auth()->id(),
                'total_amount' => 0,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $order = Order::create($orderData);
            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (isset($item['product_variant_id'])) {
                    $variant = ProductVariant::where('id', $item['product_variant_id'])
                        ->where('product_id', $product->id)
                        ->firstOrFail();

                    $unitPrice = $variant->price;
                    $availableStock = $variant->stock_quantity;
                } else {
                    $unitPrice = $product->price;
                    $availableStock = $product->stock_quantity;
                }

                if ($availableStock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for product: {$product->name}"
                    ]);
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                ]);

                $totalAmount += $orderItem->total_price;

                // Deduct inventory
                $this->updateInventory(
                    $product,
                    $variant ?? null,
                    $item['quantity'],
                    'out',
                    "Order #{$order->order_number}"
                );
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items.product', 'items.variant');
        });
    }

    public function updateOrderStatus(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $oldStatus = $order->status;
            $order->update(['status' => $status]);

            if ($status === 'cancelled' && in_array($oldStatus, ['pending', 'processing'])) {
                $this->restoreOrderInventory($order);
            }

            return $order->load('items.product', 'items.variant');
        });
    }

    private function updateInventory(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        string $type,
        string $reason
    ): void {
        if ($variant) {
            $oldQuantity = $variant->stock_quantity;
            $newQuantity = $type === 'out'
                ? $oldQuantity - $quantity
                : $oldQuantity + $quantity;

            $variant->update(['stock_quantity' => $newQuantity]);

            $variant->inventoryLogs()->create([
                'type' => $type,
                'quantity' => $quantity,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);
        } else {
            $oldQuantity = $product->stock_quantity;
            $newQuantity = $type === 'out'
                ? $oldQuantity - $quantity
                : $oldQuantity + $quantity;

            $product->update(['stock_quantity' => $newQuantity]);

            $product->inventoryLogs()->create([
                'type' => $type,
                'quantity' => $quantity,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);
        }
    }

    private function restoreOrderInventory(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            $variant = $item->variant;

            $this->updateInventory(
                $product,
                $variant,
                $item->quantity,
                'in',
                "Order cancellation #{$order->order_number}"
            );
        }
    }

    public function getCustomerOrders(int $customerId)
    {
        return Order::with('items.product', 'items.variant')
            ->where('customer_id', $customerId)
            ->latest()
            ->paginate(15);
    }

    public function getVendorOrders(int $vendorId)
    {
        return Order::with('items.product', 'items.variant')
            ->whereHas('items.product', function ($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->latest()
            ->paginate(15);
    }
}
