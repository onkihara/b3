<?php

return [

    'disks' => [
        'b3' => [
            'driver' => 'b3',
            'version' => 'v1',
            'server' => env('B3_ENDPOINT'),
            'ttl' => env('B3_TOKENTLL'),
            'secret' => env('B3_SECRET'),
            'algo' => env('B3_ALGO'),
            'iss' => env('APP_NAME'),
            'sub' => env('APP_INSTANCE'),
            'aud' => env('APP_ID'),
            'expiration_time_in_seconds' => env('B3_TOKENTLL'),
        ]
    ]
];
