<?php namespace ostark\upper\handlers;

/**
 * Interface InvokeEventHandlerInterface
 *
 * @package ostark\upper\handler
 */
interface InvokeEventHandlerInterface
{

    function __invoke($event);
}
