<?php

return [

    'required' => 'Gelieve een :attribute field op te geven',

    'custom' => [
        'name' => [
            'unique' => 'Deze naam is al in gebruik',
        ],
        'email' => [
            'unique' => 'Dit email adres is al in gebruik',
        ],
        'vat_number' => [
            'required' => 'Gelieve een BTW nummer op te geven',
            'unique' => 'Dit BTW nummer is al in gebruik',
        ],
    ],

];
