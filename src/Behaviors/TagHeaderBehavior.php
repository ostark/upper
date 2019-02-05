<?php namespace ostark\upper\Behaviors;

use yii\base\Behavior;

/**
 * Class TagHeaderBehavior
 *
 * @package ostark\upper\Behaviors
 * @property \yii\web\Response $owner
 */
class TagHeaderBehavior extends Behavior
{

    /**
     * Simply tag
     *
     * @param string      $name
     * @param array       $tags
     * @param string|null $delimiter
     *
     * @return bool
     */
    public function setTagHeader(string $name, array $tags, string $delimiter = null)
    {
        $headers = $this->owner->getHeaders();

        // no tags
        if (count($tags) === 0) {
            return false;
        }

        if (is_string($delimiter)) {
            // concatenate with $delimiter
            $headers->add($name, implode($delimiter, $tags));

            return true;
        }

        foreach ($tags as $tag) {
            // add multiple
            $headers->add($name, $tag);
        }

        return true;
    }
}
