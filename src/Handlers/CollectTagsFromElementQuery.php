<?php namespace ostark\Upper\Handlers;

use craft\events\PopulateElementEvent;
use ostark\Upper\Models\PluginSettings;
use ostark\Upper\Plugin;
use ostark\Upper\TagCollection;

class CollectTagsFromElementQuery
{
    protected PluginSettings $settings;
    protected TagCollection $tags;

    public function __construct(PluginSettings $settings, TagCollection $tags)
    {
        $this->settings = $settings;
        $this->tags = $tags;
    }

    public function __invoke(PopulateElementEvent $event): void
    {
        // Don't collect MatrixBlock and User elements for now
        if (!$this->settings->isCachableElement(get_class($event->element))) {
            return;
        }

        // Tag with GlobalSet handle
        if ($event->element instanceof \craft\elements\GlobalSet) {
            $this->tags->add($event->element->handle);
        }

        // Add to collection
        $this->tags->addTagsFromElement($event->row);
    }
}
