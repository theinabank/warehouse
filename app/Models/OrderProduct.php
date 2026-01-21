<?php

namespace App\Models;

use App\Support\PriceFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'quantity',
    ];

    protected $appends = ['price_euros', 'line_total', 'line_total_euros'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getPriceEurosAttribute(): string
    {
        return PriceFormatter::formatInEur($this->price);
    }

    public function getLineTotalAttribute(): int
    {
        return $this->price * $this->quantity;
    }

    public function getLineTotalEurosAttribute(): string
    {
        return PriceFormatter::formatInEur($this->price * $this->quantity);
    }
}
