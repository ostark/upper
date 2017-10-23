<?php namespace ostark\upper\drivers;

/**
 * Class Dummy Driver
 *
 * @package ostark\upper\drivers
 */
class Dummy extends AbstractPurger implements CachePurgeInterface
{

    /**
     * @var bool
     */
    public $logPurgeActions = true;

    /**
     * @param array $keys
     *
     * @return bool
     */
    public function purgeByKeys(array $keys)
    {
        $joinedKeys = implode(', ', $keys);
        $this->log("Dummy::purgeByUrl([{$joinedKeys}]) was called.");

        return true;
    }


    /**
     * @param string $url
     *
     * @return bool
     */
    public function purgeByUrl(string $url)
    {
        $this->log("Dummy::purgeByUrl('{$url}'') was called.");

        return true;
    }


    /**
     * @return bool
     */
    public function purgeAll()
    {
        $this->log("Dummy::purgeAll() was called.");

        return true;
    }


    /**
     * @param string|null $message
     */
    protected function log(string $message = null)
    {
        if (!$this->logPurgeActions) {
            return;
        }

        \Craft::warning($message, "upper");
    }

}
