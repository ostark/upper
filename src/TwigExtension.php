<?php namespace ostark\upper;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class TwigExtension extends AbstractExtension implements GlobalsInterface
{

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return [
            'upper' => [
                'cache' => \Craft::createObject(CacheResponse::class, [\Craft::$app->getResponse()]),
            ]
        ];
    }
}
