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

        $this->dropTableIfExists(Plugin::CACHE_TABLE);

        // mysql with fulltext field tags
        if ($this->getDb()->getIsMysql()) {

            $this->createTable(Plugin::CACHE_TABLE, [
                'uid'         => $this->string(40)->notNull()->unique(),
                'url'         => $this->text()->notNull(),
                'urlHash'     => $this->string(32)->notNull(),
                'headers'     => $this->text()->defaultValue(null),
                'tags'        => $this->text()->notNull(),
                'siteId'      => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->null()
            ]);

            echo "  > Create index: urlhash_idx" . PHP_EOL;
            $this->createIndex('urlhash_idx', Plugin::CACHE_TABLE, 'urlHash', true);

            $this->execute("ALTER TABLE " . Plugin::CACHE_TABLE . " ADD FULLTEXT INDEX tags_fulltext (tags)");

        }

        // pgsql with array field tags
        elseif ($this->getDb()->getIsPgsql()) {

            $this->createTable(Plugin::CACHE_TABLE, [
                'uid'         => $this->string(40)->notNull()->unique(),
                'url'         => $this->text()->notNull(),
                'urlHash'     => $this->string(32)->notNull(),
                'headers'     => $this->text()->defaultValue(null),
                'tags'        => 'varchar[]',
                'siteId'      => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->null(),
                'PRIMARY KEY(uid)',
            ]);

            echo "  > Create index: urlhash_idx" . PHP_EOL;
            $this->createIndex('urlhash_idx', Plugin::CACHE_TABLE, 'urlHash', true);
            $this->createIndex('uid_urlhash_ids', Plugin::CACHE_TABLE, 'uid,urlHash', true);

            $this->execute("CREATE INDEX tags_array ON " . Plugin::CACHE_TABLE . " USING GIN(tags)");

        }

        return true;

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Plugin::CACHE_TABLE);
        return true;
    }
}
