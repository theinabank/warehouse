<?php

namespace App\Repositories;

use App\Models\OrderProduct;
use Illuminate\Support\Collection;

class OrderProductRepository
{
    public function getOrderProductById(int $id): ?OrderProduct
    {
        return OrderProduct::find($id);
    }

    public function getAllOrderProducts(): Collection
    {
        return OrderProduct::all();
    }

    public function createOrderProduct(array $data): OrderProduct
    {
        return OrderProduct::create($data);
    }

    public function updateOrderProduct(OrderProduct $orderProduct, array $data): bool
    {
        return $orderProduct->update($data);
    }

    public function deleteOrderProduct(OrderProduct $orderProduct): bool
    {
        return $orderProduct->delete();
    }
}