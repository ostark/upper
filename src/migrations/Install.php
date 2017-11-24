<?php namespace ostark\upper\migrations;

use craft\db\Migration;
use ostark\upper\Plugin;



/**
 * Install migration.
 */
class Install extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Plugin::CACHE_TABLE, [
            'uid'         => $this->string(32)->notNull()->unique(),
            'url'         => $this->string(255)->notNull(),
            'body'        => $this->mediumText()->defaultValue(null),
            'headers'     => $this->text()->defaultValue(null),
            'tags'        => $this->string(255)->notNull(),
            'siteId'      => $this->integer(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->null()
        ]);

        // Mysql full text index
        if ($this->getDb()->getIsMysql()) {
            $this->createIndex('url_idx', Plugin::CACHE_TABLE, 'url', true);
            $this->execute("ALTER TABLE " . Plugin::CACHE_TABLE . " ADD FULLTEXT INDEX tags_fulltext (tags ASC)");
        }

        // Pgsql full text index
        // if ($this->getDb()->getIsPgsql()) {
        //    $this->execute("CREATE INDEX tags_fulltext ON " . Plugin::CACHE_TABLE . " USING GIN (tags)");
        // }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Plugin::CACHE_TABLE);

    }
}
