<?php

use App\Enums\Currency;
use App\Enums\InvoiceDirection;
use App\Http\Requests\StoreInvoiceRequest;
use Illuminate\Support\Facades\Validator;

test('validates title is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('title'))->toBeTrue();
});

test('validates external_id is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('external_id'))->toBeTrue();
});

test('validates title is string', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['title' => 123], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('title'))->toBeTrue();
});

test('validates title max length is 255', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['title' => str_repeat('a', 256)], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('title'))->toBeTrue();
});

test('validates direction is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('direction'))->toBeTrue();
});

test('validates direction is valid enum', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['direction' => 'invalid'], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('direction'))->toBeTrue();
});

test('accepts valid direction enum values', function () {
    $request = new StoreInvoiceRequest();

    foreach (InvoiceDirection::cases() as $direction) {
        $validator = Validator::make(
            [
                'title' => 'Test',
                'external_id' => 'EXT-001',
                'direction' => $direction->value,
                'issue_date' => '2025-01-01',
                'due_date' => '2025-02-01',
                'currency' => Currency::EUR->value,
                'invoice_lines' => [['vat' => 100, 'total' => 100]],
            ],
            $request->rules()
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validates issue_date is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('issue_date'))->toBeTrue();
});

test('validates issue_date format is Y-m-d', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['issue_date' => '01-01-2025'], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('issue_date'))->toBeTrue();
});

test('accepts valid issue_date format', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'title' => 'Test',
            'external_id' => 'EXT-001',
            'direction' => InvoiceDirection::Incoming->value,
            'issue_date' => '2025-01-01',
            'due_date' => '2025-02-01',
            'currency' => Currency::EUR->value,
            'invoice_lines' => [['vat' => 100, 'total' => 100]],
        ],
        $request->rules()
    );

    expect($validator->passes())->toBeTrue();
});

test('validates due_date is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('due_date'))->toBeTrue();
});

test('validates due_date format is Y-m-d', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['due_date' => '2025/02/01'], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('due_date'))->toBeTrue();
});

test('validates currency is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('currency'))->toBeTrue();
});

test('validates currency is valid enum', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['currency' => 'JPY'], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('currency'))->toBeTrue();
});

test('accepts valid currency enum values', function () {
    $request = new StoreInvoiceRequest();

    foreach (Currency::cases() as $currency) {
        $validator = Validator::make(
            [
                'title' => 'Test',
                'external_id' => 'EXT-001',
                'direction' => InvoiceDirection::Incoming->value,
                'issue_date' => '2025-01-01',
                'due_date' => '2025-02-01',
                'currency' => $currency->value,
                'invoice_lines' => [['vat' => 100, 'total' => 100]],
            ],
            $request->rules()
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validates invoice_lines is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines'))->toBeTrue();
});

test('validates invoice_lines is array', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['invoice_lines' => 'not-an-array'], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines'))->toBeTrue();
});

test('validates invoice_lines has minimum one item', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(['invoice_lines' => []], $request->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines'))->toBeTrue();
});

test('validates invoice_lines description is string', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'title' => 'Test',
            'external_id' => 'EXT-001',
            'direction' => InvoiceDirection::Incoming->value,
            'issue_date' => '2025-01-01',
            'due_date' => '2025-02-01',
            'currency' => Currency::EUR->value,
            'invoice_lines' => [
                ['description' => 123, 'vat' => 100, 'total' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.description'))->toBeTrue();
});

test('validates invoice_lines unit_price is integer', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['unit_price' => 'not-an-integer', 'vat' => 100, 'total' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.unit_price'))->toBeTrue();
});

test('validates invoice_lines number is integer', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['number' => 'not-an-integer', 'vat' => 100, 'total' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.number'))->toBeTrue();
});

test('validates invoice_lines number minimum is 1', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['number' => 0, 'vat' => 100, 'total' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.number'))->toBeTrue();
});

test('validates invoice_lines vat_rate is integer', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['vat_rate' => 'not-an-integer', 'vat' => 100, 'total' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.vat_rate'))->toBeTrue();
});

test('validates invoice_lines vat is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['total' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.vat'))->toBeTrue();
});

test('validates invoice_lines vat is integer', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['vat' => 'not-an-integer', 'total' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.vat'))->toBeTrue();
});

test('validates invoice_lines total is required', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['vat' => 100],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.total'))->toBeTrue();
});

test('validates invoice_lines total is integer', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'invoice_lines' => [
                ['vat' => 100, 'total' => 'not-an-integer'],
            ],
        ],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('invoice_lines.0.total'))->toBeTrue();
});

test('validates multiple invoice_lines independently', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'title' => 'Test',
            'external_id' => 'EXT-001',
            'direction' => InvoiceDirection::Incoming->value,
            'issue_date' => '2025-01-01',
            'due_date' => '2025-02-01',
            'currency' => Currency::EUR->value,
            'invoice_lines' => [
                ['description' => 'Product 1', 'unit_price' => 10000, 'number' => 1, 'vat_rate' => 21, 'vat' => 2100, 'total' => 10000],
                ['description' => 'Product 2', 'unit_price' => 5000, 'number' => 2, 'vat_rate' => 6, 'vat' => 600, 'total' => 10000],
            ],
        ],
        $request->rules()
    );

    expect($validator->passes())->toBeTrue();
});

test('request is authorized by default', function () {
    $request = new StoreInvoiceRequest();

    expect($request->authorize())->toBeTrue();
});

test('description is optional', function () {
    $request = new StoreInvoiceRequest();
    $validator = Validator::make(
        [
            'title' => 'Test',
            'external_id' => 'EXT-001',
            'direction' => InvoiceDirection::Incoming->value,
            'issue_date' => '2025-01-01',
            'due_date' => '2025-02-01',
            'currency' => Currency::EUR->value,
            'invoice_lines' => [['vat' => 100, 'total' => 100]],
            // description is not provided
        ],
        $request->rules()
    );

    expect($validator->passes())->toBeTrue();
});
