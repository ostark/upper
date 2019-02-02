<?php namespace ostark\upper\behaviors;

use craft\elements\Entry;
use yii\base\Behavior;

/**
 * Class ElementStatusBehavior
 *
 * @property \craft\base\Element $owner
 * @package ostark\upper\behaviors
 */
class ElementStatusBehavior extends Behavior
{


    protected $statusBeforeSave;

    /**
     * Get Status from Entry
     */

    /**
     * @param $status
     */
    public function setStatusBeforeSave($status)
    {
        $this->statusBeforeSave = $status;
    }

    /**
     * @return bool
     */
    public function statusChangedToLive()
    {
        if (!($this->owner instanceof Entry)) {
            return false;
        }

        if ($this->statusBeforeSave === $this->owner->getStatus()) {
            return false;
        }

        if ($this->owner->getStatus() != Entry::STATUS_LIVE) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getStatusBeforeSave()
    {
        return $this->statusBeforeSave;
    }

}
