<?php

namespace Toto\UserKit\Services;


use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Toto\UserKit\DTOs\UserDto;
use Toto\UserKit\Exceptions\UserNotFoundException;
use Toto\UserKit\Repositories\UserRepository;

class UserService
{

    /**
     * @param UserRepository|null $repository
     */
    public function __construct(private ?UserRepository $repository = null)
    {
        $this->repository = $repository ?? new UserRepository();
    }

    /**
     * @param int $id
     * @return UserDto|null
     * @throws ClientExceptionInterface
     */
    public function findUser(int $id): ?UserDto
    {
        try {
            return $this->findUserOrFail($id);
        } catch (UserNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param int $id
     * @return UserDto
     * @throws UserNotFoundException
     * @throws ClientExceptionInterface
     */
    public function findUserOrFail(int $id): UserDto
    {
        $data = $this->repository->find($id);;
        return new UserDto(
            id: $data->id,
            email: $data->email,
            first_name: $data->first_name,
            last_name: $data->last_name,
            avatar: $data->avatar
        );
    }
}