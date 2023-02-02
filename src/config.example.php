<?php
/**
 * Don't edit the config.example.php.
 * Instead modify the projects/config/upper.php and use ENV VARS
 */
use craft\helpers\App;

return [

    // Which driver?
    'driver'        => App::env('UPPER_DRIVER') ?: 'dummy',

    // Default for Cache-control s-maxage
    'defaultMaxAge' => 3600 * 24 * 7,

    // Store tags locally and purge Urls
    // In case the cache driver does not support tag purging
    'useLocalTags'  => true,

    // Optional key prefix, to prevent collisions in case you're using the
    // same cache store for several Craft installations.
    // Keep it nice and short for the sake of readability when debugging.
    // 1-8 characters, special chars get removed
    'keyPrefix'     => App::env('UPPER_KEY_PREFIX') ?: '',

    // Optional maximum length for the cache tag header. Setting this higher will
    // allow Upper to return more tags in the header. However, it will also require
    // more resources on the server to store/buffer the tags. Ensure you have enough
    // room in any proxy/CDN to display large headers if you raise this above 4k.
    // Stored in bytes, typically as `16 * 1024` for 16KB
    'maxBytesForCacheTagHeader' => null,

    // Drivers settings
    'drivers'       => [

        // Varnish config
        'varnish'    => [
            'tagHeaderName'   => 'XKEY',
            'purgeHeaderName' => 'XKEY-PURGE',
            'purgeUrl'        => App::env('VARNISH_URL') ?: 'http://127.0.0.1:80/',
            'headers'         => App::env('VARNISH_HOST') ? ['Host' => App::env('VARNISH_HOST')] : [],
            'softPurge'       => false,
        ],

        // Fastly config
        'fastly'     => [
            'tagHeaderName' => 'Surrogate-Key',
            'serviceId'     => App::env('FASTLY_SERVICE_ID'),
            'apiToken'      => App::env('FASTLY_API_TOKEN'),
            'domain'        => App::env('FASTLY_DOMAIN'),
            'softPurge'     => false
        ],

        // KeyCDN config
        'keycdn'     => [
            'tagHeaderName' => 'Cache-Tag',
            'apiKey'        => App::env('KEYCDN_API_KEY'),
            'zoneId'        => App::env('KEYCDN_ZONE_ID'),
            'zoneUrl'       => App::env('KEYCDN_ZONE_URL')
        ],

        // CloudFlare config
        'cloudflare' => [
            'tagHeaderName'      => 'Cache-Tag',
            'tagHeaderDelimiter' => ',',
            'apiToken'           => App::env('CLOUDFLARE_API_TOKEN'),
            'zoneId'             => App::env('CLOUDFLARE_ZONE_ID'),
            'domain'             => App::env('CLOUDFLARE_DOMAIN'),
            // deprecated, do not use for new installs
            'apiKey'             => App::env('CLOUDFLARE_API_KEY'),
            'apiEmail'           => App::env('CLOUDFLARE_API_EMAIL'),
        ],

        // Dummy driver (default)
        'dummy'      => [
            'tagHeaderName'   => 'X-CacheTag',
            'logPurgeActions' => true,
        ]
    ]
];
