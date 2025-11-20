<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(): Collection
    {
        return Product::with(['category', 'vendor', 'variants'])->get();
    }

    public function find(int $id): ?Product
    {
        return Product::with(['category', 'vendor', 'variants'])->find($id);
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);

            if (isset($data['variants'])) {
                $product->variants()->createMany($data['variants']);
            }

            return $product->load(['category', 'vendor', 'variants']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            if (isset($data['variants'])) {
                $product->variants()->delete();
                $product->variants()->createMany($data['variants']);
            }

            return $product->load(['category', 'vendor', 'variants']);
        });
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'vendor', 'variants'])
            ->where('is_active', true)
            ->paginate($perPage);
    }

    public function search(string $query): LengthAwarePaginator
    {
        return Product::with(['category', 'vendor', 'variants'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhereHas('category', function ($q) use ($query) {
                      $q->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->paginate(15);
    }

    public function getLowStockProducts(): Collection
    {
        return Product::with(['category', 'vendor'])
            ->where('is_active', true)
            ->whereRaw('stock_quantity <= low_stock_threshold')
            ->get();
    }

    public function getVendorProducts(int $vendorId): LengthAwarePaginator
    {
        return Product::with(['category', 'variants'])
            ->where('vendor_id', $vendorId)
            ->paginate(15);
    }
}
