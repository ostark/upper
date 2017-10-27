<?php namespace ostark\upper\drivers;

use GuzzleHttp\Client;

/**
 * Class Varnish Driver
 *
 * @package ostark\upper\drivers
 */
class Varnish extends AbstractPurger implements CachePurgeInterface
{
    /**
     * @var string
     */
    public $purgeHeaderName;

    /**
     * @var string
     */
    public $purgeUrl;


    /**
     * @param string $tag
     */
    public function purgeTag(string $tag)
    {
        if ($this->useLocalTags) {
            return $this->purgeUrlsByTag($tag);
        }

        return $this->sendPurgeRequest([
                'base_uri' => $this->purgeUrl,
                'headers'  => [$this->purgeHeaderName => $tag]
            ]
        );
    }

    /**
     * @param array $urls
     *
     * @return bool
     */
    public function purgeUrls(array $urls)
    {
        foreach ($urls as $url) {

            $success = $this->sendPurgeRequest([
                    'base_uri' => $this->purgeUrl . $url,
                    'url'      => $url
                ]
            );

            if (!$success) {
                return false;
            }
        }

        return true;

    }

    public function purgeAll()
    {
        // TODO: Implement purgeAll() method.
    }

    protected function sendPurgeRequest(array $options = [])
    {
        $response = (new Client($options))->request('PURGE');

        return (in_array($response->getStatusCode(), [204, 200]))
            ? true
            : false;
    }
}
