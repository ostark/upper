<?php namespace ostark\upper\events;

use yii\base\Event;

/**
 * Class PurgeEvent
 *
 * @package ostark\upper\events
 */
class PurgeEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array Array of tags
     */
    public $tags = [];
}
