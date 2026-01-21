<?php

namespace App\Models;

use App\Support\PriceFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'total_price',
    ];

    protected $appends = ['total_price_euros'];

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }

    public function getTotalPriceEurosAttribute(): string
    {
        return PriceFormatter::formatInEur($this->total_price);
    }
}
