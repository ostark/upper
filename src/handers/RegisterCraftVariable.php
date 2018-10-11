<?php namespace ostark\upper\handlers;

use craft\elements\db\ElementQuery;

class RegisterCraftVariable extends AbstractPluginEventHandler
{
    /**
     * @param \yii\base\Event $event
     */
    public function __invoke($event)
    {
        /** @var \craft\web\twig\variables\CraftVariable $var */
        $var = $event->sender;
        $var->set('upper', $this->plugin);
    }
}

/*
{{ craft.upper.disable() }}

{{ craft.upper.enable() }}

{{ craft.upper.pause(true) }}

{{ craft.upper.unpause() }}

{{ craft.upper.only() }}

{{ craft.upper.reset() }}

*/
