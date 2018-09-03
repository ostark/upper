<?php namespace ostark\upper\handler;

use ostark\upper\Plugin;

/**
 * Class Fallback
 */
class Fallback extends AbstractSelfHandler implements EventHandlerInterface
{

    /**
     * @var \ostark\upper\events\CacheResponseEvent $event
     */
    protected $event;


    public function handle()
    {
        // not tagged?
        if (0 == count($this->event->tags)) {
            return;
        }

        // fulltext or array
        $tags = \Craft::$app->getDb()->getIsMysql()
            ? implode(" ", $this->event->tags)
            : str_replace(['[', ']'], ['{', '}'], json_encode($this->event->tags) ?: '[]');

        // in order to have a unique (collitions are possible) identifier by url with a fixed length
        $urlHash = md5($this->event->requestUrl);

        try {
            // Insert item
            \Craft::$app->getDb()->createCommand()
                ->upsert(
                // Table
                    Plugin::CACHE_TABLE,

                    // Identifier
                    ['urlHash' => $urlHash],

                    // Data
                    [
                        'urlHash' => $urlHash,
                        'url'     => $this->event->requestUrl,
                        'tags'    => $tags,
                        'headers' => json_encode($this->event->headers),
                        'siteId'  => \Craft::$app->getSites()->currentSite->id
                    ]
                )
                ->execute();
        } catch (\Exception $e) {
            \Craft::warning("Failed to register fallback.", "upper");
        }


    }
}
