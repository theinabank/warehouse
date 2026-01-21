<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'total_price' => 0,
        ];
    }

    public function withProducts(int $min = 1, int $max = 3)
    {
        return $this->afterCreating(function (Order $order) use ($min, $max) {
            DB::transaction(function () use ($order, $min, $max) {
                $totalPrice = 0;

                $products = Product::query()
                    ->where('quantity', '>', 0)
                    ->inRandomOrder()
                    ->limit(rand($min, $max))
                    ->lockForUpdate()
                    ->get();

                if ($products->isEmpty()) {
                    throw new \RuntimeException('No products with stock available');
                }

                foreach ($products as $product) {
                    $quantityTaken = rand(1, min(5, $product->quantity));

                    $product->decrement('quantity', $quantityTaken);

                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'price' => $product->price,
                        'quantity' => $quantityTaken,
                    ]);

                    $totalPrice += $product->price * $quantityTaken;
                }

                $order->update([
                    'total_price' => $totalPrice,
                ]);
            });
        });
    }
}
