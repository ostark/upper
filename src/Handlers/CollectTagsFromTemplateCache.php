<?php namespace ostark\Upper\Handlers;

use ostark\Upper\Models\PluginSettings;

class CollectTagsFromTemplateCache
{
    protected PluginSettings $settings;

    public function __construct(PluginSettings $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(): void
    {
    }
}
