<?php namespace ostark\Upper;


use Craft;
use craft\base\Element;
use craft\base\Plugin as BasePlugin;
use craft\elements\db\ElementQuery;
use craft\services\Elements;
use craft\services\Sections;
use craft\services\Structures;
use craft\utilities\ClearCaches;
use craft\web\View;
use ostark\Upper\behaviors\CacheControlBehavior;
use ostark\Upper\behaviors\TagHeaderBehavior;
use ostark\Upper\Drivers\CachePurgeInterface;
use ostark\Upper\Handlers\AddCacheResponse;
use ostark\Upper\Handlers\CollectTagsFromElementQuery;
use ostark\Upper\Handlers\CollectTagsFromTemplateCache;
use ostark\Upper\Handlers\InvalidateCache;
use ostark\Upper\Handlers\RegisterCacheCheckbox;
use ostark\Upper\Handlers\StoreTagUrlRelation;
use ostark\Upper\Models\PluginSettings;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @package ostark\Upper
 *
 * @method Models\PluginSettings getSettings()
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

    public string $schemaVersion = '1.0.1';


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

        // Register TagCollection in container
        Craft::$container->setSingleton(TagCollection::class, function () {
            $collection = new TagCollection();
            $collection->setKeyPrefix($this->getSettings()->getKeyPrefix());
            return $collection;
        });

        // Register Purger in container
        Craft::$container->set(CachePurgeInterface::class , function () {
            return PurgerFactory::create($this->getSettings()->toArray());
        });

        // Attach Behaviors
        // TODO -> different implementation
        // \Craft::$app->getResponse()->attachBehavior('cache-control', CacheControlBehavior::class);
        // \Craft::$app->getResponse()->attachBehavior('tag-header', TagHeaderBehavior::class);

        // Register event handlers
        $this->registerFrontendEventHandlers();
        $this->registerCpEventHandlers();
        $this->registerUpdateEventHandlers();

        // Register Twig extension
        \Craft::$app->getView()->registerTwigExtension(new TwigExtension);
    }


    private function registerFrontendEventHandlers(): void
    {
        if ($this->isNotCacheable()) {
            // TODO
            // $response = \Craft::$app->getResponse();
            // $response->addCacheControlDirective('private');
            //$response->addCacheControlDirective('no-cache');
            return;
        }

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_AFTER_POPULATE_ELEMENT,
            new CollectTagsFromElementQuery($this->getSettings(), tags())
        );

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_DEFINE_CACHE_TAGS,
            new CollectTagsFromTemplateCache($this->getSettings())
        );

        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_PAGE_TEMPLATE,
            new AddCacheResponse($this->getSettings(), tags())
        );

        Event::on(
            Plugin::class,
            Plugin::EVENT_AFTER_SET_TAG_HEADER,
            new StoreTagUrlRelation()
        );

    }

    private function registerCpEventHandlers(): void
    {
        Event::on(
            ClearCaches::class,
            ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            new RegisterCacheCheckbox($this->getSettings(), purger())
        );
    }

    private function registerUpdateEventHandlers(): void
    {
        $handler = new InvalidateCache($this->getSettings());

        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, $handler);
        Event::on(Element::class, Element::EVENT_AFTER_MOVE_IN_STRUCTURE, $handler);
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, $handler);
        Event::on(Structures::class, Structures::EVENT_AFTER_MOVE_ELEMENT, $handler);
        Event::on(Sections::class, Sections::EVENT_AFTER_SAVE_SECTION, $handler);
    }


    private function isNotCacheable(): bool
    {
        if (\Craft::$app instanceof \craft\console\Application) {
            return true;
        }

        $request = \Craft::$app->getRequest();

        if ($request->getIsCpRequest() ||
            $request->getIsLivePreview() ||
            $request->getIsActionRequest() ||
            !$request->getIsGet()
        ) {
            return true;
        }
        return false;
    }


    /**
     * Creates and returns the model used to store the plugin’s settings.
     */
    protected function createSettingsModel(): PluginSettings
    {
        return new PluginSettings();
    }


    /**
     * Is called after the plugin is installed.
     * Copies example config to project's config folder
     */
    protected function afterInstall():void
    {
        $configSourceFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.example.php';
        $configTargetFile = \Craft::$app->getConfig()->configDir . DIRECTORY_SEPARATOR . $this->handle . '.php';

        if (!file_exists($configTargetFile)) {
            copy($configSourceFile, $configTargetFile);
        }
    }
}
