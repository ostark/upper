<?php namespace ostark\upper\handlers;


use craft\elements\db\ElementQuery;

class CraftVariable extends AbstractPluginEventHandler
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
{{ craft.upper.off() }}

{{ craft.upper.on() }}

{{ craft.upper.only() }}

{{ craft.upper.reset() }}

*/
