<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductRepository
{
    public function getProductById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function getProductByIdForUpdate(int $id): ?Product
    {
        return Product::whereKey($id)->lockForUpdate()->first();
    }

    public function getProductsByIdsForUpdate(array $ids): Collection
    {
        return Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');
    }

    public function decrementProductQuantity(Product $product, int $quantity = 1): bool
    {
        return $product->decrement('quantity', $quantity) > 0;
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }
}