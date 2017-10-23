<?php namespace ostark\upper\drivers;

use GuzzleHttp\Client;

/**
 * Class Keycdn Driver
 *
 * @package ostark\upper\drivers
 */
class Keycdn extends AbstractPurger implements CachePurgeInterface
{
    /**
     * KeyCDN API endpoint
     */
    const API_ENDPOINT = 'https://api.keycdn.com/';

    public $apiKey;

    public $zoneId;

    public $zoneUrl;


    /**
     * @param array $keys
     *
     * @return bool
     */
    public function purgeByKeys(array $keys)
    {
        return $this->sendRequest('DELETE', 'purgetag', [
                'tags' => $keys
            ]
        );
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function purgeByUrl(string $url)
    {
        return $this->sendRequest('DELETE', 'purgeurl', [
                'urls' => [$this->zoneUrl . $url]
            ]
        );
    }


    /**
     * @return bool
     */
    public function purgeAll()
    {
        return $this->sendRequest('GET', 'purge', []);
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
        $token  = base64_encode("{$this->apiKey}:");
        $client = new Client([
            'base_uri' => self::API_ENDPOINT,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => "Basic {$token}"
            ]
        ]);

        $uri     = "zones/{$type}/{$this->zoneId}.json";
        $options = (count($params)) ? ['json' => $params] : [];

        $response = $client->request($method, $uri, $options);

        return (in_array($response->getStatusCode(), [204, 200]))
            ? true
            : false;

    }
}
