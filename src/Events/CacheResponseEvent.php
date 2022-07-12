<?php namespace ostark\Upper\Events;

use yii\base\Event;

/**
 * Class CacheResponseEvent
 *
 * @package ostark\Upper\events
 */
class CacheResponseEvent extends Event
{
    /**
     * @var array Array of tags
     */
    public $tags = [];

    /**
     * @var string
     */
    public $requestUrl;

    /**
     * @var int Cache TTL in seconds
     */
    public $maxAge = 0;

    /**
     * @var string
     */
    public $output;

    /**
     * @var array Array of headers
     */
    public $headers = [];

}
