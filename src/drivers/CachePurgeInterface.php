<?php namespace ostark\upper\drivers;


/**
 * Interface CachePurgeInterface
 *
 * @package ostark\upper\drivers
 */
interface CachePurgeInterface
{
    /**
     * @param array $keys
     *
     * @return bool
     */
    public function purgeByKeys(array $keys);

    /**
     * @param string $url
     *
     * @return bool
     */
    public function purgeByUrl(string $url);


    /**
     * @return bool
     */
    public function purgeAll();

}
