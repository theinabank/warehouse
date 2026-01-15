<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'getAllProducts']);
    Route::get('/products/in-stock', [ProductController::class, 'getProductsInStock']);

    Route::put('/products/add-quantity', [ProductController::class, 'addQuantityToExistingProducts']);

    Route::post('/orders', [OrderController::class, 'create']);
});
