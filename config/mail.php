<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "log", "array", "failover"
    |
    */

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'smtp1' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'dgadmision@unap.edu.pe',
            'password' => 'ghsp ydhg ezya rbtx',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp2' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.informatica@unap.edu.pe',
            'password' => 'yxrv jiji ervw wunc',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp3' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.informatica1@unap.edu.pe',
            'password' => 'ephi ltcs espu rafx',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp4' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.informatica2@unap.edu.pe',
            'password' => 'jldj fyoc ajxd ynpl',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp5' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.informatica3@unap.edu.pe',
            'password' => 'aeob ywpk zbry bxtg',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp6' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.informatica4@unap.edu.pe',
            'password' => 'gctv sewy hgmq qevm',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp7' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.informatica5@unap.edu.pe',
            'password' => 'nmkf wecy vvsz vrvm',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp8' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.oti6@unap.edu.pe',
            'password' => 'rpai nphi ngbu zvoi',
            'timeout' => null,
            'auth_mode' => null,
        ],

        'smtp9' => [
            'transport' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'admision.oti@unap.edu.pe',
            'password' => 'uhry kipi mvlk egon',
            'timeout' => null,
            'auth_mode' => null,
],

'smtp10' => [
    'transport' => 'smtp',
    'host' => 'smtp.gmail.com',
    'port' => 465,
    'encryption' => 'ssl',
    'username' => 'admision.oti7@unap.edu.pe',
    'password' => 'vuxc lcdp dxfi orpn',
    'timeout' => null,
    'auth_mode' => null,
],


        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
