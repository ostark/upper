<?php namespace ostark\Upper;

use Psr\Log\InvalidArgumentException;
use yii\base\Component;

class PurgerFactory extends Component
{
    const DRIVERS_NAMESPACE = 'ostark\Upper\Drivers';

    /**
     * @param array $config
     *
     * @return \ostark\Upper\Drivers\CachePurgeInterface
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

        // tagHeaderName and tagHeaderDelimiter are not relevant to the Purger
        unset($driverConfig['tagHeaderName'], $driverConfig['tagHeaderDelimiter']);
        return \Craft::createObject($driverClass,[$driverConfig + ['useLocalTags' => $config['useLocalTags']]]);

    }
}
