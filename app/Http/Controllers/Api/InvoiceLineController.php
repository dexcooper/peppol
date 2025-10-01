<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreInvoiceLineRequest;
use App\Http\Resources\InvoiceLineResource;
use App\Models\Invoice;
use App\Models\InvoiceLine;

class InvoiceLineController extends ApiController
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\InvoiceLine::class, 'invoice_line');
    }
    public function index(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return $this->success(InvoiceLineResource::collection($invoice->invoiceLines));
    }

    public function store(StoreInvoiceLineRequest $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoiceLine = $invoice->invoiceLines()->create($request->validated());

        return $this->success(new InvoiceLineResource($invoiceLine), '',201);
    }

    public function show(InvoiceLine $invoiceLine)
    {
        return $this->success(new InvoiceLineResource($invoiceLine));
    }

    public function update(InvoiceLine $invoiceLine, StoreInvoiceLineRequest $request)
    {
        $invoiceLine->update($request->validated());

        return $this->success(new InvoiceLineResource($invoiceLine));
    }

    public function destroy(InvoiceLine $invoiceLine)
    {
        $invoiceLine->delete();

        return response()->noContent();
    }
}
