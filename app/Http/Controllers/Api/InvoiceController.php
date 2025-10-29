<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Policies\Api\InvoicePolicy as ApiInvoicePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InvoiceController extends ApiController
{
    public function __construct()
    {
//        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index()
    {
        return $this->success(InvoiceResource::collection(Invoice::query()->where('company_id', Auth::user()->company_id)->get()));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $this->authorizeInvoice('create');

        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $invoice = Invoice::create($data);

        foreach ($request['invoice_lines'] as $invoiceLine) $invoice->invoiceLines()->create($invoiceLine);

        return $this->success(new InvoiceResource($invoice), '', 201);
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice('view', $invoice);

        return $this->success(new InvoiceResource($invoice));
    }

    public function update(Invoice $invoice, StoreInvoiceRequest $request)
    {
        $this->authorizeInvoice('update', $invoice);

        $invoice->update($request->validated());
        if ($request->has('invoice_lines')) {
            $invoice->invoiceLines()->delete();
            foreach ($request['invoice_lines'] as $invoiceLine) $invoice->invoiceLines()->create($invoiceLine);
        }

        return $this->success(new InvoiceResource($invoice));
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorizeInvoice('delete', $invoice);

        $invoice->delete();
        return response()->noContent();
    }

    protected function authorizeInvoice(string $method, $invoice = null)
    {
        $policy = new ApiInvoicePolicy();

        if (! $policy->$method(request()->user(), $invoice)) {
            abort(403, 'Unauthorized.');
        }
    }
}
