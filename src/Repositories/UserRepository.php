<?php

namespace Toto\UserKit\Repositories;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use stdClass;
use Toto\UserKit\Exceptions\UserNotFoundException;

class UserRepository
{
    private const BASE_URL = "https://reqres.in/api/users";

    /**
     * @param ClientInterface|null $httpClient
     * @param RequestFactoryInterface|null $requestFactory
     * @param StreamFactoryInterface|null $streamFactory
     */
    public function __construct(private ?ClientInterface $httpClient = null, private ?RequestFactoryInterface $requestFactory = null, private ?StreamFactoryInterface $streamFactory = null)
    {
        $this->httpClient = $this->httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $this->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $this->streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws UserNotFoundException
     */
    public function find(int $id): stdClass
    {
        $request = $this->requestFactory->createRequest('GET', self::BASE_URL."/$id");
        $response = $this->httpClient->sendRequest($request);
        $body = json_decode($response->getBody()->getContents());
        if (! isset($body->data)) {
            throw new UserNotFoundException("user with id $id does not exist");
        }
        return $body->data;

    }
}