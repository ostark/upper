<?php namespace ostark\upper\drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use ostark\upper\exceptions\CloudflareApiException;

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
             return $this->purgeUrlsByTag($tag);
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
     * @throws \ostark\upper\exceptions\CloudflareApiException
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
     * @throws \yii\db\Exception
     */
    public function purgeAll()
    {
        $success = $this->sendRequest('DELETE', 'purge_cache', [
            'purge_everything' => true
        ]);

        if ($this->useLocalTags && $success === true) {
            $this->clearLocalCache();
        }

        return $success;
    }


    /**
     * @param string $method HTTP verb
     * @param string $type
     * @param array  $params
     *
     * @return bool
     * @throws \ostark\upper\exceptions\CloudflareApiException
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

        try {

            $uri     = "zones/{$this->zoneId}/$type";
            $options = (count($params)) ? ['json' => $params] : [];
            $client->request($method, $uri, $options);

        } catch (BadResponseException $e) {

            throw CloudflareApiException::create(
                $e->getRequest(),
                $e->getResponse()
            );
        }

        return true;
    }



}
