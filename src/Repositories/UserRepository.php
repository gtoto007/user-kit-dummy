<?php

namespace Toto\UserKit\Repositories;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use stdClass;
use Toto\UserKit\Exceptions\HttpResponseException;
use Toto\UserKit\Exceptions\UserNotCreatedException;
use Toto\UserKit\Exceptions\UserNotFoundException;

/**
 *
 * This class is responsible for handling the user data from the API.
 * It provides methods to find, paginate and create users.
 *
 * @package Toto\UserKit\Repositories
 */
class UserRepository
{
    /**
     * The base URL for the user API.
     */
    private const BASE_URL = "https://reqres.in/api/users";

    /**
     * Constructor.
     *
     * Initializes the HTTP client, request factory and stream factory. If not provided, it will discover the default ones.
     *
     * @param ClientInterface|null $httpClient The HTTP client to make requests. If not provided, it will discover the default one.
     * @param RequestFactoryInterface|null $requestFactory The request factory to create requests. If not provided, it will discover the default one.
     * @param StreamFactoryInterface|null $streamFactory The stream factory to create streams. If not provided, it will discover the default one.
     */
    public function __construct(private ?ClientInterface $httpClient = null, private ?RequestFactoryInterface $requestFactory = null, private ?StreamFactoryInterface $streamFactory = null)
    {
        $this->httpClient = $this->httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $this->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $this->streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * Find a user by ID.
     *
     * @param int $id The ID of the user.
     *
     * @return stdClass The user data.
     * @throws UserNotFoundException If the user was not found.
     *
     * @throws ClientExceptionInterface|HttpResponseException If there was an error making the request.
     */
    public function find(int $id): stdClass
    {
        $request = $this->requestFactory->createRequest('GET', self::BASE_URL."/$id");
        $response = $this->httpClient->sendRequest($request);
        $body = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() === 404 || ($response->getStatusCode() === 200 && ! isset($body->data))) {
            throw new UserNotFoundException("user with id $id does not exist");
        }

        if ($response->getStatusCode() !== 200) {
            throw new HttpResponseException($request, $response);
        }

        return $body->data;
    }

    /**
     * Get a paginated list of users.
     *
     * @param int $page The page number.
     * @param int $per_page The number of users per page.
     *
     * @return stdClass The paginated user data.
     * @throws ClientExceptionInterface
     * @throws HttpResponseException
     */
    public function paginate(int $page = 1, int $per_page = 6): stdClass
    {
        $queryParams = http_build_query([
            'page' => $page,
            'per_page' => $per_page,
        ]);
        $request = $this->requestFactory->createRequest('GET', self::BASE_URL.'?'.$queryParams);
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new HttpResponseException($request, $response);
        }
        return json_decode($response->getBody()->getContents());
    }

    /**
     * Create a new user.
     *
     * @param string $first_name The first name of the user.
     * @param string $last_name The last name of the user.
     * @param string $job The job of the user.
     *
     * @return stdClass The created user data.
     * @throws UserNotCreatedException|ClientExceptionInterface|HttpResponseException If the user could not be created.
     *
     */
    public function create(string $first_name, string $last_name, string $job): stdClass
    {
        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'job' => $job,
        ];

        $request = $this->createPostRequest($data);
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() !== 201) {
            throw new HttpResponseException($request, $response);
        }

        $body = json_decode($response->getBody()->getContents());

        if (! isset($body->id)) {
            throw new UserNotCreatedException("User creation failed: ID does not exist");
        }
        return $body;
    }

    private function createPostRequest($data): RequestInterface
    {
        $json = json_encode($data);
        $body = $this->streamFactory->createStream($json);

        return $this->requestFactory->createRequest('POST', self::BASE_URL)
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');

    }
}