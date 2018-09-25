<?php namespace ostark\upper\handler;

use yii\base\Configurable;

interface InvokeEventHandlerInterface
{

    function __invoke($event);

}
