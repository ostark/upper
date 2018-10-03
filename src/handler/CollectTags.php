<?php namespace ostark\upper\handler;

/**
 * Class CollectTags
 *
 * @package ostark\upper\handler
 */
class CollectTags extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
{
    /**
     * @param \craft\events\PopulateElementEvent $event
     */
    public function __invoke($event)
    {
        // Don't collect MatrixBlock and User elements for now
        if (!$this->plugin->getSettings()->isCachableElement(get_class($event->element))) {
            return;
        }

        // Tag with GlobalSet handle
        if ($event->element instanceof \craft\elements\GlobalSet) {
            $this->plugin->getTagCollection()->add($event->element->handle);
        }

        // Avoid tagging sections for single entry
        if (array_key_exists('uri', $event->row)) {
            if ($this->plugin->requestUri == $event->row['uri']) {
                $event->row['sectionId'] = null;
            }
        }

        // Add to collection
        $this->plugin->getTagCollection()->addTagsFromElement($event->row);
    }
}
