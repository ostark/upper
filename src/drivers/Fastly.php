<?php namespace ostark\upper\drivers;

use GuzzleHttp\Client;

/**
 * Class Keycdn Driver
 *
 * POST https://api.fastly.com/service/{serviceId}/purge HTTP/1.1
 * Surrogate-Key: key_1 key_2 key_3
 * Fastly-Key: {$apiToken}
 * Accept: application/json
 *
 * POST https://api.fastly.com/service/{serviceId}/purge_all HTTP/1.1
 * Fastly-Key: {$apiToken}
 * Accept: application/json
 *
 * PURGE https://www.example.com/example/uri HTTP/1.1
 * Fastly-Key:{$apiToken}
 *
 * @package ostark\upper\drivers
 *
 */
class Fastly extends AbstractPurger implements CachePurgeInterface
{
    /**
     * Fastly API endpoint
     */
    const API_ENDPOINT = 'https://api.fastly.com';

    /**
     * @var string
     */
    public $apiToken;

    /**
     * @var string
     */
    public $serviceId;

    /**
     * @var string
     */
    public $domain;


    /**
     * Purge cache by keys
     *
     * @param array $keys
     *
     * @return bool
     */
    public function purgeByKeys(array $keys)
    {
        return $this->sendRequest('POST', 'purge', [
                'Surrogate-Key' => implode(' ', $keys)
            ]
        );
    }

    /**
     * Purge cache by url
     *
     * @param string $url
     *
     * @return bool
     */
    public function purgeByUrl(string $url)
    {
        if (strpos($this->domain, 'http') === false) {
            throw new \InvalidArgumentException("'domain' is not configured for fastly driver");
        }

        if (strpos($this->domain, 'http') !== 0) {
            throw new \InvalidArgumentException("'domain' must include the protocol, e.g. http://www.foo.com");
        }

        return $this->sendRequest('PURGE', $this->domain . $url);
    }


    /**
     * Purge entire cache
     *
     * @return bool
     */
    public function purgeAll()
    {
        return $this->sendRequest('POST', 'purge_all');
    }

    /**
     * Send API call
     *
     * @param string $method HTTP verb
     * @param string $uri
     * @param array  $headers
     *
     * @return bool
     */
    protected function sendRequest(string $method = 'PURGE', string $uri, array $headers = [])
    {
        $client = new Client([
            'base_uri' => self::API_ENDPOINT,
            'headers'  => array_merge($headers, [
                'Content-Type' => 'application/json',
                'Fastly-Key'   => $this->apiToken
            ])
        ]);

        // PURGE requests don't use the API_ENDPOINT
        if ($method !== 'PURGE') {
            $uri = "service/{$this->serviceId}/{$uri}";
        }

        $response = $client->request($method, $uri);

        return (in_array($response->getStatusCode(), [204, 200]))
            ? true
            : false;

    }
}





