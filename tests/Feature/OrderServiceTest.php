<?php

namespace Tests\Feature;

use App\Exceptions\OrderCreateException;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\OrderProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private OrderService $orderService;
    private OrderRepository $orderRepository;
    private OrderProductRepository $orderProductRepository;
    private ProductRepository $productRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = Mockery::mock(OrderRepository::class);
        $this->orderProductRepository = Mockery::mock(OrderProductRepository::class);
        $this->productRepository = Mockery::mock(ProductRepository::class);

        $this->orderService = new OrderService(
            $this->orderRepository,
            $this->orderProductRepository,
            $this->productRepository
        );
    }

    private function getMockProduct()
    {
        $product = Mockery::mock(Product::class)->makePartial();
        $product->id = 1;
        $product->quantity = 5;

        return $product;
    }

    public function testCreateWithNoItems_throwsException(): void
    {
        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Malformed request");

        $this->orderService->create();
    }

    public function testCreateMalformedRequest_throwsException(): void
    {
        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Malformed request");

        $this->orderService->create([
            ['quantity' => 1],
            ['id' => 2,],
            [],
        ]);
    }

    public function testCreateWithNonExistantItem_throwsException(): void
    {
        DB::shouldReceive('beginTransaction')->once();

        $this->orderRepository->shouldReceive('createOrder')->once();
        $this->productRepository->shouldReceive('getProductsByIdsForUpdate')
            ->once()
            ->andReturn(new Collection([]));

        DB::shouldReceive('rollBack')->once();

        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Some of the product id's provided were not found: 1,2");

        $this->orderService->create([
            ['id' => 1, 'quantity' => 1],
            ['id' => 2, 'quantity' => 1],
        ]);
    }

    public function testCreateWithNoSufficientItemQuantity_throwsException(): void
    {
        DB::shouldReceive('beginTransaction')->once();

        $this->orderRepository->shouldReceive('createOrder')->once();
        $this->productRepository->shouldReceive('getProductsByIdsForUpdate')
            ->once()
            ->andReturn(new Collection([$this->getMockProduct()]));

        DB::shouldReceive('rollBack')->once();

        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Product with ID 1 does not have sufficient quantity.");

        $this->orderService->create([
            ['id' => 1, 'quantity' => 6]
        ]);
    }

    public function testCreateWithNoSufficientItemQuantityAndNonExistantItem_throwsException(): void
    {
        DB::shouldReceive('beginTransaction')->once();

        $this->orderRepository->shouldReceive('createOrder')->once();
        $this->productRepository->shouldReceive('getProductsByIdsForUpdate')
            ->once()
            ->andReturn(new Collection([$this->getMockProduct()]));

        DB::shouldReceive('rollBack')->once();

        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Some of the product id's provided were not found: 0");

        $this->orderService->create([
            ['id' => 0, 'quantity' => 6],
            ['id' => 1, 'quantity' => 6]
        ]);
    }

    public function testCreateWithZeroQuantity_throwsException(): void
    {
        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Product with ID 1 has invalid quantity");

        $this->orderService->create([
            ['id' => 1, 'quantity' => 0]
        ]);
    }

    public function testCreateWithQuantityTypeString_throwsException(): void
    {
        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Product with ID 1 has invalid quantity");

        $this->orderService->create([
            ['id' => 1, 'quantity' => 'asdf']
        ]);
    }

    public function testCreateWithQuantityTypeFloat_throwsException(): void
    {
        $this->expectException(OrderCreateException::class);
        $this->expectExceptionMessage("Product with ID 1 has invalid quantity");

        $this->orderService->create([
            ['id' => 1, 'quantity' => 3.6]
        ]);
    }

    public function testCreateOrder_orderCreated(): void
    {
        $order = Mockery::mock(Order::class)->makePartial();
        $order->id = 1;

        DB::shouldReceive('beginTransaction')->once();

        $this->orderRepository->shouldReceive('createOrder')
            ->once()
            ->andReturn($order);

        $this->productRepository->shouldReceive('getProductsByIdsForUpdate')
            ->once()
            ->andReturn(new Collection([$this->getMockProduct()]));

        $this->productRepository->shouldReceive('decrementProductQuantity')->once();

        $this->orderProductRepository->shouldReceive('createOrderProduct')->once();

        $this->orderRepository->shouldReceive('updateOrder')->once();

        DB::shouldReceive('commit')->once();

        $this->orderService->create([
            ['id' => 1, 'quantity' => 2]
        ]);
    }
}
