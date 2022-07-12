<?php

namespace ostark\Upper\Handlers;

use ostark\Upper\Events\CacheResponseEvent;
use ostark\Upper\Models\PluginSettings;
use ostark\Upper\Plugin;
use ostark\Upper\TagCollection;

class StoreTagUrlRelation
{



    public function __invoke(CacheResponseEvent $event): void
    {
        // not tagged?
        if (0 == count($event->tags)) {
            return;
        }

        // fulltext or array
        $tags = \Craft::$app->getDb()->getIsMysql()
            ? implode(" ", $event->tags)
            : str_replace(['[', ']'], ['{', '}'], json_encode($event->tags) ?: '[]');

        // in order to have a unique (collitions are possible) identifier by url with a fixed length
        $urlHash = md5($event->requestUrl);

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
                        'url'     => $event->requestUrl,
                        'tags'    => $tags,
                        'headers' => json_encode($event->headers),
                        'siteId'  => \Craft::$app->getSites()->currentSite->id
                    ]
                )
                ->execute();
        } catch (\Exception $e) {
            \Craft::warning("Failed to register fallback.", "upper");
        }

    }
}
