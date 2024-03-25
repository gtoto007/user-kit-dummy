<?php

namespace Toto\UserKit\Services;


use Exception;
use Toto\UserKit\DTOs\UserDto;
use Toto\UserKit\Repositories\UserRepository;

class UserService
{

    public function __construct(private UserRepository $repository)
    {
    }

    public function findUserByID(int $id): UserDto
    {
        $data = $this->repository->findById($id);;
        return new UserDto(
            id: $data->id,
            email: $data->email,
            first_name: $data->first_name,
            last_name: $data->last_name,
            avatar: $data->avatar
        );
    }
}