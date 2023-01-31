<?php namespace ostark\upper\jobs;

use Craft;
use craft\queue\BaseJob;
use ostark\upper\Plugin;

/**
 * Class PurgeCache
 *
 * @package ostark\upper\jobs
 */
class PurgeCacheJob extends BaseJob
{
    /**
     * @var string tag
     */
    public $tag;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        if (!$this->tag) {
            return;
        }

        // Get registered purger
        $purger = Plugin::getInstance()->getPurger();
        $purger->purgeTag($this->tag);

    }


    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('upper', 'Upper Purge: {tag}', ['tag' => $this->tag]);
    }
}
