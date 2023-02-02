<?php namespace ostark\upper\drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use ostark\upper\exceptions\FastlyApiException;

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
    public string $apiToken;

    /**
     * @var string
     */
    public string $serviceId;

    /**
     * @var string
     */
    public string $domain;

    /**
     * @var bool
     */
    public bool $softPurge = false;

    /**
     * Purge cache by tag
     *
     * @param string $tag
     *
     * @return bool
     */
    public function purgeTag(string $tag): bool
    {
        return $this->sendRequest('POST', 'purge', [
                'Surrogate-Key' => $tag
            ]
        );
    }

    /**
     * Purge cache by urls
     *
     * @param array $urls
     *
     * @return bool
     */
    public function purgeUrls(array $urls): bool
    {
        if (!str_contains($this->domain, 'http')) {
            throw new \InvalidArgumentException("'domain' is not configured for fastly driver");
        }

        if (!str_starts_with($this->domain, 'http')) {
            throw new \InvalidArgumentException("'domain' must include the protocol, e.g. http://www.foo.com");
        }

        foreach ($urls as $url) {
            if (!$this->sendRequest('PURGE', $this->domain . $url)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Purge entire cache
     *
     * @return bool
     */
    public function purgeAll(): bool
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
     * @throws \ostark\upper\exceptions\FastlyApiException
     */
    protected function sendRequest(string $method = 'PURGE', string $uri, array $headers = []): bool
    {
        $client = new Client([
            'base_uri' => self::API_ENDPOINT,
            'headers'  => array_merge($headers, [
                'Content-Type' => 'application/json',
                'Fastly-Key'   => $this->apiToken,
            ], $this->softPurge ? [
                'Fastly-Soft-Purge' => 1,
            ] : [])
        ]);

        // Prepend the service endpoint
        if (in_array($method, ['POST','GET'])) {
            $uri = "service/{$this->serviceId}/{$uri}";
        }

        try {

            $response = $client->request($method, $uri);
            $body = $response->getBody();
            $contents = $body->getContents();

        } catch (BadResponseException $e) {

            throw FastlyApiException::create(
                $e->getRequest(),
                $e->getResponse()
            );
        }

        return true;
    }
}





