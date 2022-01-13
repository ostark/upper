<?php namespace ostark\upper;


use Craft;
use craft\base\Plugin as BasePlugin;
use ostark\upper\behaviors\CacheControlBehavior;
use ostark\upper\behaviors\TagHeaderBehavior;
use ostark\upper\drivers\CachePurgeInterface;
use ostark\upper\models\Settings;

/**
 * Class Plugin
 *
 * @package ostark\upper
 *
 * @method models\Settings getSettings()
 */
class Plugin extends BasePlugin
{
    // Event names
    const EVENT_AFTER_SET_TAG_HEADER = 'upper_after_set_tag_header';
    const EVENT_BEFORE_PURGE = 'upper_before_purge';
    const EVENT_AFTER_PURGE = 'upper_after_purge';

    // Tag prefixes
    const TAG_PREFIX_ELEMENT = 'el';
    const TAG_PREFIX_SECTION = 'se';
    const TAG_PREFIX_STRUCTURE = 'st';

    // Mapping element properties <> tag prefixes
    const ELEMENT_PROPERTY_MAP = [
        'id'          => self::TAG_PREFIX_ELEMENT,
        'sectionId'   => self::TAG_PREFIX_SECTION,
        'structureId' => self::TAG_PREFIX_STRUCTURE
    ];

    // DB
    const CACHE_TABLE = '{{%upper_cache}}';

    // Header
    const INFO_HEADER_NAME = 'X-UPPER-CACHE';
    const TRUNCATED_HEADER_NAME = 'X-UPPER-CACHE-TRUNCATED';

    public $schemaVersion = '1.0.1';


    /**
     * Initialize Plugin
     */
    public function init()
    {
        parent::init();

        // Config pre-check
        if (!$this->getSettings()->drivers || !$this->getSettings()->driver) {
            return false;
        }

        // Register plugin components
        $this->setComponents([
            'purger'        => PurgerFactory::create($this->getSettings()->toArray()),
            'tagCollection' => TagCollection::class
        ]);

        // Attach Behaviors
        \Craft::$app->getResponse()->attachBehavior('cache-control', CacheControlBehavior::class);
        \Craft::$app->getResponse()->attachBehavior('tag-header', TagHeaderBehavior::class);

        // Register event handlers
        EventRegistrar::registerFrontendEvents();
        EventRegistrar::registerCpEvents();
        EventRegistrar::registerUpdateEvents();

        if ($this->getSettings()->useLocalTags) {
            EventRegistrar::registerFallback();
        }

        // Register Twig extension
        \Craft::$app->getView()->registerTwigExtension(new TwigExtension);
    }

    // ServiceLocators
    // =========================================================================

    /**
     * @return \ostark\upper\drivers\CachePurgeInterface
     */
    public function getPurger(): CachePurgeInterface
    {
        return $this->get('purger');
    }


    /**
     * @return \ostark\upper\TagCollection
     */
    public function getTagCollection(): TagCollection
    {
        /* @var \ostark\upper\TagCollection $collection */
        $collection = $this->get('tagCollection');
        $collection->setKeyPrefix($this->getSettings()->getKeyPrefix());

        return $collection;
    }


    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }


    /**
     * Is called after the plugin is installed.
     * Copies example config to project's config folder
     */
    protected function afterInstall()
    {
        $configSourceFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.example.php';
        $configTargetFile = \Craft::$app->getConfig()->configDir . DIRECTORY_SEPARATOR . $this->handle . '.php';

        if (!file_exists($configTargetFile)) {
            copy($configSourceFile, $configTargetFile);
        }
    }

}
