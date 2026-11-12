<?php

/**
 * BOMBA — Street Food Italiano
 * ---------------------------------------------------------------------------
 * Unica fonte di verità del sito. Cambia brand, colors, menu e tutto il resto:
 * il sito si rebranda da solo, senza toccare una riga di HTML.
 *
 * Nessuna dipendenza, nessun framework, nessun database.
 * PHP 8.x nativo.
 * ---------------------------------------------------------------------------
 */

declare(strict_types=1);

return [

    /* ------------------------------------------------------------------ *
     * BRAND
     * ------------------------------------------------------------------ */
    'brand' => [
        'name'        => 'BOMBA',
        'subtitle'    => 'Street Food Italiano',
        'city'        => 'Parma',
        'tagline'     => 'Fritto adesso. Mangiato subito.',
        'since'       => 2019,
        'number'      => 'N° 001',
        'claim'       => 'Il meglio della strada italiana, fritto al momento e servito caldo. Qui, a Parma.',
        'meta_title'  => 'BOMBA — Street Food Italiano a Parma | Fritto adesso, mangiato subito',
        'meta_desc'   => 'Panuozzo, supplì, trapizzino, arancini, pizza fritta e cuoppo. Fritti al momento a Parma, aperti fino a tardi. Consegna in 30 minuti o asporto.',
        'og_image'    => 'assets/img/hero-bg.jpg',
        'theme_color' => '#FF3B1F',
        'piva'        => '00000000000',
        'year'        => 2026,
    ],

    /* ------------------------------------------------------------------ *
     * PALETTE « Asfalto Neon »
     * Injectée telle quelle dans :root — changer ces valeurs rebrando tutto.
     * ------------------------------------------------------------------ */
    'colors' => [
        'asfalto'    => '#111111', // fond principal
        'carta'      => '#F5F1E8', // sections « affiche »
        'inchiostro' => '#151515', // texte sur clair
        'rosso'      => '#FF3B1F', // accent 1 — CTA, prix
        'lime'       => '#C6FF3D', // accent 2 — badges, néon
        'blu'        => '#2F5BFF', // accent 3 — stickers, liens
        'rosa'       => '#FF4FA3', // accent 4 — stickers, détails
        'senape'     => '#FFC21A', // accent 5 — étiquettes

        // Dérivés utilisés par l'UI (ombres offset, halos, contours).
        'ombra'      => '#151515',
        'kraft'      => '#D8C39A',
        'kraft_scuro'=> '#B79B6C',
        'neon_halo'  => '#FF4FA3',
        'grain'      => '#FFFFFF',
    ],

    /* ------------------------------------------------------------------ *
     * CONTATTI
     * ------------------------------------------------------------------ */
    'contact' => [
        'street'   => 'Via Esempio 21',
        'zip'      => '43121',
        'city'     => 'Parma',
        'province' => 'PR',
        'address'  => 'Via Esempio 21, 43121 Parma (PR)',
        'phone'    => '+390521000000',
        'phone_ui' => '+39 0521 000 000',
        'email'    => 'ciao@bombastreetfood.it',
        'handle'   => '@bomba.parma',
        'maps'     => 'https://maps.google.com/?q=Via+Esempio+21+43121+Parma',
        'maps_embed_label' => 'Portami lì',
    ],

    /* ------------------------------------------------------------------ *
     * ORARI  (1 = lunedì … 7 = domenica)
     * ------------------------------------------------------------------ */
    'hours' => [
        ['dow' => 1, 'short' => 'Lun',     'label' => 'Lun',          'open' => [],                             'closed' => true],
        ['dow' => 2, 'short' => 'Mar',     'label' => 'Mar – Gio',    'open' => ['12:00–15:00', '18:30–00:00'],   'closed' => false],
        ['dow' => 3, 'short' => 'Mer',     'label' => 'Mar – Gio',    'open' => ['12:00–15:00', '18:30–00:00'],   'closed' => false],
        ['dow' => 4, 'short' => 'Gio',     'label' => 'Mar – Gio',    'open' => ['12:00–15:00', '18:30–00:00'],   'closed' => false],
        ['dow' => 5, 'short' => 'Ven',     'label' => 'Ven – Sab',    'open' => ['12:00–15:00', '18:30–02:00'],   'closed' => false],
        ['dow' => 6, 'short' => 'Sab',     'label' => 'Ven – Sab',    'open' => ['12:00–15:00', '18:30–02:00'],   'closed' => false],
        ['dow' => 7, 'short' => 'Dom',     'label' => 'Dom',          'open' => ['18:30–00:00'],                  'closed' => false],
    ],

    // Affichage « Quando » (regroupé, sans doublons).
    'hours_display' => [
        ['days' => 'Lun',       'slots' => ['chiuso']],
        ['days' => 'Mar – Gio', 'slots' => ['12:00–15:00', '18:30–00:00']],
        ['days' => 'Ven – Sab', 'slots' => ['12:00–15:00', '18:30–02:00']],
        ['days' => 'Dom',       'slots' => ['18:30–00:00']],
    ],

    /* ------------------------------------------------------------------ *
     * SOCIALS
     * ------------------------------------------------------------------ */
    'socials' => [
        ['label' => 'Instagram', 'url' => 'https://instagram.com/',  'icon' => 'instagram'],
        ['label' => 'TikTok',    'url' => 'https://tiktok.com/',     'icon' => 'tiktok'],
        ['label' => 'Facebook',  'url' => 'https://facebook.com/',   'icon' => 'facebook'],
    ],

    /* ------------------------------------------------------------------ *
     * MENU
     * ------------------------------------------------------------------ *
     * tags      : veg | piccante | gluten-free
     * allergens : glutine | lattosio | uova | frutta-a-guscio | sesamo
     * cutout    : nom du détouré dans /assets/img/cutouts/ (fallback géré)
     * ------------------------------------------------------------------ */
    'menu' => [

        'fritti' => [
            'label' => 'Fritti',
            'note'  => 'Olio nuovo ogni giorno, 175° fissi.',
            'items' => [
                [
                    'id' => 'suppli', 'name' => 'Supplì al telefono', 'price' => 3.00,
                    'desc' => 'Riso al ragù, cuore di mozzarella filante, panatura croccante.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'suppli', 'note' => 'il classico',
                ],
                [
                    'id' => 'arancino-ragu', 'name' => 'Arancino al ragù', 'price' => 3.50,
                    'desc' => 'Il classico: riso allo zafferano, ragù, piselli.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'arancino',
                ],
                [
                    'id' => 'arancino-burro', 'name' => 'Arancino burro e prosciutto', 'price' => 3.50,
                    'desc' => 'Riso, besciamella, prosciutto cotto e mozzarella. Comfort puro.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'arancino',
                ],
                [
                    'id' => 'crocche', 'name' => 'Crocchè di patate', 'price' => 2.50,
                    'desc' => 'Patate, prezzemolo, pepe nero. Fuori croccanti, dentro nuvola.',
                    'tags' => ['veg'], 'allergens' => ['glutine'],
                    'cutout' => 'crocche',
                ],
                [
                    'id' => 'frittatina', 'name' => 'Frittatina di pasta', 'price' => 3.50,
                    'desc' => 'Pasta, besciamella e pepe, fritta in pastella. Napoli pura.',
                    'tags' => ['veg'], 'allergens' => ['glutine', 'lattosio', 'uova'],
                    'cutout' => 'frittatina',
                ],
                [
                    'id' => 'olive-ascolane', 'name' => 'Olive ascolane (6 pz)', 'price' => 4.50,
                    'desc' => 'Olive ripiene di carne, impanate e fritte. Una tira l\'altra.',
                    'tags' => [], 'allergens' => ['glutine', 'uova'],
                    'cutout' => 'olive',
                ],
                [
                    'id' => 'cuoppo', 'name' => 'Cuoppo Misto', 'price' => 8.00,
                    'desc' => 'Cono di carta con frittatine, crocchè, zeppoline e olive ascolane.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio', 'uova'],
                    'cutout' => 'cuoppo', 'note' => 'da dividere (forse)',
                ],
            ],
        ],

        'panini' => [
            'label' => 'Panini & Panuozzi',
            'note'  => 'Pane di pizza cotto a legna, farcito al momento.',
            'items' => [
                [
                    'id' => 'panuozzo-bomba', 'name' => 'Panuozzo Bomba', 'price' => 9.00,
                    'desc' => 'Pane di pizza cotto a legna, porchetta, provola, friarielli.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'panuozzo', 'note' => 'top',
                ],
                [
                    'id' => 'panuozzo-salsiccia', 'name' => 'Panuozzo Salsiccia e friarielli', 'price' => 9.00,
                    'desc' => 'Salsiccia piccante, friarielli ripassati, provola affumicata.',
                    'tags' => ['piccante'], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'panuozzo', 'note' => 'piccante',
                ],
                [
                    'id' => 'porchetta-cicoria', 'name' => 'Porchetta e cicoria', 'price' => 8.00,
                    'desc' => 'Porchetta tagliata al coltello, cicoria, senape fatta in casa.',
                    'tags' => [], 'allergens' => ['glutine', 'sesamo'],
                    'cutout' => 'porchetta',
                ],
                [
                    'id' => 'panino-parma', 'name' => 'Panino Parma', 'price' => 9.50,
                    'desc' => 'Crudo di Parma, burrata, rucola. Il nostro omaggio alla città.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'panuozzo', 'note' => 'km 0',
                ],
                [
                    'id' => 'veg-panuozzo', 'name' => 'Veg Panuozzo', 'price' => 8.00,
                    'desc' => 'Melanzane grigliate, provola, pesto di basilico.',
                    'tags' => ['veg'], 'allergens' => ['glutine', 'lattosio', 'frutta-a-guscio'],
                    'cutout' => 'panuozzo',
                ],
            ],
        ],

        'trapizzini' => [
            'label' => 'Trapizzini',
            'note'  => 'Tasche di pizza bianca, chiuse e farcite.',
            'items' => [
                [
                    'id' => 'trapizzino-cacciatora', 'name' => 'Pollo alla cacciatora', 'price' => 6.50,
                    'desc' => 'Tasca di pizza bianca, pollo stufato, olive e rosmarino.',
                    'tags' => [], 'allergens' => ['glutine'],
                    'cutout' => 'trapizzino',
                ],
                [
                    'id' => 'trapizzino-polpetta', 'name' => 'Polpetta al sugo', 'price' => 6.50,
                    'desc' => 'Polpetta, sugo lungo, pane che assorbe tutto. Servono le mani.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio', 'uova'],
                    'cutout' => 'trapizzino',
                ],
                [
                    'id' => 'trapizzino-parmigiana', 'name' => 'Parmigiana di melanzane', 'price' => 6.00,
                    'desc' => 'Parmigiana, mozzarella, basilico. Quello della domenica.',
                    'tags' => ['veg'], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'trapizzino',
                ],
                [
                    'id' => 'trapizzino-trippa', 'name' => 'Trippa alla romana', 'price' => 6.50,
                    'desc' => 'Trippa, pecorino, mentuccia. Per i veri. Nessun pentimento.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'trapizzino', 'note' => 'per pochi',
                ],
            ],
        ],

        'pizza-fritta' => [
            'label' => 'Pizza fritta',
            'note'  => 'Fritta prima, poi passata al forno.',
            'items' => [
                [
                    'id' => 'montanara', 'name' => 'Montanara', 'price' => 7.00,
                    'desc' => 'Fritta, poi al forno: pomodoro, parmigiano, basilico.',
                    'tags' => ['veg'], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'pizza-fritta',
                ],
                [
                    'id' => 'classica-ripiena', 'name' => 'Classica ripiena', 'price' => 9.00,
                    'desc' => 'Ricotta, cicoli, provola e pepe. Chiusa a libretto e fritta.',
                    'tags' => [], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'pizza-fritta',
                ],
                [
                    'id' => 'diavola-fritta', 'name' => 'Diavola fritta', 'price' => 8.50,
                    'desc' => 'Salame piccante, provola, peperoncino fresco. Acqua inclusa.',
                    'tags' => ['piccante'], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'pizza-fritta', 'note' => 'piccante',
                ],
            ],
        ],

        'dolci' => [
            'label' => 'Dolci',
            'note'  => 'Fatti qui, finiti presto.',
            'items' => [
                [
                    'id' => 'cannolo-espresso', 'name' => 'Cannolo espresso', 'price' => 3.50,
                    'desc' => 'Ricotta montata, cioccolato fondente e un colpo di espresso.',
                    'tags' => ['veg'], 'allergens' => ['glutine', 'lattosio'],
                    'cutout' => 'cannolo',
                ],
                [
                    'id' => 'tiramisu-barattolo', 'name' => 'Tiramisù in barattolo', 'price' => 4.50,
                    'desc' => 'Nel barattolo, come lo fa la nonna. Cucchiaio incluso.',
                    'tags' => ['veg'], 'allergens' => ['glutine', 'lattosio', 'uova'],
                    'cutout' => 'tiramisu',
                ],
                [
                    'id' => 'zeppole-nutella', 'name' => 'Zeppole con Nutella', 'price' => 5.00,
                    'desc' => 'Zeppole calde, crema di nocciole, zucchero a pioggia.',
                    'tags' => ['veg'], 'allergens' => ['glutine', 'lattosio', 'uova', 'frutta-a-guscio'],
                    'cutout' => 'zeppole', 'note' => 'top',
                ],
                [
                    'id' => 'gelato', 'name' => 'Gelato artigianale (2 gusti)', 'price' => 3.50,
                    'desc' => 'Mantecato qui sotto. Chiedi i gusti del giorno al banco.',
                    'tags' => ['veg', 'gluten-free'], 'allergens' => ['lattosio'],
                    'cutout' => 'gelato',
                ],
            ],
        ],

        'bere' => [
            'label' => 'Da bere',
            'note'  => 'Freddo davvero, non « quasi ».',
            'items' => [
                [
                    'id' => 'spritz', 'name' => 'Spritz', 'price' => 6.00,
                    'desc' => 'Ghiaccio, bollicine, fetta d\'arancia. Il rito delle 19:00.',
                    'tags' => ['veg', 'gluten-free'], 'allergens' => [],
                    'cutout' => 'spritz',
                ],
                [
                    'id' => 'birra', 'name' => 'Birra artigianale', 'price' => 5.00,
                    'desc' => 'Bionda non filtrata di un birrificio della zona. 33 cl.',
                    'tags' => ['veg'], 'allergens' => ['glutine'],
                    'cutout' => 'lattina',
                ],
                [
                    'id' => 'chinotto', 'name' => 'Chinotto', 'price' => 3.00,
                    'desc' => 'Amaro il giusto. In vetro, come una volta.',
                    'tags' => ['veg', 'gluten-free'], 'allergens' => [],
                    'cutout' => 'lattina',
                ],
                [
                    'id' => 'aranciata', 'name' => 'Aranciata', 'price' => 3.00,
                    'desc' => 'Spremuta di arance vere, poi gassata. In vetro.',
                    'tags' => ['veg', 'gluten-free'], 'allergens' => [],
                    'cutout' => 'lattina',
                ],
                [
                    'id' => 'acqua', 'name' => 'Acqua', 'price' => 1.50,
                    'desc' => 'Naturale o frizzante. 50 cl.',
                    'tags' => ['veg', 'gluten-free'], 'allergens' => [],
                    'cutout' => 'lattina',
                ],
            ],
        ],
    ],

    // Libellés des filtres + des allergènes (affichage uniquement).
    'filters' => [
        ['id' => 'tutti',        'label' => 'Tutti'],
        ['id' => 'veg',          'label' => 'Veg'],
        ['id' => 'piccante',     'label' => 'Piccante', 'icon' => 'peperoncino'],
        ['id' => 'gluten-free',  'label' => 'Senza glutine'],
    ],

    'allergens' => [
        'glutine'         => 'Glutine',
        'lattosio'        => 'Lattosio',
        'uova'            => 'Uova',
        'frutta-a-guscio' => 'Frutta a guscio',
        'sesamo'          => 'Sesamo',
    ],

    /* ------------------------------------------------------------------ *
     * BESTSELLERS — « I più bombati »
     * id pointe vers un article du menu (prix/desc hérités si absents).
     * ------------------------------------------------------------------ */
    'bestsellers' => [
        [
            'id' => 'panuozzo-bomba', 'name' => 'Panuozzo Bomba', 'price' => 9.00,
            'desc' => 'Pane di pizza cotto a legna, porchetta, provola, friarielli.',
            'cutout' => 'panuozzo', 'sticker' => 'top', 'bg' => 'rosso', 'fg' => 'carta',
        ],
        [
            'id' => 'suppli', 'name' => 'Supplì al telefono', 'price' => 3.00,
            'desc' => 'Riso al ragù, cuore di mozzarella filante, panatura croccante.',
            'cutout' => 'suppli', 'sticker' => 'nuovo!', 'bg' => 'senape', 'fg' => 'inchiostro',
        ],
        [
            'id' => 'trapizzino-cacciatora', 'name' => 'Trapizzino Pollo alla cacciatora', 'price' => 6.50,
            'desc' => 'Tasca di pizza bianca, pollo stufato, olive e rosmarino.',
            'cutout' => 'trapizzino', 'sticker' => 'top', 'bg' => 'asfalto', 'fg' => 'carta',
        ],
        [
            'id' => 'arancino-ragu', 'name' => 'Arancino al ragù', 'price' => 3.50,
            'desc' => 'Il classico: riso allo zafferano, ragù, piselli.',
            'cutout' => 'arancino', 'sticker' => 'piccante', 'bg' => 'lime', 'fg' => 'inchiostro',
        ],
        [
            'id' => 'montanara', 'name' => 'Pizza fritta Montanara', 'price' => 7.00,
            'desc' => 'Fritta, poi al forno: pomodoro, parmigiano, basilico.',
            'cutout' => 'pizza-fritta', 'sticker' => 'nuovo!', 'bg' => 'blu', 'fg' => 'carta',
        ],
        [
            'id' => 'cuoppo', 'name' => 'Cuoppo Misto', 'price' => 8.00,
            'desc' => 'Cono di carta con frittatine, crocchè, zeppoline e olive ascolane.',
            'cutout' => 'cuoppo', 'sticker' => 'top', 'bg' => 'rosa', 'fg' => 'inchiostro',
        ],
    ],

    /* ------------------------------------------------------------------ *
     * COMBO — « Crea il tuo combo »
     * Sconto fisso: 9 + 3 + 5 = 17 → 13,90 → RISPARMI € 3,10
     * ------------------------------------------------------------------ */
    'combo' => [
        'discount' => 3.10,
        'intro'    => 'Scegli 3 cose, paghi meno. Matematica della strada.',
        'cta'      => 'Aggiungi il combo al sacchetto',
        'steps' => [
            [
                'key' => 'main', 'title' => 'Il principale', 'hint' => 'Panuozzo o trapizzino',
                'missing' => 'il principale',
                'options' => ['panuozzo-bomba', 'panuozzo-salsiccia', 'porchetta-cicoria', 'veg-panuozzo', 'trapizzino-cacciatora', 'trapizzino-polpetta'],
            ],
            [
                'key' => 'fried', 'title' => 'Il fritto', 'hint' => 'Uno, non negoziabile',
                'missing' => 'il fritto',
                'options' => ['suppli', 'arancino-ragu', 'crocche', 'frittatina', 'olive-ascolane'],
            ],
            [
                'key' => 'drink', 'title' => 'Da bere', 'hint' => 'Ghiacciato',
                'missing' => 'da bere',
                'options' => ['birra', 'spritz', 'chinotto', 'aranciata', 'acqua'],
            ],
        ],
    ],

    /* ------------------------------------------------------------------ *
     * GALLERIA — muro di polaroid
     * ------------------------------------------------------------------ */
    'gallery' => [
        ['id' => 'g01', 'src' => 'assets/img/locale-01.jpg', 'caption' => 'Venerdì sera',  'focus' => '50% 40%', 'rot' => -4, 'size' => 'lg'],
        ['id' => 'g02', 'src' => 'assets/img/cucina.jpg',    'caption' => 'La friggitrice','focus' => '50% 60%', 'rot' => 3,  'size' => 'md'],
        ['id' => 'g03', 'src' => 'assets/img/bancone.jpg',   'caption' => 'Il banco',      'focus' => '40% 50%', 'rot' => -2, 'size' => 'md'],
        ['id' => 'g04', 'src' => 'assets/img/crew.jpg',      'caption' => 'Il crew',       'focus' => '50% 30%', 'rot' => 5,  'size' => 'lg'],
        ['id' => 'g05', 'src' => 'assets/img/notte.jpg',     'caption' => 'Ore 01:30',     'focus' => '60% 50%', 'rot' => -6, 'size' => 'sm'],
        ['id' => 'g06', 'src' => 'assets/img/piatti/fritti.jpg', 'caption' => 'Supplì party', 'focus' => '50% 50%', 'rot' => 2, 'size' => 'md'],
        ['id' => 'g07', 'src' => 'assets/img/furgone.jpg',   'caption' => 'Il furgone',    'focus' => '45% 55%', 'rot' => -3, 'size' => 'lg'],
        ['id' => 'g08', 'src' => 'assets/img/locale-02.jpg', 'caption' => 'Impasto',       'focus' => '50% 45%', 'rot' => 6,  'size' => 'sm'],
        ['id' => 'g09', 'src' => 'assets/img/hero-bg.jpg',   'caption' => 'Sabato',        'focus' => '50% 65%', 'rot' => -5, 'size' => 'md'],
        ['id' => 'g10', 'src' => 'assets/img/locale-03.jpg', 'caption' => 'Fila fuori',    'focus' => '50% 40%', 'rot' => 4,  'size' => 'lg'],
    ],

    /* ------------------------------------------------------------------ *
     * RECENSIONI
     * ------------------------------------------------------------------ */
    'reviews' => [
        'rating'   => '4,8',
        'rating_max' => '5',
        'count'    => 'oltre 900 recensioni',
        'items' => [
            ['text' => 'Il supplì più filante di Parma. Fine della discussione.', 'author' => 'Luca B.',      'bg' => 'carta',      'rot' => -3],
            ['text' => 'Arrivato in 25 minuti, ancora croccante. Magia.',          'author' => 'Chiara T.',    'bg' => 'senape',     'rot' => 4],
            ['text' => 'Il panuozzo mi ha cambiato la serata. Forse la vita.',     'author' => 'Davide M.',    'bg' => 'carta',      'rot' => -2],
            ['text' => 'Prezzi onesti, porzioni da veri affamati.',                'author' => 'Federica S.',  'bg' => 'lime',       'rot' => 5],
            ['text' => 'Aperti fino a tardi = salvatori ufficiali del sabato.',    'author' => 'Matteo G.',    'bg' => 'carta',      'rot' => -5],
        ],
    ],

    /* ------------------------------------------------------------------ *
     * PRENOTAZIONE
     * ------------------------------------------------------------------ */
    'booking' => [
        'guests_max'   => 8,
        'closed_dow'   => 1,           // lunedì
        'closed_label' => 'Chiuso',
        'day_labels'   => ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'],
        'lunch'  => ['label' => 'Pranzo', 'from' => '12:00', 'to' => '15:00'],
        'dinner' => ['label' => 'Sera',   'from' => '18:30', 'to' => '01:00'],
        'step_minutes' => 30,
        'occasions'    => ['Nessuna', 'Compleanno', 'Serata con amici', 'Primo appuntamento'],
        'code_prefix'  => 'BMB-T-',
        'privacy'      => 'Accetto il trattamento dei dati personali',
        'full_label'   => 'Pieno',
        'last_label'   => 'Ultimi posti',
        'big_party'    => 'Siete una banda? Chiamaci al ',
    ],

    /* ------------------------------------------------------------------ *
     * DELIVERY / ASPORTO
     * ------------------------------------------------------------------ */
    'delivery' => [
        'fee'        => 1.90,
        'free_from'  => 20.00,
        'min_order'  => 10.00,
        'eta'        => '25–35 min',
        'zone'       => 'Parma e prima cintura',
        'pickup_step_minutes' => 10,
        'tracking' => [
            ['key' => 'ricevuto',  'label' => 'Ricevuto',             'icon' => 'check'],
            ['key' => 'friggitrice','label' => 'In friggitrice',      'icon' => 'flame'],
            ['key' => 'impacchettato','label' => 'Impacchettato',     'icon' => 'bag'],
            ['key' => 'consegna',  'label' => 'Il rider sta arrivando','icon' => 'scooter', 'mode' => 'delivery'],
            ['key' => 'banco',     'label' => 'Pronto al banco',      'icon' => 'bell',    'mode' => 'pickup'],
        ],
        'payment_methods' => [
            ['id' => 'carta',    'label' => 'Carta',    'icon' => 'card'],
            ['id' => 'satispay', 'label' => 'Satispay', 'icon' => 'satispay'],
            ['id' => 'contanti', 'label' => 'Contanti', 'icon' => 'cash'],
        ],
        'payment_note' => 'Pagamento simulato — nessun addebito',
    ],

    /* ------------------------------------------------------------------ *
     * STICKERS
     * ------------------------------------------------------------------ */
    'stickers' => [
        // Footer : chute physique Matter.js (12–16 stickers).
        'footer' => [
            'stella-fritto', 'ruota-bomba', 'freccia', 'fulmine', 'fiamma',
            'cuore', 'napoli-parma', 'spicy', 'aperti-2', 'nonna',
            'scribble', 'cerchio', 'stella-fritto', 'fulmine', 'cuore', 'ruota-bomba',
        ],
        // Éparpillement décoratif (menu mobile, sections).
        'scatter' => ['stella-fritto', 'fulmine', 'fiamma', 'cuore', 'spicy'],
    ],

    /* ------------------------------------------------------------------ *
     * TICKER / RUBALISE
     * ------------------------------------------------------------------ */
    'ticker' => [
        ['text' => 'ORDINA ORA', 'sep' => '✶'],
        ['text' => 'FRITTO ADESSO', 'sep' => '✶'],
        ['text' => "CONSEGNA IN 30'", 'sep' => '✶'],
    ],
    'ticker_2' => ['SUPPLÌ', 'PANUOZZO', 'TRAPIZZINO', 'ARANCINO'],

    /* ------------------------------------------------------------------ *
     * HERO
     * ------------------------------------------------------------------ */
    'hero' => [
        'lines'     => ['MANGIA.', 'RIPETI.'],
        'neon'      => 'APERTI FINO A TARDI',
        'cta1'      => 'Ordina adesso',
        'cta2'      => 'Prenota un tavolo',
        'sticker'   => "consegna in 30'!",
        'strip'     => "N° 001 — PARMA — DAL 2019 — FRITTO IN 4 MINUTI",
        'photo'     => 'assets/img/hero-bg.jpg',
        // Collage : slug du détouré, coefficients de parallaxe, placement.
        'collage' => [
            ['cutout' => 'panuozzo',    'x' => 50, 'y' => 54, 'w' => 46, 'depth' => 1.00, 'rot' => -4, 'z' => 5, 'add' => 'panuozzo-bomba'],
            ['cutout' => 'arancino',    'x' => 20, 'y' => 34, 'w' => 20, 'depth' => 1.90, 'rot' => 8,  'z' => 4, 'add' => 'arancino-ragu'],
            ['cutout' => 'suppli',      'x' => 78, 'y' => 30, 'w' => 17, 'depth' => 2.30, 'rot' => -9, 'z' => 4, 'add' => 'suppli'],
            ['cutout' => 'cuoppo',      'x' => 84, 'y' => 68, 'w' => 21, 'depth' => 1.40, 'rot' => 6,  'z' => 3, 'add' => 'cuoppo'],
            ['cutout' => 'peperoncino', 'x' => 13, 'y' => 72, 'w' => 12, 'depth' => 2.80, 'rot' => -14,'z' => 6, 'add' => null],
            ['cutout' => 'basilico',    'x' => 68, 'y' => 84, 'w' => 13, 'depth' => 3.10, 'rot' => 12, 'z' => 6, 'add' => null],
            ['cutout' => 'trapizzino',  'x' => 31, 'y' => 78, 'w' => 18, 'depth' => 2.00, 'rot' => 5,  'z' => 3, 'add' => 'trapizzino-cacciatora'],
        ],
    ],

    /* ------------------------------------------------------------------ *
     * STORY — chiffres compteur
     * ------------------------------------------------------------------ */
    'story' => [
        'title' => ['NATI PER', 'STRADA'],
        'photo' => 'assets/img/crew.jpg',
        'text'  => "Tutto è iniziato con una friggitrice, un furgone e troppa fame. Oggi friggiamo ogni giorno impasti fatti in casa, compriamo dai produttori della zona e teniamo aperto fino a tardi, perché la fame non guarda l'orologio.",
        'stats' => [
            ['value' => 4,      'suffix' => "'",      'label' => 'per friggere'],
            ['value' => 30000,  'prefix' => '+', 'sep' => true, 'label' => 'supplì all\'anno'],
            ['value' => 2,      'suffix' => ':00',    'label' => 'chiudiamo, forse'],
        ],
        'stamp' => 'APPROVATO DALLA NONNA',
    ],

    /* ------------------------------------------------------------------ *
     * BADGE DEMO
     * ------------------------------------------------------------------ */
    'demo_badge' => true,

    /* ------------------------------------------------------------------ *
     * CDN (centralisé, versionné)
     * ------------------------------------------------------------------ */
    'cdn' => [
        'gsap'      => 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/',
        'lenis'     => 'https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.min.js',
        'swiper_js' => 'https://cdn.jsdelivr.net/npm/swiper@11.1.14/swiper-bundle.min.js',
        'swiper_css'=> 'https://cdn.jsdelivr.net/npm/swiper@11.1.14/swiper-bundle.min.css',
        'matter'    => 'https://cdn.jsdelivr.net/npm/matter-js@0.20.0/build/matter.min.js',
        'fonts'     => 'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=DM+Mono:wght@400;500&family=Inter+Tight:wght@400;600&family=Permanent+Marker&display=swap',
    ],
];
