<?php namespace ostark\upper\events;

use yii\base\Event;

/**
 * Class CacheResponseEvent
 *
 * @package ostark\upper\events
 */
class CacheResponseEvent extends Event
{
    /**
     * @var array Array of tags
     */
    public array $tags = [];

    /**
     * @var string
     */
    public string $requestUrl;

    /**
     * @var int Cache TTL in seconds
     */
    public int $maxAge = 0;

    /**
     * @var string
     */
    public string $output;

    /**
     * @var array Array of headers
     */
    public array $headers = [];

}
