<?php namespace ostark\upper;

use craft\web\Response;

class CacheResponse
{
    /**
     * @var \craft\web\Response|\ostark\upper\behaviors\CacheControlBehavior
     */
    public $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function never()
    {
        $this->response->addCacheControlDirective('private');
        $this->response->addCacheControlDirective('no-cache');
    }

    public function for(string $time)
    {
        $seconds = strtotime($time) - time();
        $this->response->setSharedMaxAge($seconds);
    }

}
