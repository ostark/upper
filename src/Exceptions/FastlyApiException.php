<?php namespace ostark\Upper\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FastlyApiException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Fastly API error: $message", $code, $previous);
    }

    /**
     * @param \Psr\Http\Message\RequestInterface       $request
     * @param \Psr\Http\Message\ResponseInterface|null $response
     *
     * @return static
     */
    public static function create(RequestInterface $request, ResponseInterface $response = null)
    {
        $uri = $request->getUri();

        if (is_null($response)) {
            return new static("Cloudflare no response error, uri: '$uri'");
        }

        // Extract error message from body
        $status = $response->getStatusCode();
        $json   = json_decode($response->getBody());
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new static("Cloudflare API error ($status) on: '$uri'", $status);
        }

        // Error message
        if (isset($json->msg)) {
            return new static($json->msg . ", uri: '$uri'", $response->getStatusCode());
        }

        // Unknown
        return new static("Unknown error, uri: '$uri'");

    }
}

