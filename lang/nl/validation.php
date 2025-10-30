<?php

return [

    'required' => ':Attribute opgeven',

    'custom' => [
        'name' => [
            'unique' => 'Naam is al in gebruik',
        ],
        'email' => [
            'unique' => 'Email adres is al in gebruik',
        ],
        'vat_number' => [
            'required' => 'BTW nummer opgeven',
            'unique' => 'BTW nummer is al in gebruik',
        ],
        'vat_rate' => [
            'required' => 'BTW percentage opgeven',
        ],
    ],

];
