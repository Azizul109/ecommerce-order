<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Product;
    public function create(array $data): Product;
    public function update(Product $product, array $data): Product;
    public function delete(Product $product): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function search(string $query): LengthAwarePaginator;
    public function getLowStockProducts(): Collection;
    public function getVendorProducts(int $vendorId): LengthAwarePaginator;
}
