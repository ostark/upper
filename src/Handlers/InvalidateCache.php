<?php namespace ostark\Upper\Handlers;

use craft\events\ElementEvent;
use craft\events\ElementStructureEvent;
use craft\events\MoveElementEvent;
use craft\events\SectionEvent;
use craft\helpers\ElementHelper;
use ostark\Upper\Events\PurgeEvent;
use ostark\Upper\Jobs\PurgeCacheJob;
use ostark\Upper\Models\PluginSettings;
use ostark\Upper\Plugin;
use yii\base\Event;

class InvalidateCache
{
    protected PluginSettings $settings;

    public function __construct(PluginSettings $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(Event $event): void
    {

        $tags = [];

        if ($event instanceof ElementEvent) {

            if (!$this->settings->isCachableElement(get_class($event->element))) {
                return;
            }

            // Prevent purge on updates of drafts or revisions
            if (ElementHelper::isDraftOrRevision($event->element)) {
                return;
            }

            // Prevent purge on resaving
            if (property_exists($event->element, 'resaving') && $event->element->resaving === true) {
                return;
            }

            if ($event->element instanceof \craft\elements\GlobalSet && is_string($event->element->handle)) {
                $tags[] = $event->element->handle;
            } elseif ($event->element instanceof \craft\elements\Asset && $event->isNew) {
                $tags[] = (string)$event->element->volumeId;
            } else {
                if (isset($event->element->sectionId)) {
                    $tags[] = Plugin::TAG_PREFIX_SECTION . $event->element->sectionId;
                }
                if (!$event->isNew) {
                    $tags[] = Plugin::TAG_PREFIX_ELEMENT . $event->element->getId();
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
            return;
        }

        foreach ($tags as $tag) {
            $tag = Plugin::getInstance()->getTagCollection()->prepareTag($tag);

            $purgeEvent = new PurgeEvent([
                'tag' => $tag,
            ]);

            Plugin::getInstance()->trigger(Plugin::EVENT_BEFORE_PURGE, $purgeEvent);

            // Push to queue
            \Craft::$app->getQueue()->push(new PurgeCacheJob([
                    'tag' => $purgeEvent->tag
                ]
            ));

            Plugin::getInstance()->trigger(Plugin::EVENT_AFTER_PURGE, $purgeEvent);
        }

    }
}
