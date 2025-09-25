<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(InvoiceResource::collection(Invoice::all()));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = Invoice::create($request->all());
        return $this->success(new InvoiceResource($invoice));
    }

    public function show(Invoice $invoice)
    {
        return $this->success(new InvoiceResource($invoice));
    }

    public function update(Invoice $invoice, StoreInvoiceRequest $request)
    {
        $invoice->update($request->all());
        return $this->success(new InvoiceResource($invoice));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->noContent();
    }
}
