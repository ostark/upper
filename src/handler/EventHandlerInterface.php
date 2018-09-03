<?php namespace ostark\upper\handler;

interface EventHandlerInterface
{

    function __construct(\yii\base\Event $event);

    function handle();

}
