<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreInvoiceLineRequest;
use App\Http\Resources\InvoiceLineResource;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Policies\Api\InvoicePolicy as ApiInvoicePolicy;
use App\Policies\Api\InvoiceLinePolicy as ApiInvoiceLinePolicy;

class InvoiceLineController extends ApiController
{
    public function __construct()
    {
        $this->authorizeResource(InvoiceLine::class, 'invoice_line');
    }
    public function index(Invoice $invoice)
    {
        $this->authorizeInvoice('view', $invoice);

        return $this->success(InvoiceLineResource::collection($invoice->invoiceLines));
    }

    public function store(StoreInvoiceLineRequest $request, Invoice $invoice)
    {
        $this->authorizeInvoice('view', $invoice);

        $invoiceLine = $invoice->invoiceLines()->create($request->validated());

        return $this->success(new InvoiceLineResource($invoiceLine), '',201);
    }

    public function show(InvoiceLine $invoiceLine)
    {
        $this->authorizeInvoiceLine('view', $invoiceLine);

        return $this->success(new InvoiceLineResource($invoiceLine));
    }

    public function update(InvoiceLine $invoiceLine, StoreInvoiceLineRequest $request)
    {
        $this->authorizeInvoiceLine('update', $invoiceLine);

        $invoiceLine->update($request->validated());

        return $this->success(new InvoiceLineResource($invoiceLine));
    }

    public function destroy(InvoiceLine $invoiceLine)
    {
        $this->authorizeInvoiceLine('delete', $invoiceLine);

        $invoiceLine->delete();

        return response()->noContent();
    }

    protected function authorizeInvoice(string $method, $invoice = null)
    {
        $policy = new ApiInvoicePolicy();

        if (! $policy->$method(request()->user(), $invoice)) {
            abort(403, 'Unauthorized.');
        }
    }

    protected function authorizeInvoiceLine(string $method, $invoiceLine = null)
    {
        $policy = new ApiInvoiceLinePolicy();

        if (! $policy->$method(request()->user(), $invoiceLine)) {
            abort(403, 'Unauthorized.');
        }
    }
}
