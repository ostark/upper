<?php namespace ostark\upper\handler;

use yii\base\Configurable;

interface EventHandlerInterface
{

    function __invoke($event);

}
