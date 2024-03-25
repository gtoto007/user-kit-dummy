<?php

namespace Toto\UserKit\Repositories;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use stdClass;

class UserRepository
{
    private const BASE_URL = "https://reqres.in/api/users";

    public function __construct(private ClientInterface $httpClient, private RequestFactoryInterface $requestFactory, private StreamFactoryInterface $streamFactory)
    {

    }

    public function findById(int $id): stdClass
    {
        $request = $this->requestFactory->createRequest('GET', self::BASE_URL."/$id");
        $response = $this->httpClient->sendRequest($request);
        return json_decode($response->getBody()->getContents())->data;
    }
}