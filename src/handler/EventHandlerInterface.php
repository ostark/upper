<?php namespace ostark\upper\handler;

interface EventHandlerInterface
{

    function __invoke(\yii\base\Event $event);

}
