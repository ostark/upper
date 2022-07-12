<?php

if (!function_exists('tags')) {
   function tags(): \ostark\Upper\TagCollection {
       Craft::createObject(\ostark\Upper\TagCollection::class);
   }
}

if (!function_exists('purger')) {
    function purger(): \ostark\Upper\Drivers\CachePurgeInterface {
        Craft::createObject(\ostark\Upper\Drivers\CachePurgeInterface::class);
    }
}
