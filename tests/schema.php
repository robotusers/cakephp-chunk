<?php
declare(strict_types=1);

return [
    'authors' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
            ],
            'name' => [
                'type' => 'string',
                'default' => null,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
        ],
    ],
];
