<?php namespace ostark\upper\handler;

use ostark\upper\Plugin;
use yii\base\Event;

abstract class AbstractPluginEventHandler
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
     */
    public function __construct()
    {
        $this->plugin = Plugin::getInstance();
    }

}
