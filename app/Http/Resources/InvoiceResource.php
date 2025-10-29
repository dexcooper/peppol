<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'company' => new CompanyResource($this->company),
            'externalId' => $this->external_id,
            'vatNumber' => $this->vat_number,
            'title' => $this->title,
            'description' => $this->description,
            'direction' => $this->direction,
            'peppolId' => $this->peppol_id,
            'status' => $this->status,
            'issueDate' => $this->issue_date,
            'dueDate' => $this->due_date,
            'currency' => $this->currency,
            'invoiceLines' => $this->invoiceLines ? InvoiceLineResource::collection($this->invoiceLines) : null,
        ];
    }
}
