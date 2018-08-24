<?php namespace ostark\upper;

/**
 * Class TagCollection
 *
 * @package ostark\upper
 */
class TagCollection
{
    protected $tags = [];

    public function add(string $tag)
    {
        $this->tags[] = Plugin::getInstance()->prepareTag($tag);
    }

    public function getAll()
    {
        return $this->tags;
    }

    public function addTagsFromElement(array $elementRawQueryResult = null)
    {
        if (!is_array($elementRawQueryResult)) {
            return;
        }

        foreach ($this->extractTags($elementRawQueryResult) as $tag) {
            $this->add($tag);
        }

        $this->unique();
    }

    protected function extractTags(array $elementRawQueryResult = null): array
    {
        $tags       = [];
        $properties = array_keys(Plugin::ELEMENT_PROPERTY_MAP);

        foreach ($properties as $prop) {
            if (isset($elementRawQueryResult[$prop]) && !is_null($elementRawQueryResult[$prop])) {
                $tags[] = Plugin::ELEMENT_PROPERTY_MAP[$prop] . $elementRawQueryResult[$prop];
            }
        }

        return $tags;
    }

    protected function unique()
    {
        $this->tags = array_unique($this->tags);

        return $this;
    }
}
