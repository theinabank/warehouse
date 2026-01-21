<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'price' => $this->price,
            'price_euros' => $this->price_euros,
            'quantity_taken' => $this->quantity,
            'line_total' => $this->line_total,
            'line_total_euros' => $this->line_total_euros,
        ];
    }
}
