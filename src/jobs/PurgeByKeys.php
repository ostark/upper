<?php namespace ostark\upper\jobs;

use Craft;
use craft\queue\BaseJob;
use ostark\upper\Plugin;

/**
 * Class PurgeByKeys
 *
 * @package ostark\upper\jobs
 */
class PurgeByKeys extends BaseJob
{
    /**
     * @var array keys
     */
    public $keys = [];

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        // Get registered purger
        $purger = Plugin::getInstance()->getPurger();
        $purger->purgeByKeys($this->keys);

    }


    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        $keys = implode(', ', $this->keys);

        return Craft::t('upper', 'Purge Keys: {keys}', ['keys' => $keys]);
    }
}
