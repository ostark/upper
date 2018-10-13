<?php namespace ostark\upper\handlers;

use craft\elements\Asset;
use craft\elements\GlobalSet;
use craft\events\ElementEvent;
use craft\events\ElementStructureEvent;
use craft\events\MoveElementEvent;
use craft\events\SectionEvent;
use ostark\upper\jobs\PurgeCacheJob;
use ostark\upper\Plugin;

/**
 * Class StateUpdate
 *
 * @package ostark\upper\handler
 */
class DetectStatusUpdate extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
{
    /**
     * @param \yii\base\Event $event
     */
    public function __invoke($event)
    {


        \Craft::warning('Status changed trigger', 'upper');


        if ($event instanceof ElementEvent) {
            if (!$this->plugin->getSettings()->isCachableElement(get_class($event->element))) {
                return;
            }

            // Status updated but not saved
            if (!$event->isNew) {
                $new = $event->element;
                $old = \Craft::$app->getElements()->getElementById($new->getId(), get_class($new));

                // status change
                if ($new->getStatus() != $old->getStatus()) {
                    $this->plugin->newElementStatus = $new->getStatus();
                }
            }
        }
    }
}
