<?php declare(strict_types=1);

namespace ostark\upper\models;

use Craft;
use craft\base\Model;
use yii\helpers\Inflector;

/**
 * Upper Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Oliver Stark
 * @package   Upper
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public string $driver;


    /**
     * Some field model attribute
     *
     * @var array
     */
    public array $drivers = [];

    /**
     * Some field model attribute
     *
     * @var int
     */
    public int|null $defaultMaxAge = null;

    /**
     * @var bool
     */
    public bool $useLocalTags = true;

    /**
     * Key prefix
     *
     * @var string
     */
    public string $keyPrefix = '';

    /**
     * Max kilobytes of the X-Cachetag header
     *
     * @var int
     */
    public int|null $maxBytesForCacheTagHeader = null;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['driver', 'drivers','keyPrefix'], 'required'],
        ];
    }

    /**
     * @return string
     */
    public function getTagHeaderName(): string
    {
        return $this->drivers[$this->driver]['tagHeaderName'];
    }

    /**
     * @return string
     */
    public function getHeaderTagDelimiter(): string
    {
        return $this->drivers[$this->driver]['tagHeaderDelimiter'] ?? ' ';
    }

    /**
     * Get key prefix.
     * To prevent key collision if you use the same
     * cache server for several Craft installations.
     *
     * @return string
     */
    public function getKeyPrefix(): string
    {
        if (!$this->keyPrefix) {
            return '';
        }

        $clean = Inflector::slug($this->keyPrefix);
        return substr($clean, 0, 8);
    }

    /**
     * @return array
     */
    public function getNoCacheElements(): array
    {
        return ['craft\elements\User', 'craft\elements\MatrixBlock', 'verbb\supertable\elements\SuperTableBlockElement'];
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function isCachableElement(string $class): bool
    {
        return in_array($class, $this->getNoCacheElements()) ? false : true;
    }

}
