<?php namespace ostark\Upper\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use ostark\Upper\Exceptions\KeycdnApiException;

/**
 * Class Keycdn Driver
 *
 * @package ostark\Upper\Drivers
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
     * @param string $tag
     *
     * @return bool
     */
    public function purgeTag(string $tag)
    {
        return $this->sendRequest('DELETE', 'purgetag', [
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
        // prefix urls
        $zoneUrls = array_map(function ($url) {
            return $this->zoneUrl . $url;
        }, $urls);

        return $this->sendRequest('DELETE', 'purgeurl', [
                'urls' => $zoneUrls
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
     * @throws \ostark\Upper\exceptions\KeycdnApiException
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
