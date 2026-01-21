<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AddQuantityProductsRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Routing\Controller as BaseController;

class ProductController extends BaseController
{
    private const DEFAULT_PER_PAGE = 20;

    public function __construct(
        private ProductService $productService
    ) {}

    private function paginatedResponse(CursorPaginator $products): JsonResponse
    {
        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'per_page' => $products->perPage(),
                'next_cursor' => optional($products->nextCursor())->encode(),
                'prev_cursor' => optional($products->previousCursor())->encode(),
            ],
        ]);
    }

    public function getAllProducts(ProductIndexRequest $request): JsonResponse
    {
        $perPage = $request->integer('per_page', self::DEFAULT_PER_PAGE);

        $products = Product::orderBy('id')
            ->cursorPaginate($perPage);

        return $this->paginatedResponse($products);
    }

    public function getProductsInStock(ProductIndexRequest $request): JsonResponse
    {
        $perPage = $request->integer('per_page', self::DEFAULT_PER_PAGE);

        $products = Product::where('quantity', '>', 0)
            ->orderBy('id')
            ->cursorPaginate($perPage);

        return $this->paginatedResponse($products);
    }

    public function addQuantityToExistingProducts(AddQuantityProductsRequest $request): JsonResponse
    {
        $updatedProducts = $updatedProducts = $this->productService->updateQuantity(
            $request->validated()['products']
        );

        return response()->json([
            'message' => 'Products updated successfully',
            'data' => ProductResource::collection($updatedProducts),
        ]);
    }
}