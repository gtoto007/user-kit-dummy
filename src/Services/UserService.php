<?php

namespace Toto\UserKit\Services;


use Toto\UserKit\DTOs\Paginator;
use Toto\UserKit\DTOs\UserDto;
use Toto\UserKit\Exceptions\Api\ApiException;
use Toto\UserKit\Exceptions\Api\BadRequestException;
use Toto\UserKit\Exceptions\Api\ResourceNotCreatedException;
use Toto\UserKit\Exceptions\Api\ResourceNotFoundException;
use Toto\UserKit\Exceptions\Api\ServerErrorException;
use Toto\UserKit\Exceptions\Api\UnauthorizedException;
use Toto\UserKit\Repositories\UserRepository;


/**
 * The UserService class handles operations related to users.
 *
 * It provides methods for finding a user by ID, paginating users, and creating a new user.
 * Each method interacts with a UserRepository to perform the necessary api operations.
 *
 * @package Toto\UserKit\Services
 */
class UserService
{
    /**
     * Constructs a new instance of the UserService class.
     *
     * @param UserRepository|null $repository An optional UserRepository to use for persistent operations.
     * If not provided, a new UserRepository will be created and used.
     */
    public function __construct(private ?UserRepository $repository = null)
    {
        $this->repository = $repository ?? new UserRepository();
    }

    /**
     * Finds a user by ID or returns null if the user is not found.
     *
     * @param int $id The ID of the user to find.
     * @return UserDto|null The found user as a Data Transfer Object, or null if the user is not found.
     */
    public function findUser(int $id): ?UserDto
    {
        try {
            return $this->findUserOrFail($id);
        } catch (ApiException $e) {
            return null;
        }
    }

    /**
     * Finds a user by ID or throws an exception if the user is not found.
     *
     * @param int $id The ID of the user to find.
     * @return UserDto The found user as a Data Transfer Object.
     * @throws ResourceNotFoundException when user with $id doesn't exist
     * @throws BadRequestException  when the HTTP response status code is 400.
     * @throws UnauthorizedException when the HTTP response status code start with 401.
     * @throws ServerErrorException when the HTTP response status code start with 5**.
     * @throws ApiException  in all other cases
     */
    public function findUserOrFail(int $id): UserDto
    {
        $record = $this->repository->find($id);;
        return $this->mapUserDTO($record);
    }

    /**
     * Paginates the users
     *
     * @param int $page The page number to retrieve. Defaults to Paginator::DEFAULT_PAGE.
     * @param int $per_page The number of users per page. Defaults to Paginator::DEFAULT_PER_PAGE.
     * @return Paginator A Paginator object containing the paginated results.
     * @throws BadRequestException  when the HTTP response status code is 400.
     * @throws UnauthorizedException when the HTTP response status code start with 401.
     * @throws ServerErrorException when the HTTP response status code start with 5**.
     * @throws ApiException  in all other cases
     */

    public function paginate(int $page = Paginator::DEFAULT_PAGE, int $per_page = Paginator::DEFAULT_PER_PAGE): Paginator
    {
        $page = $page > 0 ? $page : Paginator::DEFAULT_PAGE;
        $per_page = $per_page > 0 ? $per_page : Paginator::DEFAULT_PER_PAGE;

        $response = $this->repository->paginate($page, $per_page);
        return new Paginator(
            page: $response->page,
            per_page: $response->per_page,
            total_pages: $response->total_pages,
            total: $response->total,
            data: array_map(fn($record) => $this->mapUserDTO($record), $response->data)
        );
    }

    private function mapUserDTO($data): UserDto
    {
        return new UserDto(
            id: $data->id,
            email: $data->email,
            first_name: $data->first_name,
            last_name: $data->last_name,
            avatar: $data->avatar
        );
    }

    /**
     * Creates a new user.
     *
     * @param string $first_name The first name of the user.
     * @param string $last_name The last name of the user.
     * @param string $job The job of the user.
     * @return int The ID of the newly created user.
     *
     * @throws ResourceNotCreatedException when the body response does not contain a user id
     * @throws BadRequestException  when the HTTP response status code is 400.
     * @throws UnauthorizedException when the HTTP response status code start with 401.
     * @throws ServerErrorException when the HTTP response status code start with 5**.
     * @throws ApiException  in all other cases
     */
    public function createUser(string $first_name, string $last_name, string $job): int
    {
        $response = $this->repository->create($first_name, $last_name, $job);
        return intval($response->id);
    }
}