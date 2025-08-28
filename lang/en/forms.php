<?php

return [
    'company' => [
        'name' => __('fields.name'),
        'vat_number'  => 'VAT number',
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
        'total_amount' => __('fields.total_amount'),
        'vat_rate' => 'BTW percentage',
    ]
];
