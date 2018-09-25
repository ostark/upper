<?php namespace ostark\upper\handler;



use ostark\upper\events\CacheResponseEvent;
use ostark\upper\Plugin;
use yii\base\Event;

class CacheTagResponse extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
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
        $plugin   = Plugin::getInstance();
        $tags     = $plugin->getTagCollection()->getAll();
        $settings = $plugin->getSettings();
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

        $plugin->trigger($plugin::EVENT_AFTER_SET_TAG_HEADER, new CacheResponseEvent([
                'tags'       => $tags,
                'maxAge'     => $maxAge,
                'requestUrl' => \Craft::$app->getRequest()->getUrl(),
                'headers'    => $response->getHeaders()->toArray()
            ]
        ));

    }
}
