<?php namespace ostark\upper\drivers;

use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
    public string $purgeHeaderName;

    /**
     * @var string
     */
    public string $purgeUrl;

    /**
     * @var array
     */
    public $headers = [];

    /**
     * @param string $tag
     */
    public function purgeTag(string $tag): int
    {
        if ($this->useLocalTags) {
            return $this->purgeUrlsByTag($tag);
        }

        return $this->sendPurgeRequest([
                'headers'  => $this->headers + [$this->purgeHeaderName => $tag]
            ]
        );
    }

    /**
     * @param array $urls
     *
     * @return bool
     */
    public function purgeUrls(array $urls): int
    {
        foreach ($urls as $url) {
            $success = $this->sendPurgeRequest([
                    'url'      => $url,
                    'headers'  => $this->headers
                ]
            );

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
    public function purgeAll(): int
    {
        return $this->sendPurgeRequest([
            'headers'  => $this->headers
        ], 'BAN');
    }


    protected function sendPurgeRequest(array $options = [], $method = 'PURGE'): int
    {
        $success = true;
        $purgeUrls = explode(',', $this->purgeUrl);
        foreach ($purgeUrls as $purgeUrl) {
            Craft::info($method . ' Varnish cache ' . $purgeUrl);

            $options['base_uri'] = $purgeUrl;
            if (isset($options['url'])) {
                $options['base_uri'] .= $options['url'];
            }

            try {
                $response = (new Client($options))->request($method);

                if ($success) {
                    $success = in_array($response->getStatusCode(), [204, 200]);
                }
            } catch (GuzzleException $guzzleException) {
                $success = false;
                Craft::warning($guzzleException->getMessage());
            }
        }

        return $success;
    }
}
