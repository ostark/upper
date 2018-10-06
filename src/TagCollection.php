<?php namespace ostark\upper;

/**
 * Class TagCollection
 *
 * @package ostark\upper
 */
class TagCollection
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var string
     */
    protected $keyPrefix = '';


    public function add(string $tag)
    {
        $this->tags[] = $this->prepareTag($tag);
    }

    public function getAll()
    {
        return $this->tags;
    }


    /**
     * Extracts tags from Element and adds then to collection
     *
     * @param array|null $elementRawQueryResult
     */
    public function addTagsFromElement(array $elementRawQueryResult = null)
    {
        if (!is_array($elementRawQueryResult)) {
            return;
        }

        foreach (self::extractTags($elementRawQueryResult) as $tag) {
            $this->add($tag);
        }

        $this->unique();
    }

    /**
     * @param string $keyPrefix
     */
    public function setKeyPrefix($keyPrefix)
    {
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * Prepends tag with configured prefix.
     * To prevent key collision if you use the same
     * cache server for several Craft installations.
     *
     * @param string $tag
     *
     * @return string
     */
    public function prepareTag($tag)
    {
        return $this->keyPrefix . $tag;
    }


    protected function unique()
    {
        $this->tags = array_unique($this->tags);

        return $this;
    }

    /**
     * Extract tags
     *
     * @param array|null $elementRawQueryResult
     *
     * @return array
     */
    protected static function extractTags(array $elementRawQueryResult = null): array
    {
        $tags       = [];
        $properties = array_keys(Plugin::ELEMENT_PROPERTY_MAP);

        // $elementRawQueryResult is a flat assoc array that contains
        // information of an element like id, section and structure
        // We prefix the property ids with short identifiers.

        foreach ($properties as $prop) {
            if (isset($elementRawQueryResult[$prop]) && !is_null($elementRawQueryResult[$prop])) {
                $propPrefix = Plugin::ELEMENT_PROPERTY_MAP[$prop];
                $tags[]     = $propPrefix . $elementRawQueryResult[$prop];
            }
        }

        return $tags;
    }

}
