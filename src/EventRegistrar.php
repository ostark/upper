<?php namespace ostark\upper;

use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\events\ElementEvent;
use craft\events\ElementStructureEvent;
use craft\events\MoveElementEvent;
use craft\events\PopulateElementEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\SectionEvent;
use craft\events\TemplateEvent;
use craft\services\Elements;
use craft\services\Sections;
use craft\services\Structures;
use craft\utilities\ClearCaches;
use craft\web\View;
use ostark\upper\events\CacheResponseEvent;
use ostark\upper\jobs\PurgeByKeys;
use yii\base\Event;


class EventRegistrar
{

    public static function registerUpdateEvents()
    {
        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, function ($event) {
            static::handleUpdateEvent($event);
        });
        Event::on(Element::class, Element::EVENT_AFTER_MOVE_IN_STRUCTURE, function ($event) {
            static::handleUpdateEvent($event);
        });
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, function ($event) {
            static::handleUpdateEvent($event);
        });
        Event::on(Structures::class, Structures::EVENT_AFTER_MOVE_ELEMENT, function ($event) {
            static::handleUpdateEvent($event);
        });
        Event::on(Sections::class, Sections::EVENT_AFTER_SAVE_SECTION, function ($event) {
            static::handleUpdateEvent($event);
        });

    }

    public static function registerFrontendEvents()
    {
        // No need to continue when in cli mode
        if (\Craft::$app instanceof \craft\console\Application) {
            return false;
        }

        // HTTP request object
        $request = \Craft::$app->getRequest();

        // Don't cache CP, LivePreview, Non-GET requests
        if ($request->getIsCpRequest() ||
            $request->getIsLivePreview() ||
            !$request->getIsGet()
        ) {
            return false;
        }

        // Collect tags
        Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT, function (PopulateElementEvent $event) {

            // Don't collect MatrixBlock and User elements for now
            if (!Plugin::getInstance()->getSettings()->isCachableElement(get_class($event->element))) {
                return;
            }
            // Add to collection
            Plugin::getInstance()->getTagCollection()->addTagsFromElement($event->row);

        });

        // Add the tags to the response header
        Event::on(View::class, View::EVENT_AFTER_RENDER_PAGE_TEMPLATE, function (TemplateEvent $event) {

            $plugin   = Plugin::getInstance();
            $response = \Craft::$app->getResponse();
            $tags     = $plugin->getTagCollection()->getAll();
            $settings = $plugin->getSettings();
            $headers  = $response->getHeaders();

            // Make existing cache-control headers accessible
            $response->setCacheControlDirectiveFromString($headers->get('cache-control'));

            // Don't cache if private | no-cache set already
            if ($response->hasCacheControlDirective('private') || $response->hasCacheControlDirective('no-cache')) {
                return;
            }

            // MaxAge or defaultMaxAge?
            $maxAge = $response->getMaxAge() ?? $settings->defaultMaxAge;

            // Set Headers
            $response->setTagHeader($settings->getTagHeaderName(), $tags, $settings->getHeaderTagDelimiter());
            $response->setSharedMaxAge($maxAge);

            $plugin->trigger($plugin::EVENT_AFTER_SET_TAG_HEADER, new CacheResponseEvent([
                    'tags'       => $tags,
                    'maxAge'     => $maxAge,
                    'requestUrl' => \Craft::$app->getRequest()->getUrl(),
                    'headers'    => $response->getHeaders()->toArray(),
                    'output'     => $event->output
                ]
            ));
        });

    }


    public static function registerDashboardEvents()
    {
        // Register cache purge checkbox
        Event::on(
            ClearCaches::class,
            ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            function (RegisterCacheOptionsEvent $event) {
                $event->options[] = [
                    'key'    => 'upper-purge-all',
                    'label'  => \Craft::t('upper', 'Cache Proxy (Upper Plugin)'),
                    'action' => function () {
                        Plugin::getInstance()->getPurger()->purgeAll();
                    },
                ];
            }
        );
    }


    /**
     * @param \yii\base\Event $event
     */
    protected static function handleUpdateEvent(Event $event)
    {
        if ($event instanceof ElementEvent) {
            if (!Plugin::getInstance()->getSettings()->isCachableElement(get_class($event->element))) {
                return;
            }
            $keys = ($event->isNew)
                ? [Plugin::TAG_PREFIX_SECTION . $event->element->sectionId]
                : [Plugin::TAG_PREFIX_ELEMENT . $event->element->getId()];
        }

        if ($event instanceof SectionEvent) {
            $keys = [Plugin::TAG_PREFIX_SECTION . $event->section->id];
        }

        if ($event instanceof MoveElementEvent or $event instanceof ElementStructureEvent) {
            $keys = [Plugin::TAG_PREFIX_STRUCTURE . $event->structureId];
        }

        // Push to queue
        \Craft::$app->getQueue()->push(new PurgeByKeys([
                'keys' => $keys
            ]
        ));
    }

}
