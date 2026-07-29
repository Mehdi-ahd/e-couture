<?php

return [

    'schema_version' => 1,

    'page_sizes' => [
        'bootstrap' => [
            'clients' => 50,
            'modele_vetements' => 20,
            'commande_vetements' => 20,
            'fiche_mesures' => 20,
            'mesures' => 20,
            'type_vetements' => null, // null = all
            'type_mesures' => null,
            'patrons' => 20,
        ],
        'sync' => [
            'clients' => 100,
            'modele_vetements' => 100,
            'commande_vetements' => 100,
            'fiche_mesures' => 100,
            'mesures' => 200,
            'patrons' => 100,
        ],
    ],

    'priority' => [
        1 => 'users',
        2 => 'clients',
        3 => 'modele_vetements',
        4 => 'commande_vetements',
        5 => 'fiche_mesures',
        6 => 'mesures',
        7 => 'patrons',
        8 => 'type_vetements',
        9 => 'type_mesures',
    ],
];
