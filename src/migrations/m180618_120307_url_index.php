<?php

namespace ostark\Upper\migrations;

use craft\db\Migration;
use ostark\Upper\Plugin;

/**
 * m180618_120307_url_index migration.
 */
class m180618_120307_url_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        echo "  > Truncate table: " . Plugin::CACHE_TABLE . PHP_EOL;
        $this->truncateTable(Plugin::CACHE_TABLE);

        echo "  > Drop index: url_idx" . PHP_EOL;
        $this->dropIndex('url_idx', Plugin::CACHE_TABLE);

        echo "  > Alter column: 'url' - string to text " . PHP_EOL;
        $this->alterColumn(Plugin::CACHE_TABLE, 'url', $this->text());

        echo "  > Add column: 'urlHash'" . PHP_EOL;
        $this->addColumn(Plugin::CACHE_TABLE, 'urlHash', $this->string(32)->notNull()->after('url'));

        echo "  > Create index: urlhash_idx" . PHP_EOL;

        $this->createIndex('urlhash_idx', Plugin::CACHE_TABLE, 'urlHash', true);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180618_120307_url_index cannot be reverted.\n";
        return false;
    }
}
