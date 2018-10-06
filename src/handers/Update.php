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
 * Class Update
 *
 * @package ostark\upper\handler
 */
class Update extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
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

            if ($event->element instanceof GlobalSet && is_string($event->element->handle)) {
                $tags[] = $event->element->handle;
            } elseif ($event->element instanceof Asset && $event->isNew) {
                $tags[] = (string)$event->element->volumeId;
            } else {
                if (!$event->isNew) {
                    $tags[] = Plugin::TAG_PREFIX_ELEMENT . $event->element->getId();
                }
                if (isset($event->element->sectionId)) {
                    $tags[] = Plugin::TAG_PREFIX_SECTION . $event->element->sectionId;
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
                ]
            ));
        }
    }
}
