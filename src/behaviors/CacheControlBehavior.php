<?php namespace ostark\Upper\behaviors;

use yii\base\Behavior;
use yii\web\Response;

/**
 * Class CacheControlBehavior
 *
 * @package ostark\Upper\behaviors
 * @property \yii\web\Response $owner
 */
class CacheControlBehavior extends Behavior
{
    /**
     * @var array
     */
    protected $cacheControl = [];


    /**
     * Adds a custom Cache-Control directive.
     *
     * @param string $key   The Cache-Control directive name
     * @param mixed  $value The Cache-Control directive value
     */
    public function addCacheControlDirective(string $key, $value = true)
    {
        $this->cacheControl[$key] = $value;
        $this->owner->getHeaders()->set('Cache-Control', $this->getCacheControlHeader());
    }

    /**
     * Removes a Cache-Control directive.
     *
     * @param string $key The Cache-Control directive
     */
    public function removeCacheControlDirective(string $key)
    {
        unset($this->cacheControl[$key]);
        $this->owner->getHeaders()->set('Cache-Control', $this->getCacheControlHeader());
    }


    /**
     * Returns true if the Cache-Control directive is defined.
     *
     * @param string $key The Cache-Control directive
     *
     * @return bool true if the directive exists, false otherwise
     */
    public function hasCacheControlDirective(string $key)
    {
        return array_key_exists($key, $this->cacheControl);
    }

    /**
     * Returns a Cache-Control directive value by name.
     *
     * @param string $key The directive name
     *
     * @return mixed|null The directive value if defined, null otherwise
     */
    public function getCacheControlDirective($key)
    {
        return array_key_exists($key, $this->cacheControl) ? $this->cacheControl[$key] : null;
    }


    /**
     * Returns the number of seconds after the time specified in the response's Date
     * header when the response should no longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive, and then it falls
     * back on an expires header. It returns null when no maximum age can be established.
     *
     * @return int|null Number of seconds
     *
     */
    public function getMaxAge()
    {
        if ($this->hasCacheControlDirective('s-maxage')) {
            return (int)$this->getCacheControlDirective('s-maxage');
        }
        if ($this->hasCacheControlDirective('max-age')) {
            return (int)$this->getCacheControlDirective('max-age');
        }

        return null;

    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * This methods sets the Cache-Control max-age directive.
     *
     * @param int $value Number of seconds
     *
     * @return $this
     *
     * @final since version 3.2
     */
    public function setMaxAge($value)
    {
        $this->addCacheControlDirective('max-age', $value);

        return $this;
    }


    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * This methods sets the Cache-Control s-maxage directive.
     *
     * @param int $value Number of seconds
     *
     * @return $this
     *
     * @final since version 3.2
     */
    public function setSharedMaxAge($value)
    {
        $this->addCacheControlDirective('public');
        $this->removeCacheControlDirective('private');
        $this->removeCacheControlDirective('no-cache');
        $this->addCacheControlDirective('s-maxage', $value);

        return $this;
    }

    public function getCacheControl()
    {
        return $this->cacheControl;
    }

    public function setCacheControlDirectiveFromString(string $value = null)
    {
        if (is_null($value) || strlen($value) === 0) {
            return false;
        }
        foreach (explode(', ', $value) as $directive) {
            $parts = explode('=', $directive);
            $this->addCacheControlDirective($parts[0], $parts[1] ?? true);
        }
    }

    protected function getCacheControlHeader()
    {
        $parts = array();
        ksort($this->cacheControl);
        foreach ($this->cacheControl as $key => $value) {
            if (true === $value) {
                $parts[] = $key;
            } else {
                if (preg_match('#[^a-zA-Z0-9._-]#', $value)) {
                    $value = '"' . $value . '"';
                }
                $parts[] = "$key=$value";
            }
        }

        return implode(', ', $parts);
    }
}
