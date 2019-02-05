<?php namespace ostark\upper;

use Craft;
use craft\base\Element;
use craft\base\Plugin as BasePlugin;
use craft\console\Application;
use craft\elements\db\ElementQuery;
use craft\events\DefineBehaviorsEvent;
use craft\helpers\Db;
use craft\records\Entry;
use craft\services\Elements;
use craft\services\Sections;
use craft\services\Structures;
use craft\utilities\ClearCaches;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use ostark\upper;
use ostark\upper\Behaviors\CacheControlBehavior;
use ostark\upper\Behaviors\TagHeaderBehavior;
use ostark\upper\Contracts\CachePurgeInterface;
use ostark\upper\Models\Settings;
use putyourlightson\elementstatusevents\ElementStatusEvents;
use yii\base\Event;
use yii\caching\CacheInterface;

/**
 * Class Plugin
 *
 * @package ostark\upper
 *
 * @method Models\Settings getSettings()
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

    public $newElementStatus = null;


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
            'purger'        => PurgerFactory::create($this->getSettings()),
            'tagCollection' => TagCollection::class
        ]);

        // Register event Contracts
        $this->registerFrontendEventHandlers();
        $this->registerUpdateHandlers();

        // Attach Behaviors
        \Craft::$app->getResponse()->attachBehavior('cache-control', CacheControlBehavior::class);
        \Craft::$app->getResponse()->attachBehavior('tag-header', TagHeaderBehavior::class);

        if (\Craft::$app instanceof Application) {
            // Register console commands
            \Craft::$app->controllerMap['upper'] = Commands::class;
            \Craft::$app->set(CacheInterface::class, \Craft::$app->getCache());
        }
    }

    // ServiceLocators
    // =========================================================================

    /**
     * @return \ostark\upper\Contracts\CachePurgeInterface
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
     * Checks whether a request is cacheable or not
     *
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
     * Frontend related Contracts
     */
    protected function registerFrontendEventHandlers()
    {
        // Frontend Events
        if ($this->isRequestCacheable()) {
            // Set current uri for fast access later
            $this->requestUri = \Craft::$app->getRequest()->getPathInfo();

            // Extract tags from Elements and store them in a TagCollection
            Event::on(ElementQuery::class, ElementQuery::EVENT_AFTER_POPULATE_ELEMENT, new upper\Contracts\CollectTags());

            // Add tags from TagCollection as a response header
            Event::on(View::class, View::EVENT_AFTER_RENDER_PAGE_TEMPLATE, new upper\Contracts\AddCacheTagResponseHeader());

            // Store url tags mapping in DB
            if ($this->getSettings()->useLocalTags) {
                Event::on(Plugin::class, Plugin::EVENT_AFTER_SET_TAG_HEADER, new upper\Contracts\StoreLocalTagMap());
            }
        }
    }


    /**
     * Detect updates
     */
    protected function registerUpdateHandlers()
    {
        if (!\Craft::$app->getRequest()->getIsSiteRequest()) {

            // Purge Handler
            $purgeOnUpdate = new upper\Contracts\PurgeOnUpdate();

            // Update Events
            Event::on(Element::class, Element::EVENT_AFTER_MOVE_IN_STRUCTURE, $purgeOnUpdate);
            Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, $purgeOnUpdate);
            Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, $purgeOnUpdate);
            Event::on(Sections::class, Sections::EVENT_AFTER_SAVE_SECTION, $purgeOnUpdate);
            Event::on(Structures::class, Structures::EVENT_AFTER_MOVE_ELEMENT, $purgeOnUpdate);
            Event::on(ElementStatusEvents::class, ElementStatusEvents::EVENT_STATUS_CHANGED, $purgeOnUpdate);

            // Register option (checkbox) in the CP
            Event::on(ClearCaches::class, ClearCaches::EVENT_REGISTER_CACHE_OPTIONS, new upper\Contracts\RegisterCacheOptions());
        }
    }

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
