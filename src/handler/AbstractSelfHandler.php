<?php namespace ostark\upper\handler;

use ostark\upper\Plugin;
use yii\base\Event;

abstract class AbstractSelfHandler
{
    /**
     * @var \yii\base\Event
     */
    protected $event;

    /**
     * @var \ostark\upper\Plugin $plugin
     */
    protected $plugin;

    /**
     * AbstractSelfHandler constructor.
     *
     * @param \yii\base\Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->plugin = Plugin::getInstance();
    }

}
