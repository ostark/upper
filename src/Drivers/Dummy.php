<?php namespace ostark\Upper\Drivers;

/**
 * Class Dummy Driver
 *
 * @package ostark\Upper\Drivers
 */
class Dummy extends AbstractPurger implements CachePurgeInterface
{

    /**
     * @var bool
     */
    public $logPurgeActions = true;

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function purgeTag(string $tag)
    {
        $this->log("Dummy::purgeTag($tag) was called.");

        if ($this->useLocalTags) {
            $this->purgeUrlsByTag($tag);
        }

        return true;
    }


    /**
     * @param array $urls
     *
     * @return bool
     */
    public function purgeUrls(array $urls)
    {
        $joinedUrls = implode(',', $urls);
        $this->log("Dummy::purgeUrls([$joinedUrls]') was called.");

        return true;
    }


    /**
     * @return bool
     */
    public function purgeAll()
    {
        if ($this->useLocalTags) {
            $this->clearLocalCache();
        }

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
