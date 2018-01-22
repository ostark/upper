<?php namespace ostark\upper\drivers;

use ostark\upper\Plugin;
use yii\base\Object;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractPurger Driver
 *
 * @package ostark\upper\drivers
 */
class AbstractPurger extends Object
{
    /**
     * @var bool
     */
    public $useLocalTags;

    public function __construct($config)
    {
        // assign config to object properties
        parent::__construct($config);
    }


    /**
     * @param string $tag
     *
     * @return bool
     */
    public function purgeUrlsByTag(string $tag)
    {
        try {
            if ($urls = $this->getTaggedUrls($tag)) {
                $this->purgeUrls(array_values($urls));
                $this->invalidateLocalCache(array_keys($urls));
                return true;
            }
        } catch (Exception $e) {
            \Craft::warning("Failed to purge '$tag'.", "upper");
        }
    }

    /**
     * Get cached urls by given tag
     *
     * Example result
     * [
     *   '2481f019-27a4-4338-8784-10d1781b' => '/services/development'
     *   'a139aa60-9b56-43b0-a9f5-bfaa7c68' => '/services/app-development'
     * ]
     *
     * @param $tag
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function getTaggedUrls($tag)
    {
        if (!\Craft::$app->getDb()->getIsMysql()) {
            return;
        };

        $sql = sprintf(
            'SELECT uid, url FROM %s WHERE MATCH(tags) AGAINST ("%s" IN BOOLEAN MODE)',
            \Craft::$app->getDb()->quoteTableName(Plugin::CACHE_TABLE),
            $tag
        );

        // Execute the sql
        $results = \Craft::$app->getDb()
            ->createCommand($sql)
            ->queryAll();

        if (count($results) === 0) {
            return [];
        }

        return ArrayHelper::map($results, 'uid', 'url');

    }

    /**
     * @param array $uids
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function invalidateLocalCache(array $uids)
    {
        if (!\Craft::$app->getDb()->getIsMysql()) {
            return 0;
        };

        return \Craft::$app->getDb()->createCommand()
            ->delete(Plugin::CACHE_TABLE, ['uid' => $uids])
            ->execute();

    }


    /***
     * @return int
     * @throws \yii\db\Exception
     */
    public function clearLocalCache()
    {
        if (!\Craft::$app->getDb()->getIsMysql()) {
            return 0;
        };

        return \Craft::$app->getDb()->createCommand()
            ->delete(Plugin::CACHE_TABLE)
            ->execute();

    }

}
