<?php namespace ostark\upper\EventHandlers;

/**
 * Interface InvokeEventHandlerInterface
 *
 * @package ostark\upper\handler
 */
interface InvokeEventHandlerInterface
{

    function __invoke($event);
}
