<?php namespace ostark\Upper\Handlers;

use craft\events\TemplateEvent;
use ostark\Upper\Events\CacheResponseEvent;
use ostark\Upper\Models\PluginSettings;
use ostark\Upper\Plugin;
use ostark\Upper\TagCollection;

class AddCacheResponse
{
    protected PluginSettings $settings;
    protected TagCollection $tags;


    public function __construct(PluginSettings $settings, TagCollection $tags)
    {
        $this->settings = $settings;
        $this->tags = $tags;
    }

    public function __invoke(TemplateEvent $event): void
    {
        /** @var \yii\web\Response $response */
        $response      = \Craft::$app->getResponse();
        $plugin        = Plugin::getInstance();
        $tagCollection = $this->tags;
        $tags          = $this->tags->getAll();
        $headers       = $response->getHeaders();

        // Make existing cache-control headers accessible
        $response->setCacheControlDirectiveFromString($headers->get('cache-control'));

        // Don't cache if private | no-cache set already
        if ($response->hasCacheControlDirective('private') || $response->hasCacheControlDirective('no-cache')) {
            $headers->set(Plugin::INFO_HEADER_NAME, 'BYPASS');

            return;
        }

        // MaxAge or defaultMaxAge?
        $maxAge = $response->getMaxAge() ?? $this->settings->defaultMaxAge;

        // Set Headers
        $maxBytes = $this->settings->maxBytesForCacheTagHeader;
        $maxedTags = $tagCollection->getUntilMaxBytes($maxBytes);
        $response->setTagHeader($this->settings->getTagHeaderName(), $maxedTags, $this->settings->getHeaderTagDelimiter());

        // Flag truncation
        if (count($tags) > count($maxedTags)) {
            $headers->set(Plugin::TRUNCATED_HEADER_NAME, count($tags) - count($maxedTags));
        }

        $response->setSharedMaxAge($maxAge);
        $headers->set(Plugin::INFO_HEADER_NAME, "CACHED: " . date(\DateTime::ISO8601));

        $plugin->trigger($plugin::EVENT_AFTER_SET_TAG_HEADER, new CacheResponseEvent([
                'tags'       => $tags,
                'maxAge'     => $maxAge,
                'requestUrl' => \Craft::$app->getRequest()->getUrl(),
                'headers'    => $response->getHeaders()->toArray()
            ]
        ));

    }
}
