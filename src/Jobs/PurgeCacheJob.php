<?php namespace ostark\Upper\Jobs;

use Craft;
use craft\queue\BaseJob;
use ostark\Upper\Plugin;

/**
 * Class PurgeCache
 *
 * @package ostark\Upper\jobs
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
    public function execute($queue)
    {
        if (!$this->tag) {
            return false;
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
