<?php namespace ostark\upper\drivers;

use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use ostark\upper\exceptions\CloudflareApiException;
use ostark\upper\Plugin;

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

    const MAX_URLS_PER_PURGE = 30;

    public $apiKey;

    public $apiEmail;

    public $apiToken;

    public $zoneId;

    public $domain;

    public $sites;

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
        if ($this->sites) {
            foreach ($urls as $uid => $url) {
                $sql ="SELECT siteId FROM %s WHERE uid = '%s'";
                $sql = sprintf(
                    $sql,
                    \Craft::$app->getDb()->quoteTableName(Plugin::CACHE_TABLE),
                    $uid
                );
                $result = \Craft::$app->getDb()
                    ->createCommand($sql)
                    ->queryOne();
                $siteId = $result['siteId'];
                if (strpos($this->sites[$siteId]['domain'], 'http') !== 0) {
                    throw new \InvalidArgumentException("'domain' must include the protocol, e.g. https://www.foo.com");
                }
                $files[$siteId][] = rtrim($this->sites[$siteId]['domain'], '/') . $url;
            }

            // Chunk larger collections to meet the API constraints
            foreach ($files as $siteId => $siteFiles) {
                foreach (array_chunk($siteFiles, self::MAX_URLS_PER_PURGE) as $fileGroup) {
                    $this->sendRequest('DELETE', 'purge_cache', [
                        'files' => $fileGroup
                    ], [$this->sites[$siteId]['zoneId']]);
                }    
            }
        } else {
            if (strpos($this->domain, 'http') !== 0) {
                throw new \InvalidArgumentException("'domain' must include the protocol, e.g. https://www.foo.com");
            }
    
            // prefix urls with domain
            $files = array_map(function($url) {
                return rtrim($this->domain, '/') . $url;
            }, $urls);    

            // Chunk larger collections to meet the API constraints
            foreach (array_chunk($files, self::MAX_URLS_PER_PURGE) as $fileGroup) {
                $this->sendRequest('DELETE', 'purge_cache', [
                    'files' => $fileGroup
                ]);
            }
        }

        return true;
    }


    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function purgeAll()
    {
        $zoneIds = [];
        if ($this->sites) {
            foreach ($this->sites as $site) {
                $zoneIds[] = $site['zoneId'];
            }    
        }

        $success = $this->sendRequest('DELETE', 'purge_cache', [
            'purge_everything' => true,
        ], $zoneIds);

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
    protected function sendRequest($method = 'DELETE', string $type, array $params = [], $zoneIds = [])
    {
        $client = $this->getClient();

        if (empty($zoneIds)) {
            $zoneIds[] = $this->zoneId;
        }

        foreach ($zoneIds as $zoneId) {
            try {
                $uri = "zones/$zoneId/$type";
                $options = (count($params)) ? ['json' => $params] : [];
                $client->request($method, $uri, $options);
            } catch (BadResponseException $e) {

                throw CloudflareApiException::create(
                    $e->getRequest(),
                    $e->getResponse()
                );
            }
        }

        return true;
    }

    private function getClient()
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->usesLegacyApiKey()) {
            Craft::$app->getDeprecator()->log('Upper Config: Cloudflare $apiKey', 'Globally scoped Cloudflare API keys are deprecated for security. Create a scoped token instead and use via the `apiToken` key in the driver config.');

            $headers['X-Auth-Key'] = $this->apiKey;
            $headers['X-Auth-Email'] = $this->apiEmail;
        } else {
            $headers['Authorization'] = 'Bearer ' . $this->apiToken;
        }

        return new Client([
            'base_uri' => self::API_ENDPOINT,
            'headers' => $headers,
        ]);
    }

    private function usesLegacyApiKey()
    {
        return !isset($this->apiToken) && isset($this->apiKey);
    }
}
