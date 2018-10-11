<?php namespace ostark\upper\handlers;

use ostark\upper\events\CacheResponseEvent;
use ostark\upper\Plugin;

/**
 * Class CacheTagResponse
 *
 * @package ostark\upper\handler
 */
class AddCacheTagResponseHeader extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
{
    /***
     * @param \yii\base\Event $event
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function __invoke($event)
    {

        /** @var \yii\web\Response|\ostark\upper\behaviors\CacheControlBehavior|\ostark\upper\behaviors\TagHeaderBehavior $response */
        $response = \Craft::$app->getResponse();
        $tags     = $this->plugin->getTagCollection()->getAll();
        $settings = $this->plugin->getSettings();
        $headers  = $response->getHeaders();

        // Make existing cache-control headers accessible
        $response->setCacheControlDirectiveFromString($headers->get('cache-control'));

        // Don't cache if private | no-cache set already
        if ($response->hasCacheControlDirective('private') || $response->hasCacheControlDirective('no-cache')) {
            $headers->set(Plugin::INFO_HEADER_NAME, 'BYPASS');

            return;
        }

        // MaxAge or defaultMaxAge?
        $maxAge = $response->getMaxAge() ?? $settings->defaultMaxAge;

        // Set Headers
        $response->setTagHeader($settings->getTagHeaderName(), $tags, $settings->getHeaderTagDelimiter());
        $response->setSharedMaxAge($maxAge);
        $headers->set(Plugin::INFO_HEADER_NAME, "CACHED: " . date(\DateTime::ISO8601));

        $this->plugin->trigger(Plugin::EVENT_AFTER_SET_TAG_HEADER, new CacheResponseEvent([
                'tags'       => $tags,
                'maxAge'     => $maxAge,
                'requestUrl' => \Craft::$app->getRequest()->getUrl(),
                'headers'    => $response->getHeaders()->toArray()
            ]
        ));

    }
}
