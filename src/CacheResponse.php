<?php declare(strict_types=1);

namespace ostark\upper;


use yii\base\Response;
use ostark\upper\behaviors\CacheControlBehavior;

class CacheResponse
{
    /**
     * @var Response|CacheControlBehavior
     */
    public Response|CacheControlBehavior $response;

    public function __construct(Response|CacheControlBehavior $response)
    {
        $this->response = $response;
    }

    public function never(): void
    {
        if (!$this->isWebResponse()) {
            return;
        }

        $this->response->addCacheControlDirective('private');
        $this->response->addCacheControlDirective('no-cache');
    }

    public function for(string $time): void
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
