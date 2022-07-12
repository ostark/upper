<?php namespace ostark\Upper\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class KeycdnApiException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("KeyCDN API error: $message", $code, $previous);
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
            return new static("No response error, uri: '$uri'");
        }

        // Extract error message from body
        $status = $response->getStatusCode();
        $json   = json_decode($response->getBody());
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new static("KeyCDN API error ($status) on: '$uri'", $status);
        }

        // Error message
        if (isset($json->status, $json->description) && $json->status === 'error') {
            return new static($json->description . ", uri: '$uri'", $status);
        }

        // Unknown
        return new static("Unknown error, uri: '$uri'");

    }
}

