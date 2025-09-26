<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceLineRequest;
use App\Http\Resources\InvoiceLineResource;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Http\Request;

class InvoiceLineController extends Controller
{
    public function index(Invoice $invoice)
    {
        return InvoiceLineResource::collection($invoice->invoiceLines);
    }

    public function store(StoreInvoiceLineRequest $request, Invoice $invoice)
    {
        $invoiceLine = $invoice->invoiceLines()->create($request->validated());
        return new InvoiceLineResource($invoiceLine);
    }

    public function show(InvoiceLine $invoiceLine)
    {
        return new InvoiceLineResource($invoiceLine);
    }
}
