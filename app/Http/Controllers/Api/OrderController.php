<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\OrderCreateException;
use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class OrderController extends BaseController
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function create(OrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->create(
                $request->validated()['products']
            );

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('orderProducts')
            ], 201);
        } catch (OrderCreateException $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'errors' => $e->getErrorMessages()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'error' => 'Something went wrong',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }
}