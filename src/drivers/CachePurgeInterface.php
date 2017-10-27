<?php namespace ostark\upper\drivers;


/**
 * Interface CachePurgeInterface
 *
 * @package ostark\upper\drivers
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
