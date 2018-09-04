<?php namespace ostark\upper\handler;


class RegisterCacheOptions extends AbstractSelfHandler implements EventHandlerInterface
{
    /**
     * @var \craft\events\RegisterCacheOptionsEvent $event
     */
    protected $event;

    /**
     * @param \yii\base\Event $event
     */
    public function __invoke(\yii\base\Event $event)
    {
        $driver                 = ucfirst($this->plugin->getSettings()->driver);
        $this->event->options[] = [
            'key'    => 'upper-purge-all',
            'label'  => \Craft::t('upper', 'Upper ({driver})', ['driver' => $driver]),
            'action' => function () {
                Plugin::getInstance()->getPurger()->purgeAll();
            },
        ];

    }
}
