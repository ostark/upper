<?php namespace ostark\upper\handler;

use craft\events\ElementEvent;
use craft\events\ElementStructureEvent;
use craft\events\MoveElementEvent;
use craft\events\SectionEvent;
use ostark\upper\jobs\PurgeCacheJob;
use ostark\upper\Plugin;

class UpdateEvent extends AbstractSelfHandler implements EventHandlerInterface
{
    /**
     * @param \yii\base\Event $event
     */
    public function __invoke(\yii\base\Event $event)
    {
        $tags = [];

        if ($this->event instanceof ElementEvent) {

            if (!$this->plugin->getSettings()->isCachableElement(get_class($this->event->element))) {
                return;
            }

            if ($this->event->element instanceof \craft\elements\GlobalSet && is_string($this->event->element->handle)) {
                $tags[] = $this->event->element->handle;
            } elseif ($this->event->element instanceof \craft\elements\Asset && $this->event->isNew) {
                $tags[] = (string)$this->event->element->volumeId;
            } else {
                if (isset($this->event->element->sectionId)) {
                    $tags[] = Plugin::TAG_PREFIX_SECTION . $this->event->element->sectionId;
                }
                if (!$this->event->isNew) {
                    $tags[] = Plugin::TAG_PREFIX_ELEMENT . $this->event->element->getId();
                }
            }

        }

        if ($this->event instanceof SectionEvent) {
            $tags[] = Plugin::TAG_PREFIX_SECTION . $this->event->section->id;
        }

        if ($this->event instanceof MoveElementEvent or $this->event instanceof ElementStructureEvent) {
            $tags[] = Plugin::TAG_PREFIX_STRUCTURE . $this->event->structureId;
        }

        if (count($tags) === 0) {
            $type = get_class($this->event);
            \Craft::warning("Unabled to find tag. Unknown Event '$type'.", "upper");

            return;
        }

        foreach ($tags as $tag) {
            $tag = Plugin::getInstance()->getTagCollection()->prepareTag($tag);
            // Push to queue
            \Craft::$app->getQueue()->push(new PurgeCacheJob([
                    'tag' => $tag
                ]
            ));
        }

    }
}
