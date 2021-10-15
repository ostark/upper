<?php namespace ostark\upper;

use yii\base\Response;

class CacheResponse
{
    /**
     * @var \yii\base\Response|\ostark\upper\behaviors\CacheControlBehavior
     */
    public $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function never()
    {
        if (!$this->isWebResponse()) {
            return;
        }

        $this->response->addCacheControlDirective('private');
        $this->response->addCacheControlDirective('no-cache');
    }

    public function for(string $time)
    {
        if (!$this->isWebResponse()) {
            return;
        }

        $seconds = strtotime($time) - time();
        $this->response->setSharedMaxAge($seconds);
    }

    public function isWebResponse(): bool
    {
        return $this->response instanceof \craft\web\Response;
    }
}
