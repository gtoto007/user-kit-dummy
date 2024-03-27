<?php

namespace Toto\UserKit\Exceptions;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpResponseException extends Exception
{
    public function __construct(private RequestInterface $request, private ResponseInterface $response, $code = 0, Exception $previous = null)
    {
        $message = "Invalid response for request to {$request->getUri()}: received status code {$response->getStatusCode()}";
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
}