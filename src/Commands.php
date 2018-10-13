<?php

namespace ostark\upper;

use craft\helpers\ArrayHelper;
use craft\helpers\Console;
use yii\base\InvalidArgumentException;
use yii\console\Controller as BaseConsoleController;
use yii\console\ExitCode;
use yii\console\widgets\Table;

/**
 * Cli Commands
 */
class Commands extends BaseConsoleController
{

    /**
     * Checks for scheduled Entries
     */
    public function actionScheduled()
    {

    }

    /**
     * Purges a tag manually
     *
     * @param array $tags Tags (multiple separate with comma)
     */
    public function actionPurge(array $tags = [])
    {
        if (count($tags) === 0) {
            Plugin::getInstance()->getPurger()->purgeAll();
            return ExitCode::OK;
        }

        foreach ($tags as $tag) {
            if (!(Plugin::getInstance()->getPurger()->purgeTag($tag))) {
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }

        return ExitCode::OK;
    }
}
