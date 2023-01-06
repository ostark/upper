<?php namespace ostark\upper\drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use ostark\upper\exceptions\KeycdnApiException;

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
    final const API_ENDPOINT = 'https://api.keycdn.com/';

    public $apiKey;

    public $zoneId;

    public $zoneUrl;


    /**
     * @return bool
     */
    public function purgeTag(string $tag)
    {
        return $this->sendRequest('purgetag', 'DELETE', [
                'tags' => [$tag]
            ]
        );
    }

    /**
     * @return bool
     */
    public function purgeUrls(array $urls)
    {
        // prefix urls
        $zoneUrls = array_map(fn($url) => $this->zoneUrl . $url, $urls);

        return $this->sendRequest('purgeurl', 'DELETE', [
                'urls' => $zoneUrls
            ]
        );
    }


    /**
     * @return bool
     */
    public function purgeAll()
    {
        return $this->sendRequest('purge', 'GET', []);
    }


    /**
     * @param string $method HTTP verb
     *
     * @return bool
     * @throws \ostark\upper\exceptions\KeycdnApiException
     */
    protected function sendRequest(string $type, $method = 'DELETE', array $params = [])
    {
        $token  = base64_encode("{$this->apiKey}:");
        $client = new Client([
            'base_uri' => self::API_ENDPOINT,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => "Basic {$token}"
            ]
        ]);

        try {

            $uri     = "zones/{$type}/{$this->zoneId}.json";
            $options = (count($params)) ? ['json' => $params] : [];
            $client->request($method, $uri, $options);

        } catch (BadResponseException $e) {

            throw KeycdnApiException::create(
                $e->getRequest(),
                $e->getResponse()
            );
        }

        return true;

    }
}
