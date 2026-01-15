<?php

namespace App\Services;

use App\Exceptions\OrderCreateException;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\OrderProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderProductRepository $orderProductRepository,
        private ProductRepository $productRepository
    ) {}

    private function validateInput(array $products = []): void
    {
        if (!$products) {
            throw new OrderCreateException(['Malformed request']);
        }

        $productErrors = [];

        foreach ($products as $item) {
            if (!isset($item['id'], $item['quantity'])) {
                throw new OrderCreateException(['Malformed request']);
            }

            $itemId = (int)$item['id'];
            $quantityTaken = (int)$item['quantity'];

            if ($quantityTaken < 1 || is_float($item['quantity'])) {
                $productErrors[] = "Product with ID {$itemId} has invalid quantity";

                continue;
            }
        }

        if ($productErrors) {
            throw new OrderCreateException($productErrors);
        }
    }

    public function create(array $products = []): Order
    {
        $this->validateInput($products);

        DB::beginTransaction();
        
        $totalPrice = 0;

        $order = $this->orderRepository->createOrder([
            'total_price' => $totalPrice,
        ]);

        $productsForOrder = $this->productRepository->getProductsByIdsForUpdate(
            array_map(
                static fn (array $product) => (int)$product['id'],
                $products
            )
        );

        $productErrors = [];

        if ($productsForOrder->count() !== count($products)) {
            $idsNotFound = array_diff(
                array_map(static fn (array $p) => (int)$p['id'], $products),
                array_map(static fn (Product $p) => $p->id, $productsForOrder->all())
            );

            $productErrors[] = "Some of the product id's provided were not found: " . implode(',', $idsNotFound);

            DB::rollBack();

            throw new OrderCreateException($productErrors);
        }

        foreach ($productsForOrder as $productForOrder) {
            $quantityTaken = Arr::first(
                $products,
                static fn (array $p) => (int)$p['id'] === $productForOrder->id
                )['quantity'] ?? null;

            if ($productForOrder->quantity < $quantityTaken) {
                $productErrors[] = "Product with ID {$productForOrder->id} does not have sufficient quantity.";

                continue;
            }

            $this->productRepository->decrementProductQuantity($productForOrder, $quantityTaken);

            $this->orderProductRepository->createOrderProduct([
                'order_id' => $order->id,
                'product_id' => $productForOrder->id,
                'price' => $productForOrder->price,
                'quantity' => $quantityTaken,
            ]);

            $totalPrice += $productForOrder->price * $quantityTaken;
        }

        if ($productErrors) {
            DB::rollBack();

            throw new OrderCreateException($productErrors);
        }

        $this->orderRepository->updateOrder($order, [
            'total_price' => number_format($totalPrice, 2, '.', '')
        ]);

        DB::commit();

        return $order;
    }
}