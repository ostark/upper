<?php

namespace ostark\upper\drivers;

use GuzzleHttp\Exception\BadResponseException;
use ostark\upper\exceptions\AkamaiApiException;

/**
 * Class Akamai Driver
 *
 * @package ostark\upper\drivers
 *
 */
class Akamai extends AbstractPurger implements CachePurgeInterface
{
    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $clientToken;

    /**
     * @var string
     */
    public $clientSecret;

    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string
     */
    public $maxSize;

    /**
     * Purge cache by tag
     *
     * @param string $tag
     *
     * @return bool
     */
    public function purgeTag(string $tag)
    {
        if ($this->useLocalTags) {
            return $this->purgeUrlsByTag($tag);
        }

        $this->sendRequest('production', 'POST', 'tag', $tag);
        $this->sendRequest('staging', 'POST', 'tag', $tag);

        return true;
    }

    /**
     * Purge cache by urls
     *
     * @param array $urls
     *
     * @return bool
     */
    public function purgeUrls(array $urls)
    {
        foreach ($urls as $url) {
            if (!$this->sendRequest('production', 'POST', 'url', getenv('DEFAULT_SITE_URL') . $url)) {
                return false;
            }
            if (!$this->sendRequest('staging', 'POST', 'url', getenv('DEFAULT_SITE_URL') . $url)) {
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
    public function purgeAll()
    {
        // TODO: Purge all in Akamai
        return true;
        // return $this->sendRequest('POST', 'purge_all');
    }

    /**
     * Send API call
     *
     * @param string $method HTTP verb
     * @param string $type of purge
     * @param string $uri
     * @param array  $headers
     *
     * @return bool
     * @throws \ostark\upper\exceptions\AkamaiApiException
     */
    protected function sendRequest(string $environment = 'production', string $method = 'POST', string $type = "url", string $uri = "", array $headers = [])
    {
        // Akamai Open Edgegrid reads $_ENV which doesn't get populated by Craft, so filling in the blanks
        $_ENV['AKAMAI_HOST'] = getenv('AKAMAI_HOST');
        $_ENV['AKAMAI_CLIENT_TOKEN'] = getenv('AKAMAI_CLIENT_TOKEN');
        $_ENV['AKAMAI_CLIENT_SECRET'] = getenv('AKAMAI_CLIENT_SECRET');
        $_ENV['AKAMAI_ACCESS_TOKEN'] = getenv('AKAMAI_ACCESS_TOKEN');
        $_ENV['AKAMAI_MAX_SIZE'] = getenv('AKAMAI_MAX_SIZE');

        $auth = \Akamai\Open\EdgeGrid\Authentication::createFromEnv();

        $auth->setHttpMethod('POST');
        $auth->setPath('/ccu/v3/invalidate/' . $type . '/' . $environment);

        $body = json_encode(array(
            'objects' => array($uri)
        ));

        $auth->setBody($body);

        $context = array(
            'http' => array(
                'header' => array(
                    'Authorization: ' . $auth->createAuthHeader(),
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($body),
                ),
                'method' => 'POST',
                'content' => $body
            )
        );

        $context = stream_context_create($context);

        try {
            json_decode(file_get_contents('https://' . $auth->getHost() . $auth->getPath(), false, $context));
        } catch (BadResponseException $e) {
            throw AkamaiApiException::create(
                $e->getRequest(),
                $e->getResponse()
            );
        }

        return true;
    }
}
