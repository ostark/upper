<?php namespace ostark\upper\handler;


use ostark\upper\Plugin;

class RegisterCacheOptions extends AbstractPluginEventHandler implements InvokeEventHandlerInterface
{

    /**
     * @param \craft\events\RegisterCacheOptionsEvent $event
     */
    public function __invoke($event)
    {
        $driver = ucfirst($this->plugin->getSettings()->driver);

        $event->options[] = [
            'key'    => 'upper-purge-all',
            'label'  => \Craft::t('upper', 'Upper ({driver})', ['driver' => $driver]),
            'action' => function () {
                Plugin::getInstance()->getPurger()->purgeAll();
            },
        ];
    }
}
