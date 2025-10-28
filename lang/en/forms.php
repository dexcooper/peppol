<?php

return [
    'company' => [
        'general' => __('fields.general'),
        'first_name' => __('fields.first_name'),
        'name' => __('fields.name'),
        'email'  => __('fields.email'),
        'vat_number'  => 'VAT number',
        'contact_person' => __('fields.contact_person'),
        'address' => __('fields.address'),
        'street' => __('fields.street'),
        'number' => __('fields.number'),
        'zip_code' => __('fields.zip_code'),
        'city' => __('fields.city'),
        'country' => __('fields.country'),
        'peppol' => 'Peppol',
        'peppol_provider' => 'Peppol provider',
    ],
    'user' => [
        'name' => __('fields.name'),
        'email' => __('fields.email'),
        'password' => __('fields.password'),
        'company' => __('resources.company.label'),
    ],
    'invoice' => [
        'details' => __('fields.details'),
        'company' => __('resources.company.label'),
        'title' => __('fields.title'),
        'description' => __('fields.description'),
        'other' => __('fields.other'),
        'status' => __('fields.status'),
        'total_amount' => 'Total',
        'direction' => 'Direction',
        'issue_date' => 'Issue date',
        'due_date' => 'Due date',
    ],
    'invoice_line' => [
        'description' => __('fields.description'),
        'number' => __('fields.number'),
        'unit_price' => 'Unit price',
        'total_amount' => __('fields.total_amount'),
        'vat_rate' => 'BTW percentage',
    ]
];
