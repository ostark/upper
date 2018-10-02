<?php namespace ostark\upper;

use craft\base\Element;
use craft\base\Plugin as BasePlugin;
use craft\elements\db\ElementQuery;
use craft\services\Elements;
use craft\services\Sections;
use craft\services\Structures;
use craft\utilities\ClearCaches;
use craft\web\View;
use ostark\upper;
use ostark\upper\behaviors\CacheControlBehavior;
use ostark\upper\behaviors\TagHeaderBehavior;
use ostark\upper\drivers\CachePurgeInterface;
use ostark\upper\models\Settings;
use yii\base\Event;

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

    public $schemaVersion = '1.0.1';

    public $requestUri;


    /**
     * Initialize Plugin
     *
     * @throws \yii\base\InvalidConfigException
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

        // Register all handlers
        $this->registerEventHandlers();

        // Attach Behaviors
        \Craft::$app->getResponse()->attachBehavior('cache-control', CacheControlBehavior::class);
        \Craft::$app->getResponse()->attachBehavior('tag-header', TagHeaderBehavior::class);

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
     *
     */
    protected function registerEventHandlers()
    {
        // Frontend events
        if ($this->isRequestCacheable()) {

            // Set current uri for fast access later
            $this->requestUri = \Craft::$app->getRequest()->getPathInfo();

            // Extract tags form Elements and store them in a TagCollection
            Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT, new upper\handler\CollectTags());

            // Add tags from TagCollection as a response header
            Event::on(View::class, View::EVENT_AFTER_RENDER_PAGE_TEMPLATE, new upper\handler\CacheTagResponse());

            // Store url tags mapping in DB
            if ($this->getSettings()->useLocalTags) {
                Event::on(Plugin::class, Plugin::EVENT_AFTER_SET_TAG_HEADER, new upper\handler\LocalTagMapping());
            }
        }

        // CP requests
        if (\Craft::$app->getRequest()->getIsCpRequest()) {

            // Handler object (with __invoke() method)
            $updateHandler = new upper\handler\Update();

            // Update events
            Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, $updateHandler);
            Event::on(Elements::class, Element::EVENT_AFTER_MOVE_IN_STRUCTURE, $updateHandler);
            Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, $updateHandler);
            Event::on(Elements::class, Structures::EVENT_AFTER_MOVE_ELEMENT, $updateHandler);
            Event::on(Elements::class, Sections::EVENT_AFTER_SAVE_SECTION, $updateHandler);

            // Register option (checkbox) in the CP
            Event::on(ClearCaches::class, ClearCaches::EVENT_REGISTER_CACHE_OPTIONS, new upper\handler\RegisterCacheOptions());

        }
    }

    /**
     * @return bool
     */
    protected function isRequestCacheable()
    {
        // No need to continue when in cli mode
        if (\Craft::$app instanceof \craft\console\Application) {
            return false;
        }

        // HTTP request object
        $request = \Craft::$app->getRequest();

        // Don't cache CP, LivePreview, Non-GET requests
        if ($request->getIsCpRequest() ||
            $request->getIsLivePreview() ||
            !$request->getIsGet()
        ) {
            return false;
        }

        return true;

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
