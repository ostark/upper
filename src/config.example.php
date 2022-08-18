<?php
/**
 * Don't edit the config.example.php.
 * Instead modify the projects/config/upper.php and use ENV VARS
 */

return [

    // Which driver?
    'driver'        => getenv('UPPER_DRIVER') ?: 'dummy',

    // Default for Cache-control s-maxage
    'defaultMaxAge' => 3600 * 24 * 7,

    // Store tags locally and purge Urls
    // In case the cache driver does not support tag purging
    'useLocalTags'  => true,

    // Optional key prefix, to prevent collisions in case you're using the
    // same cache store for several Craft installations.
    // Keep it nice and short for the sake of readability when debugging.
    // 1-8 characters, special chars get removed
    'keyPrefix'     => getenv('UPPER_KEY_PREFIX') ?: '',

    // Drivers settings
    'drivers'       => [

        // Varnish config
        'varnish'    => [
            'tagHeaderName'   => 'XKEY',
            'purgeHeaderName' => 'XKEY-PURGE',
            'purgeUrl'        => getenv('VARNISH_URL') ?: 'http://127.0.0.1:80/',
            'headers'         => getenv('VARNISH_HOST') ? ['Host' => getenv('VARNISH_HOST')] : [],
            'softPurge'       => false,
        ],

        // Fastly config
        'fastly'     => [
            'tagHeaderName' => 'Surrogate-Key',
            'serviceId'     => getenv('FASTLY_SERVICE_ID'),
            'apiToken'      => getenv('FASTLY_API_TOKEN'),
            'domain'        => getenv('FASTLY_DOMAIN'),
        ],

        // KeyCDN config
        'keycdn'     => [
            'tagHeaderName' => 'Cache-Tag',
            'apiKey'        => getenv('KEYCDN_API_KEY'),
            'zoneId'        => getenv('KEYCDN_ZONE_ID'),
            'zoneUrl'       => getenv('KEYCDN_ZONE_URL')
        ],

        // CloudFlare config
        'cloudflare' => [
            'tagHeaderName'      => 'Cache-Tag',
            'tagHeaderDelimiter' => ',',
            'apiToken'           => getenv('CLOUDFLARE_API_TOKEN'),
            'zoneId'             => getenv('CLOUDFLARE_ZONE_ID'),
            'domain'             => getenv('CLOUDFLARE_DOMAIN'),
            'sites'              => [
                'defaultId' => [
                    'zoneId' => getenv('CLOUDFLARE_ZONE_ID'),
                    'domain' => getenv('CLOUDFLARE_DOMAIN'),    
                ],
                'alternateId' => [
                    'zoneId' => getenv('ALT_CLOUDFLARE_ZONE_ID'),
                    'domain' => getenv('ALT_CLOUDFLARE_DOMAIN'),    
                ],
            ],
            // deprecated, do not use for new installs
            'apiKey'             => getenv('CLOUDFLARE_API_KEY'),
            'apiEmail'           => getenv('CLOUDFLARE_API_EMAIL'),
        ],

        // Dummy driver (default)
        'dummy'      => [
            'tagHeaderName'   => 'X-CacheTag',
            'logPurgeActions' => true,
        ]
    ]
];
