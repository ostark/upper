<?php

if (!defined('CRAFT_BASE_PATH')) {
    define('CRAFT_BASE_PATH', __DIR__);
    define('YII_DEBUG', true);
    define('TEST_FILE', '/tmp/craft-async-queue.txt');
}


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__.'/../vendor/yiisoft/yii2/Yii.php';
require_once __DIR__.'/../vendor/craftcms/cms/src/Craft.php';

