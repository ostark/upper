<?php

declare(strict_types=1);

use craft\test\TestSetup;

ini_set('date.timezone', 'UTC');
date_default_timezone_set('UTC');

// Use the current installation of Craft
define('CRAFT_TESTS_PATH', __DIR__);
define('CRAFT_STORAGE_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_craft' . DIRECTORY_SEPARATOR . 'storage');
define('CRAFT_TEMPLATES_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_craft' . DIRECTORY_SEPARATOR . 'templates');
define('CRAFT_CONFIG_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_craft' . DIRECTORY_SEPARATOR . 'config');
define('CRAFT_MIGRATIONS_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_craft' . DIRECTORY_SEPARATOR . 'migrations');
define('CRAFT_TRANSLATIONS_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_craft' . DIRECTORY_SEPARATOR . 'translations');
define('CRAFT_VENDOR_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor'));

$devMode = true;
$environment = 'test';
TestSetup::configureCraft();


require_once __DIR__ . '/../vendor/autoload.php';


$configService = new \craft\services\Config();
$configService->env = $environment;
$configService->configDir = CRAFT_CONFIG_PATH;
$configService->appDefaultsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'defaults';
$generalConfig = $configService->getConfigFromFile('general');

$config = \craft\helpers\ArrayHelper::merge(
    [
        'vendorPath' => CRAFT_VENDOR_PATH,
        'env' => $environment,
        'components' => [
            'config' => $configService,
        ],
        'id' => 'test',
        'basePath' => __DIR__,
        'class' => craft\console\Application::class,
    ],
    require 'vendor/craftcms/cms/src/config/app.php',
    require 'vendor/craftcms/cms/src/config/app.console.php',
    $configService->getConfigFromFile('app'),
);

// Initialize the application
/** @var \craft\web\Application|craft\console\Application $app */
$app = Craft::createObject($config);


// Load and run Craft
/** @var craft\console\Application $app */
\Craft::$app = $app;
