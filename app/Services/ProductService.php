<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function getAllProducts(array $filters = [])
    {
        return $this->productRepository->paginate();
    }

    public function getProduct(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            return $this->productRepository->create($data);
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            return $this->productRepository->update($product, $data);
        });
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    public function searchProducts(string $query)
    {
        return $this->productRepository->search($query);
    }

    public function importFromCsv(UploadedFile $file): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle);

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            try {
                $data = array_combine($headers, $row);

                $productData = [
                    'vendor_id' => auth()->id(),
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'stock_quantity' => $data['stock_quantity'],
                    'low_stock_threshold' => $data['low_stock_threshold'] ?? 10,
                    'is_active' => true,
                ];

                $this->createProduct($productData);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return $results;
    }

    public function getLowStockProducts()
    {
        return $this->productRepository->getLowStockProducts();
    }

    public function getVendorProducts(int $vendorId)
    {
        return $this->productRepository->getVendorProducts($vendorId);
    }
}
