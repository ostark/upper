<?php namespace ostark\upper\drivers;

use yii\base\Object;

/**
 * Class AbstractPurger Driver
 *
 * @package ostark\upper\drivers
 */
class AbstractPurger extends Object
{
    public function __construct($config)
    {
        // assign config to object properties
        parent::__construct($config);
    }
}
