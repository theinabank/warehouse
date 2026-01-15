<?php

namespace Tests\Feature;

use App\Exceptions\ProductUpdateException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ProductService $productService;
    private ProductRepository $productRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = Mockery::mock(ProductRepository::class);

        $this->productService = new ProductService(
            $this->productRepository
        );
    }

    public function testUpdateQuantityWthNoItems_throwsException(): void
    {
        $this->expectException(ProductUpdateException::class);
        $this->expectExceptionMessage("Malformed request");

        $this->productService->updateQuantity();
    }

    public function testUpdateQuantityMalformedRequest_throwsException(): void
    {
        $this->expectException(ProductUpdateException::class);
        $this->expectExceptionMessage("Malformed request");

        $this->productService->updateQuantity([
            ['quantity' => 1],
            ['id' => 2],
            [],
        ]);
    }

    public function testUpdateQuantityFloat_throwsException(): void
    {
        $this->expectException(ProductUpdateException::class);
        $this->expectExceptionMessage("Product with ID 1 has invalid quantity");

        $this->productService->updateQuantity([
            ['id' => 1, 'quantity' => 3.6]
        ]);
    }

    public function testUpdateQuantityString_throwsException(): void
    {
        $this->expectException(ProductUpdateException::class);
        $this->expectExceptionMessage("Product with ID 1 has invalid quantity");

        $this->productService->updateQuantity([
            ['id' => 1, 'quantity' => 'asdf']
        ]);
    }

    public function testUpdateQuantityWithNonExistantItem_throwsException(): void
    {
        $this->productRepository->shouldReceive('getProductsByIdsForUpdate')
            ->once()
            ->andReturn(new Collection([]));

        $this->expectException(ProductUpdateException::class);
        $this->expectExceptionMessage("Some of the product id's provided were not found: 1,2");

        $this->productService->updateQuantity([
            ['id' => 1, 'quantity' => 1],
            ['id' => 2, 'quantity' => 1],
        ]);
    }

    public function testUpdateQuantity_quantityUpdated(): void
    {
        $product = Mockery::mock(Product::class)->makePartial();
        $product->id = 1;
        $product->quantity = 5;

        $this->productRepository->shouldReceive('getProductsByIdsForUpdate')
            ->once()
            ->andReturn(new Collection([$product]));

        $this->productRepository->shouldReceive('incrementProductQuantity')->once();

        $product->shouldReceive('refresh')->once();

        $this->productService->updateQuantity([
            ['id' => 1, 'quantity' => 2],
        ]);
    }
}
