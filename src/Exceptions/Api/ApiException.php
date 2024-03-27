<?php

namespace Toto\UserKit\Exceptions\Api;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiException extends Exception
{

    public function __construct(private RequestInterface $request, private ?ResponseInterface $response = null, string $message = null, Exception $previous = null)
    {
        $code = $this->hasResponse() ? $this->response->getStatusCode() : ($previous ? $previous->getCode() : 0);
        if (empty($message)) {
            $message = "Invalid response for request to {$request->getUri()}";
            if ($code != 0) {
                $message .= " with error code $code";
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function hasResponse()
    {
        return $this->response != null;
    }
}