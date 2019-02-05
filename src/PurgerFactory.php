<?php namespace ostark\upper;

use ostark\upper\Models\Settings;
use Psr\Log\InvalidArgumentException;
use yii\base\Component;

class PurgerFactory extends Component
{
    const DRIVERS_NAMESPACE = 'ostark\upper\drivers';

    /**
     * @param \ostark\upper\Models\Settings $settings
     *
     * @return \ostark\upper\Contracts\CachePurgeInterface|object
     * @throws \yii\base\InvalidConfigException
     */
    public static function create(Settings $settings)
    {
        if (!$settings->driver) {
            throw new InvalidArgumentException("'driver' in config missing");
        }

        if (!isset($settings->drivers[$settings->driver])) {
            throw new InvalidArgumentException("driver '{$settings->driver}' is not configured");
        }

        if (!isset($settings->drivers[$settings->driver]['tagHeaderName'])) {
            throw new InvalidArgumentException("'tagHeaderName' is not configured");
        }

        $driverConfig = $settings->drivers[$settings->driver];

        // Predefined or custom driver class?
        $driverClass = $driverConfig['class'] ?? self::DRIVERS_NAMESPACE . '\\' . ucfirst($settings->driver);

        // tagHeaderName and tagHeaderDelimiter are not relevant to the Purger
        unset($driverConfig['tagHeaderName'], $driverConfig['tagHeaderDelimiter']);

        return \Craft::createObject($driverClass, [$driverConfig + ['useLocalTags' => $settings->useLocalTags]]);
    }
}
