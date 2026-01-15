<?php

namespace App\Repositories;

use App\Models\Order;
use Generator;

class OrderRepository
{
    public function getOrderById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function createOrder(array $data): Order
    {
        return Order::create($data);
    }

    public function updateOrder(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function deleteOrder(Order $order): bool
    {
        return $order->delete();
    }
}