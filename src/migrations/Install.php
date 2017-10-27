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

        $this->createIndex('url_idx', Plugin::CACHE_TABLE, 'url', true);
        $this->execute("ALTER TABLE " . Plugin::CACHE_TABLE . " ADD FULLTEXT INDEX tags_fulltext (tags ASC)");

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Plugin::CACHE_TABLE);

    }
}
