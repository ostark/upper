<?php namespace ostark\Upper\Handlers;

use craft\events\RegisterCacheOptionsEvent;
use ostark\Upper\Drivers\CachePurgeInterface;
use ostark\Upper\Models\PluginSettings;
use ostark\Upper\Plugin;

class RegisterCacheCheckbox
{
    protected PluginSettings $settings;
    protected CachePurgeInterface $purger;

    public function __construct(PluginSettings $settings, CachePurgeInterface $purger)
    {
        $this->settings = $settings;
        $this->purger = $purger;
    }

    public function __invoke(RegisterCacheOptionsEvent $event, CachePurgeInterface $purger): void
    {
        $driver = ucfirst($this->settings->driver);
        $event->options[] = [
            'key' => 'upper-purge-all',
            'label' => \Craft::t('upper', 'Upper ({driver})', ['driver' => $driver]),
            'action' => function () {
                $this->purger->purgeAll();
            },
        ];


    }
}
