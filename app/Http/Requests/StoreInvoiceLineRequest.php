<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceLineRequest extends FormRequest
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
            'description' => 'string',
            'unit_price' => 'required|integer|min:0',
            'number' => 'required|integer|min:1',
            'total_amount' => 'required|integer|min:0',
            'vat_rate' => 'required|integer|min:0',
        ];
    }
}
