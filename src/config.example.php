<?php
/**
 * Don't edit the config.example.php.
 * Instead modify the projects/config/upper.php and use ENV VARS
 *
 */

return [

    // Which driver?
    'driver'        => getenv('UPPER_DRIVER') ?: 'dummy',

    // Default for Cache-control s-maxage
    'defaultMaxAge' => 3600 * 24 * 7,

    // Store tags locally and purge Urls
    // In case the cache driver does not support tag purging
    'useLocalTags'  => true,

    // Drivers settings
    'drivers'       => [

        // Varnish config
        'varnish'    => [
            'tagHeaderName'   => 'XKEY',
            'purgeHeaderName' => 'XKEY-PURGE',
            'purgeUrl'        => getenv('VARNISH_URL') ?: 'http://127.0.0.1:80/',
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
            'apiKey'             => getenv('CLOUDFLARE_API_KEY'),
            'apiEmail'           => getenv('CLOUDFLARE_API_EMAIL'),
            'zoneId'             => getenv('CLOUDFLARE_ZONE_ID'),
            'domain'             => getenv('CLOUDFLARE_DOMAIN')
        ],

        // Dummy driver (default)
        'dummy'      => [
            'tagHeaderName'   => 'X-CacheTag',
            'logPurgeActions' => true,
        ]
    ]
];
