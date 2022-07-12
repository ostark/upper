<?php namespace ostark\Upper\Drivers;


/**
 * Interface CachePurgeInterface
 *
 * @package ostark\Upper\Drivers
 */
interface CachePurgeInterface
{
    /**
     * @param string $tag
     *
     * @return bool
     */
    public function purgeTag(string $tag);

    /**
     * @param array $urls
     *
     * @return bool
     */
    public function purgeUrls(array $urls);


    /**
     * @return bool
     */
    public function purgeAll();

}
