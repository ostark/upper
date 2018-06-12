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

        // mysql with fulltext field tags
        if ($this->getDb()->getIsMysql()) {

            $this->createTable(Plugin::CACHE_TABLE, [
                'uid'         => $this->string(40)->notNull()->unique(),
                'url'         => $this->string(2083)->notNull(),
                'headers'     => $this->text()->defaultValue(null),
                'tags'        => $this->text()->notNull(),
                'siteId'      => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->null()
            ]);

            $this->createIndex('url_idx', Plugin::CACHE_TABLE, 'url', true);
            $this->execute("ALTER TABLE " . Plugin::CACHE_TABLE . " ADD FULLTEXT INDEX tags_fulltext (tags ASC)");

        }

        // pgsql with array field tags
        elseif ($this->getDb()->getIsPgsql()) {

            $this->createTable(Plugin::CACHE_TABLE, [
                'uid'         => $this->string(40)->notNull()->unique(),
                'url'         => $this->string(2083)->notNull(),
                'headers'     => $this->text()->defaultValue(null),
                'tags'        => 'varchar[]',
                'siteId'      => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->null(),
                'PRIMARY KEY(uid)',
            ]);

            $this->createIndex('url_idx', Plugin::CACHE_TABLE, 'url', true);
            $this->execute("CREATE INDEX tags_array ON " . Plugin::CACHE_TABLE . " USING GIN(tags)");

        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Plugin::CACHE_TABLE);

    }
}
