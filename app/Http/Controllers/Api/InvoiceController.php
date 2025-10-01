<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends ApiController
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Invoice::class, 'invoice');
    }

    public function index()
    {
        return $this->success(InvoiceResource::collection(Invoice::query()->where('company_id', Auth::user()->company_id)->get()));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $invoice = Invoice::create($data);

        foreach ($request['invoice_lines'] as $invoiceLine) $invoice->invoiceLines()->create($invoiceLine);

        return $this->success(new InvoiceResource($invoice), '', 201);
    }

    public function show(Invoice $invoice)
    {
        return $this->success(new InvoiceResource($invoice));
    }

    public function update(Invoice $invoice, StoreInvoiceRequest $request)
    {
        $invoice->update($request->validated());
        if ($request->has('invoice_lines')) {
            $invoice->invoiceLines()->delete();
            foreach ($request['invoice_lines'] as $invoiceLine) $invoice->invoiceLines()->create($invoiceLine);
        }

        return $this->success(new InvoiceResource($invoice));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->noContent();
    }
}
