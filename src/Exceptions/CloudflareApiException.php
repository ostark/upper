<?php namespace ostark\Upper\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CloudflareApiException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Cloudflare API error: $message", $code, $previous);
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
            return new static("Cloudflare API error ($status) on: '$uri'", $status);
        }


        // Error message
        if (isset($json->errors) && count($json->errors) >= 1) {
            return new static($json->errors[0]->message . ", uri: '$uri'", $json->errors[0]->code);
        }

        // No success, but no error
        if (isset($json->success) && !$json->success) {
            return new static("Request was unsuccessful, uri: '$uri'");
        }

        // Unknown
        return new static("Unknown error, uri: '$uri'");

    }
}
