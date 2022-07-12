<?php namespace ostark\Upper\Events;

use yii\base\Event;

/**
 * Class PurgeEvent
 *
 * @package ostark\Upper\events
 */
class PurgeEvent extends Event
{
    // Properties
    // =========================================================================

    /**
    * @var string tag
    */
    public $tag;

}
