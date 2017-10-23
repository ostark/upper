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
     * @param array $keys
     */
    public function purgeByKeys(array $keys)
    {
        $this->sendPurgeRequest([
                'base_uri' => $this->purgeUrl,
                'headers'  => [$this->purgeHeaderName => implode(" ", $keys)]
            ]
        );
    }

    /**
     * @param string $url
     */
    public function purgeByUrl(string $url)
    {
        $this->sendPurgeRequest([
                'base_uri' => $this->purgeUrl . $url,
                'url'      => $url
            ]
        );
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
