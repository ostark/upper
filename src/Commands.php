<?php

namespace ostark\upper;

use craft\elements\Entry;
use craft\helpers\Console;
use craft\helpers\Db;
use yii\base\Module;
use yii\caching\CacheInterface;
use yii\console\Controller as BaseConsoleController;
use yii\console\ExitCode;
use yii\console\widgets\Table;

/**
 * Cli Commands
 */
class Commands extends BaseConsoleController
{

    const LAST_CHECK_CACHE_KEY = 'upper.lastScheduledCheck';
    const LAST_CHECK_DEFAULT_INTERVAL = '-24 hours';
    const DATE_FORMAT = 'Y-m-d H:i';

    /**
     * @var \yii\caching\CacheInterface
     */
    protected $cache;

    /**
     * Commands constructor.
     *
     * @param string                      $id
     * @param \yii\base\Module            $module
     * @param \yii\caching\CacheInterface $cache
     * @param array                       $config
     */
    public function __construct(string $id, Module $module, CacheInterface $cache, array $config = [])
    {
        $this->cache = $cache;
        parent::__construct($id, $module, $config);
    }


    /**
     * Checks for scheduled Entries
     *
     * @param int    $delayInSeconds      Seconds between purge calls, to prevent rate limit errors
     * @param string $forcedCheckInterval Time string of lower bound of the range, e.g. '-2 hours'
     *
     *
     * @return int
     * @throws \Exception
     */
    public function actionScheduled(int $delayInSeconds = 1, $forcedCheckInterval = null)
    {
        $lastCheck = $this->cache->exists(self::LAST_CHECK_CACHE_KEY)
            ? $this->cache->get(self::LAST_CHECK_CACHE_KEY)
            : Db::prepareDateForDb((new \DateTime())->modify(self::LAST_CHECK_DEFAULT_INTERVAL));

        if ($forcedCheckInterval) {
            $lastCheck = Db::prepareDateForDb((new \DateTime())->modify($forcedCheckInterval));
        }

        $now       = Db::prepareDateForDb(new \DateTime());
        $published = $this->getPublishedEntries($lastCheck, $now);
        $expired   = $this->getExpiredEntries($lastCheck, $now);
        $entries   = array_merge($published, $expired);
        $rows      = [];

        // Remember this check
        $this->cache->set(self::LAST_CHECK_CACHE_KEY, $now);

        // Print info
        $this->stdout(sprintf("> Expired Entries: %d" . PHP_EOL, count($expired)), Console::FG_GREY);
        $this->stdout(sprintf("> Published Entries: %d" . PHP_EOL, count($published)), Console::FG_GREY);
        $this->stdout(sprintf("> Range: %s to %s" . PHP_EOL, $lastCheck, $now), Console::FG_CYAN);

        if (!count($entries)) {
            return ExitCode::OK;
        }

        foreach ($entries as $entry) {
            /** @var Entry $entry */
            $postDateString   = $entry->postDate ? $entry->postDate->format(self::DATE_FORMAT) : '-';
            $expiryDateString = $entry->expiryDate ? $entry->expiryDate->format(self::DATE_FORMAT) : '-';
            $tags[]           = $tag = Plugin::TAG_PREFIX_ELEMENT . $entry->id;
            $rows[]           = [$tag, $entry->title, $postDateString, $expiryDateString];
        };

        echo Table::widget([
            'headers' => ['Tag', 'Title', 'PostDate', 'ExpiryDate'],
            'rows'    => $rows,
        ]);

        foreach ($tags as $tag) {
            Plugin::getInstance()->getPurger()->purgeTag($tag);
            sleep($delayInSeconds);
        }
    }

    /**
     * Purges a tag manually
     *
     * @param array $tags Tags (multiple separate with comma)
     */
    public function actionPurge(array $tags = [])
    {
        // Purge all
        if (count($tags) === 0) {
            if (Console::confirm("Do you want to purge all tags?")) {
                if (Plugin::getInstance()->getPurger()->purgeAll()) {
                    $this->stdout("Cache purged." . PHP_EOL, Console::FG_GREEN);
                    return ExitCode::OK;
                }
                $this->stdout("Purge Error." . PHP_EOL, Console::FG_RED);
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Purge tag(s)
        foreach ($tags as $tag) {
            if (!(Plugin::getInstance()->getPurger()->purgeTag($tag))) {
                $this->stdout("Unable to purge tag: $tag" . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }

        $this->stdout(sprintf("Cache tag purged: %s", implode(', ', $tags)) . PHP_EOL, Console::FG_GREEN);
        return ExitCode::OK;
    }


    /**
     * @param $rangeStart
     * @param $rangeEnd
     *
     * @return array
     */
    protected function getPublishedEntries($rangeStart, $rangeEnd): array
    {
        // Entries published within time frame
        $entries = (Entry::find()
            ->where(['not', ['postDate' => null]])
            ->andWhere(['between', 'postDate', $rangeStart, $rangeEnd])
            ->withStructure(false)
            ->orderBy(null)
            ->anyStatus())->all();

        // Exclude manually published entries (postDate ≅ dateUpdated)
        return array_filter($entries, function (Entry $item) {
            $diffInSeconds = abs($item->postDate->getTimestamp() - $item->dateUpdated->getTimestamp());
            return ($diffInSeconds > 60);
        });
    }

    /**
     * @param $rangeStart
     * @param $rangeEnd
     *
     * @return Entry[]
     */
    protected function getExpiredEntries($rangeStart, $rangeEnd): array
    {
        return (Entry::find()
            ->where(['not', ['expiryDate' => null]])
            ->andWhere(['between', 'expiryDate', $rangeStart, $rangeEnd])
            ->withStructure(false)
            ->orderBy(null)
            ->anyStatus()
        )->all();
    }
}
