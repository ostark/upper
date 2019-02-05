<?php namespace ostark\upper\Events;

use yii\base\Event;

/**
 * Class PurgeEvent
 *
 * @package ostark\upper\Events
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
