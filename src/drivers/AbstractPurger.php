<?php namespace ostark\upper\drivers;

use ostark\upper\Plugin;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractPurger Driver
 *
 * @package ostark\upper\drivers
 */
class AbstractPurger extends Object
{
    public function __construct($config)
    {
        // assign config to object properties
        parent::__construct($config);
    }


    public function getUrls($tag)
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
        $results = \Craft::$app->getDb()->createCommand($sql)->queryAll();

        if (count($results)) {
            $results = ArrayHelper::map($results, 'uid', 'url');
        }

        /*
         * [
            '7cadd899-3d21-4d67-bb74-b30084df' => '/'
            '2481f019-27a4-4338-8784-10d1781b' => '/services/development'
            'a139aa60-9b56-43b0-a9f5-bfaa7c68' => '/services/app-development'
          ]
         */

        return $results;

    }

}
