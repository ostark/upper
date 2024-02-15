<?php namespace ostark\upper\drivers;

use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use prediger\upper\drivers\AbstractPurger;
use prediger\upper\drivers\CachePurgeInterface;


/**
 * Class Varnish Driver
 *
 * @package prediger\upper\drivers
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
     * @var string[]
     */
    private iterable $tags_done = [];

    /**
     * @param string $tag
     */
    public function purgeTag(string $tag)
    {
        if (\in_array($tag, $this->tags_done)) {
            Craft::info('Upper Varnish Purge Tag: ' . $tag . ' already done');
            return true;
        }
        if ($this->useLocalTags) {
            $result = $this->purgeUrlsByTag($tag);
            if ($result) {
                $this->tags_done[] = $tag;
            }
            return $result;
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
    public function purgeUrls(array $urls)
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
    public function purgeAll()
    {
        return $this->sendPurgeRequest([
            'headers'  => $this->headers
        ], 'BAN');
    }


    protected function sendPurgeRequest(array $options = [], $method = 'PURGE')
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
