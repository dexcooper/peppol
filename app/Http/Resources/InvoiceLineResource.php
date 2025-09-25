<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'description' => $this->description,
            'unitPrice' => $this->unit_price,
            'number' => $this->number,
            'totalAmount' => $this->total_amount,
            'vatRate' => $this->vat_rate,
        ];
    }
}
