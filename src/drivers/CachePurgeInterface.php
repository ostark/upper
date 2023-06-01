<?php namespace ostark\upper\drivers;


/**
 * Interface CachePurgeInterface
 *
 * @package ostark\upper\drivers
 */
interface CachePurgeInterface
{
    /**
     * @return bool
     */
    public function purgeTag(string $tag);

    /**
     * @return bool
     */
    public function purgeUrls(array $urls);


    /**
     * @return bool
     */
    public function purgeAll();

}
