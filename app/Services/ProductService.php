<?php

namespace App\Services;

use App\Exceptions\ProductUpdateException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    private function validateInput(array $products = []): void
    {
        if (!$products) {
            throw new ProductUpdateException(['Malformed request']);
        }

        $productErrors = [];

        foreach ($products as $item) {
            if (!isset($item['id'], $item['quantity'])) {
                throw new ProductUpdateException(['Malformed request']);
            }

            $itemId = (int)$item['id'];
            $quantityTaken = (int)$item['quantity'];

            if ($quantityTaken < 1 || is_float($item['quantity'])) {
                $productErrors[] = "Product with ID {$itemId} has invalid quantity";

                continue;
            }
        }

        if ($productErrors) {
            throw new ProductUpdateException($productErrors);
        }
    }

    public function updateQuantity(array $products = []): Collection
    {
        $this->validateInput($products);

        $updatedProducts = collect();
        $productErrors = [];

        $productsForUpdate = $this->productRepository->getProductsByIdsForUpdate(
            array_map(
                static fn (array $product) => (int)$product['id'],
                $products
            )
        );

        if ($productsForUpdate->count() !== count($products)) {
            $idsNotFound = array_diff(
                array_map(static fn (array $p) => (int)$p['id'], $products),
                array_map(static fn (Product $p) => $p->id, $productsForUpdate->all())
            );

            $productErrors[] = "Some of the product id's provided were not found: " . implode(',', $idsNotFound);

            throw new ProductUpdateException($productErrors);
        }

        foreach ($productsForUpdate as $productForUpdate) {
            $quantity = Arr::first(
                $products,
                static fn (array $p) => (int)$p['id'] === $productForUpdate->id
                )['quantity'] ?? null;

            $this->productRepository->incrementProductQuantity($productForUpdate, $quantity);

            $productForUpdate->refresh();

            $updatedProducts->push($productForUpdate);
        }

        return $updatedProducts;
    }
}