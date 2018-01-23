<?php namespace ostark\upper\drivers;

use GuzzleHttp\Client;

/**
 * Class Cloudflare Driver
 *
 * @package ostark\upper\drivers
 */
class Cloudflare extends AbstractPurger implements CachePurgeInterface
{
    /**
     * Cloudflare API endpoint
     */
    const API_ENDPOINT = 'https://api.cloudflare.com/client/v4/';

    public $apiKey;

    public $apiEmail;

    public $zoneId;

    public $domain;


    /**
     * @param string $tag
     *
     * @return bool
     */
    public function purgeTag(string $tag)
    {
        if ($this->useLocalTags) {
            $this->purgeUrlsByTag($tag);
        }

        return $this->sendRequest('DELETE', 'purge_cache', [
                'tags' => [$tag]
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
        if (strpos($this->domain, 'http') !== 0) {
            throw new \InvalidArgumentException("'domain' must include the protocol, e.g. https://www.foo.com");
        }

        // prefix urls with domain
        $files = array_map(function ($url) {
            return rtrim($this->domain, '/') . $url;
        }, $urls);

        return $this->sendRequest('DELETE', 'purge_cache', [
                'files' => $files
            ]
        );
    }


    /**
     * @return bool
     */
    public function purgeAll()
    {
        if ($this->useLocalTags) {
            $this->clearLocalCache();
        }

        return $this->sendRequest('DELETE', 'purge_cache', [
            'purge_everything' => true
        ]);
    }


    /**
     * @param string $method HTTP verb
     * @param string $type
     * @param array  $params
     *
     * @return bool
     */
    protected function sendRequest($method = 'DELETE', string $type, array $params = [])
    {
        $client = new Client([
            'base_uri' => self::API_ENDPOINT,
            'headers'  => [
                'Content-Type' => 'application/json',
                'X-Auth-Key'   => $this->apiKey,
                'X-Auth-Email' => $this->apiEmail,
            ]
        ]);

        $uri     = "zones/{$this->zoneId}/$type";
        $options = (count($params)) ? ['json' => $params] : [];

        $response = $client->request($method, $uri, $options);

        return (in_array($response->getStatusCode(), [200]))
            ? true
            : false;

    }
}
