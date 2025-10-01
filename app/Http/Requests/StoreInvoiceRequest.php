<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\InvoiceDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'external_id' => 'required|string',
            'description' => 'string',
            'direction' => ['required', new Enum(InvoiceDirection::class)],
            'issue_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d',
            'currency' => ['required', new Enum(Currency::class)],
            'invoice_lines' => 'required|array|min:1',
            'invoice_lines.*.description' => 'string',
            'invoice_lines.*.unit_price' => 'integer',
            'invoice_lines.*.number' => 'integer|min:1',
            'invoice_lines.*.vat_rate' => 'integer',
            'invoice_lines.*.vat' => 'required|integer',
            'invoice_lines.*.total' => 'required|integer',
        ];
    }
}
