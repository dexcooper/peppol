<?php

return [
    'company' => [
        'general' => __('fields.general'),
        'first_name' => __('fields.first_name'),
        'name' => __('fields.name'),
        'email'  => __('fields.email'),
        'vat_number'  => 'BTW nummer',
        'contact_person' => __('fields.contact_person'),
        'address' => __('fields.address'),
        'street' => __('fields.street'),
        'number' => __('fields.number'),
        'zip_code' => __('fields.zip_code'),
        'city' => __('fields.city'),
        'country' => __('fields.country'),
        'peppol' => 'Peppol',
        'provider' => 'Provider',
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
        'total_amount' => 'Totaal',
        'direction' => 'Richting',
        'issue_date' => 'Uitgiftedatum',
        'due_date' => 'Vervaldatum',
    ],
    'invoice_line' => [
        'description' => __('fields.description'),
        'number' => __('fields.number'),
        'unit_price' => 'Eenheidsprijs',
        'total_amount' => __('fields.total_amount'),
        'vat_rate' => 'BTW percentage',
    ]
];
