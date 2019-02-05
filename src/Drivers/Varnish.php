<?php namespace ostark\upper\Contracts;

use GuzzleHttp\Client;

/**
 * Class Varnish Driver
 *
 * @package ostark\upper\Drivers
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
     * @var array
     */
    public $headers = [];

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
                'headers'  => $this->headers + [$this->purgeHeaderName => $tag]
            ]);
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
                    'url'      => $url,
                    'headers'  => $this->headers
                ]);

            if (!$success) {
                return false;
            }
        }

        return true;
    }


    /**
     * Purge entire cache
     *
     * Requires a custom vcl config
     *
     * @see https://varnish-cache.org/docs/6.0/users-guide/purging.html#bans
     *
     * @return bool
     */
    public function purgeAll()
    {
        $options = [
            'base_uri' => $this->purgeUrl,
            'headers'  => $this->headers
        ];

        $response = (new Client($options))->request('BAN');

        return (in_array($response->getStatusCode(), [204, 200]))
            ? true
            : false;
    }


    protected function sendPurgeRequest(array $options = [])
    {
        $response = (new Client($options))->request('PURGE');

        return (in_array($response->getStatusCode(), [204, 200]))
            ? true
            : false;
    }
}
