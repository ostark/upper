<?php namespace ostark\upper\Contracts;

/**
 * Interface InvokeEventHandlerInterface
 *
 * @package ostark\upper\handler
 */
interface InvokeEventHandlerInterface
{

    function __invoke($event);
}
