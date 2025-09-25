<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\InvoiceDirection;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\Routing\Requirement\EnumRequirement;

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
            'company_id' => 'required|integer|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'string',
            'direction' => ['required', new Enum(InvoiceDirection::class)],
            'status' => ['required', new Enum(InvoiceStatus::class)],
            'issue_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d',
            'currency' => ['required', new Enum(Currency::class)],
            'total_amount' => 'required|integer',
        ];
    }
}
