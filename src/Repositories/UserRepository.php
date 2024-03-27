<?php

namespace Toto\UserKit\Repositories;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use stdClass;
use Toto\UserKit\Exceptions\Api\ApiException;
use Toto\UserKit\Exceptions\Api\BadRequestException;
use Toto\UserKit\Exceptions\Api\ResourceNotCreatedException;
use Toto\UserKit\Exceptions\Api\ResourceNotFoundException;
use Toto\UserKit\Exceptions\Api\ServerErrorException;
use Toto\UserKit\Exceptions\Api\UnauthorizedException;


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
     * @throws ResourceNotFoundException when user with $id doesn't exist
     * @throws BadRequestException  when the HTTP response status code is 400.
     * @throws UnauthorizedException when the HTTP response status code start with 401.
     * @throws ServerErrorException when the HTTP response status code start with 5**.
     * @throws ApiException  in all other cases or when `sendRequest` method throws a `ClientExceptionInterface`.
     */
    public function find(int $id): stdClass
    {
        $request = $this->requestFactory->createRequest('GET', self::BASE_URL."/$id");
        $response = $this->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
                $body = json_decode($response->getBody()->getContents());
                if (empty($body) || ! isset($body->data)) {
                    throw new ResourceNotFoundException($request, $response, "user with id $id does not exist");
                }
                return $body->data;
            case 404:
                throw new ResourceNotFoundException($request, $response, "user with id $id does not exist");
            default:
                $this->throwAppropriateApiException($request, $response);
        }
    }

    /**
     * This method sends a request using the HTTP client. If the client throws an exception, it is caught and wrapped into an ApiException
     * @param RequestInterface $request The request to be sent.
     * @return ResponseInterface The response from the HTTP client.
     * @throws ApiException If the HTTP client throws an exception, it is caught and wrapped in an ApiException.
     */
    private function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ApiException(request: $request, previous: $e);
        }
    }


    /**
     * @throws BadRequestException  when the HTTP response status code is 400.
     * @throws UnauthorizedException when the HTTP response status code start with 401.
     * @throws ServerErrorException when the HTTP response status code start with 5**.
     * @throws ApiException  in all other cases
     */
    private function throwAppropriateApiException(RequestInterface $request, ResponseInterface $response)
    {

        if ($response->getStatusCode() == 400) {
            throw new BadRequestException($request, $response);
        }
        if ($response->getStatusCode() == 401) {
            throw new UnauthorizedException($request, $response);
        }
        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            throw new ServerErrorException($request, $response);
        }
        throw new ApiException($request, $response);
    }

    /**
     * Get a paginated list of users.
     *
     * @param int $page The page number.
     * @param int $per_page The number of users per page.
     *
     * @return stdClass The paginated user data.
     * @throws BadRequestException  when the HTTP response status code is 400.
     * @throws UnauthorizedException when the HTTP response status code start with 401.
     * @throws ServerErrorException when the HTTP response status code start with 5**.
     * @throws ApiException  in all other cases or when `sendRequest` method throws a `ClientExceptionInterface`.
     */
    public function paginate(int $page = 1, int $per_page = 6): stdClass
    {
        $queryParams = http_build_query([
            'page' => $page,
            'per_page' => $per_page,
        ]);
        $request = $this->requestFactory->createRequest('GET', self::BASE_URL.'?'.$queryParams);

        $response = $this->sendRequest($request);
        if ($response->getStatusCode() !== 200) {
            $this->throwAppropriateApiException($request, $response);
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
     * @throws ResourceNotCreatedException when the body response does not contain a user id
     * @throws BadRequestException  when the HTTP response status code is 400.
     * @throws UnauthorizedException when the HTTP response status code start with 401.
     * @throws ServerErrorException when the HTTP response status code start with 5**.
     * @throws ApiException  in all other cases or when `sendRequest` method throws a `ClientExceptionInterface`.
     */
    public function create(string $first_name, string $last_name, string $job): stdClass
    {
        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'job' => $job,
        ];

        $request = $this->createPostRequest($data);
        $response = $this->sendRequest($request);

        if ($response->getStatusCode() !== 201) {
            $this->throwAppropriateApiException($request, $response);
        }
        $body = json_decode($response->getBody()->getContents());

        if (empty($body) || ! isset($body->id)) {
            throw new ResourceNotCreatedException($request, $response, "User creation failed: ID does not exist");
        }
        return $body;
    }

    /**
     * @param array $data
     * @return RequestInterface
     */
    private function createPostRequest(array $data): RequestInterface
    {
        $json = json_encode($data);
        $body = $this->streamFactory->createStream($json);

        return $this->requestFactory->createRequest('POST', self::BASE_URL)
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');

    }
}