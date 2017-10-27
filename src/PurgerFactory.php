<?php namespace ostark\upper;

use Psr\Log\InvalidArgumentException;
use yii\base\Component;

class PurgerFactory extends Component
{
    const DRIVERS_NAMESPACE = 'ostark\upper\drivers';

    /**
     * @param array $config
     *
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public static function create(array $config = [])
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException("'driver' in config missing");
        }
        if (!isset($config['drivers'][$config['driver']])) {
            throw new InvalidArgumentException("driver '{$config['driver']}' is not configured");
        }
        if (!isset($config['drivers'][$config['driver']]['tagHeaderName'])) {
            throw new InvalidArgumentException("'tagHeaderName' is not configured");
        }

        $driverConfig = $config['drivers'][$config['driver']];
        $driverClass = $driverConfig['class'] ?? self::DRIVERS_NAMESPACE . '\\' . ucfirst($config['driver']);

        // tagHeaderName is not relevant to the Purger
        unset($driverConfig['tagHeaderName']);

        return \Craft::createObject($driverClass,[$driverConfig + ['useLocalTags' => $config['useLocalTags']]]);

    }
}
