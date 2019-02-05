<?php namespace ostark\upper\EventHandlers;

use craft\base\Element;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\events\ElementEvent;
use craft\events\ElementStructureEvent;
use craft\events\MoveElementEvent;
use craft\events\SectionEvent;
use ostark\upper\Jobs\PurgeCacheJob;
use ostark\upper\Plugin;

/**
 * Class Update
 *
 * @package ostark\upper\handler
 */
class PurgeOnUpdate extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
{
    /**
     * @param \yii\base\Event $event
     */
    public function __invoke($event)
    {
        $tags = [];

        if ($event instanceof ElementEvent) {
            if (!$this->plugin->getSettings()->isCachableElement(get_class($event->element))) {
                return;
            }
            // GlobalSet
            if ($event->element instanceof GlobalSet && is_string($event->element->handle)) {
                $tags[] = $event->element->handle;
            } // New Asset
            elseif ($event->element instanceof Asset && $event->isNew) {
                $tags[] = (string)$event->element->volumeId;
            } // Existing Entry
            elseif (!$event->isNew) {
                $tags[] = Plugin::TAG_PREFIX_ELEMENT . $event->element->getId();
            }
            // New or changed status: Invalidate section of Entry
            if ($event->element->hasMethod('statusChangedToLive') && $event->element->statusChangedToLive() === true) {
                if (isset($event->element->sectionId)) {
                    $tags[] = Plugin::TAG_PREFIX_SECTION . $event->element->sectionId;
                }
            };

            if ($event->element->hasMethod('getStatusBeforeSave')) {
                $s1 = $event->element->getStatusBeforeSave();
                $s2 = $event->element->getStatus();
                if ($s1 != $s2) {
                    $entry = $event->element->id;
                    \Craft::info("Status changed from '$s1' to '$s2' - Enrtry: $entry" , 'craftcodingchallenge');
                }
            }

        }

        if ($event instanceof SectionEvent) {
            $tags[] = Plugin::TAG_PREFIX_SECTION . $event->section->id;
        }

        if ($event instanceof MoveElementEvent or $event instanceof ElementStructureEvent) {
            $tags[] = Plugin::TAG_PREFIX_STRUCTURE . $event->structureId;
        }

        if (count($tags) === 0) {
            $type = get_class($event);
            \Craft::warning("Unable to find tag. Unknown Event '$type'.", "upper");

            return;
        }

        foreach ($tags as $tag) {
            $tag = $this->plugin->getTagCollection()->prepareTag($tag);
            // Push to queue
            \Craft::$app->getQueue()->push(new PurgeCacheJob([
                    'tag' => $tag
                ]));
        }
    }
}
