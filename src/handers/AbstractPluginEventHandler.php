<?php namespace ostark\upper\handlers;

use ostark\upper\Plugin;

/**
 * Class AbstractPluginEventHandler
 *
 * @package ostark\upper\handler
 */
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
