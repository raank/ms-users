<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Access key ID
    |--------------------------------------------------------------------------
    */

    'key' => env('AWS_ACCESS_KEY_ID'),
    
    /*
    |--------------------------------------------------------------------------
    | Secret access key
    |--------------------------------------------------------------------------
    */

    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    
    /*
    |--------------------------------------------------------------------------
    | Region to AWS services
    |--------------------------------------------------------------------------
    */

    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),

    /*
    |--------------------------------------------------------------------------
    | Define the profile to SQS Client
    |--------------------------------------------------------------------------
    */

    'profile' => env('AWS_SQS_PROFILE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | SQS Queues on AWS
    |--------------------------------------------------------------------------
    */
    
    'queues' => [
        'emails' => env('AWS_SQS_QUEUE')
    ],

    /*
    |--------------------------------------------------------------------------
    | SQS Queue params
    |--------------------------------------------------------------------------
    */

    'sqs' => [

        /*
        |--------------------------------------------------------------------------
        | Define the SQS queue URL
        |--------------------------------------------------------------------------
        */
        'prefix' => env('AWS_SQS_PREFIX'),

        /*
        |--------------------------------------------------------------------------
        | Define the SQS queue name
        |--------------------------------------------------------------------------
        */
        'queue' => env('AWS_SQS_QUEUE'),

        /*
        |--------------------------------------------------------------------------
        | Define the client version
        |--------------------------------------------------------------------------
        */
        'version' => env('AWS_SQS_VERSION', '2012-11-05'),

        /*
        |--------------------------------------------------------------------------
        | Define the message params
        |--------------------------------------------------------------------------
        */
        'messages' => [
            'group_id' => 'default',
        ],
    ]

];