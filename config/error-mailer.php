<?php

return [
    'email' => [
        'recipient' => ['wellarts@gmail.com'],
        'bcc'       => [],
        'cc'        => [],
        'subject'   => 'ERRO EM SISTEMA - ' . env('APP_NAME'),
    ],

    'disabledOn' => [
        //
    ],

    'cacheCooldown' => 1, // in minutes

    'webhooks' => [
        'discord' => env('ERROR_MAILER_DISCORD_WEBHOOK'),

        'message' => [
            'title'        => 'Error Alert - ' . env('APP_NAME'),
            'description'  => 'UM ERRO FOI ENCONTRADO EM UM SISTEMA.',
            'error'        => 'Error',
            'file'         => 'File',
            'line'         => 'Line',
            'details_link' => 'See more details',
        ],
    ],

    'storage_path' => storage_path('app/errors'),
];
