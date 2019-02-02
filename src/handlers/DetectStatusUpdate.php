<?php namespace ostark\upper\handlers;

use craft\elements\Entry;
use craft\events\ElementEvent;
use ostark\upper\behaviors\ElementStatusBehavior;


/**
 * Class StateUpdate
 *
 *
 * @property \craft\base\Element $owner
 * @package ostark\upper\handler
 */
class DetectStatusUpdate extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
{
    /**
     * @param \yii\base\Event $event
     */
    public function __invoke($event)
    {

        if ($event instanceof ElementEvent) {

            if (!($event->element instanceof Entry)) {
                return;
            }

            // Attach behavior to store the state
            $event->element->attachBehavior('status', ElementStatusBehavior::class);

            // Status updated but not saved
            if (!$event->isNew) {
                $new = $event->element;
                $old = \Craft::$app->getElements()->getElementById($new->getId(), get_class($new));

                // Remember previous status
                $event->element->setStatusBeforeSave($old->getStatus());
            }
        }
    }
}
