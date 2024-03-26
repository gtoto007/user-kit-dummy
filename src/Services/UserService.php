<?php

namespace Toto\UserKit\Services;


use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Toto\UserKit\DTOs\Paginator;
use Toto\UserKit\DTOs\UserDto;
use Toto\UserKit\Exceptions\UserNotFoundException;
use Toto\UserKit\Repositories\UserRepository;

class UserService
{

    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PER_PAGE = 6;

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
        $record = $this->repository->find($id);;
        return $this->mapUserDTO($record);
    }

    public function paginate(int $page = self::DEFAULT_PAGE, int $per_page = self::DEFAULT_PER_PAGE): Paginator
    {
        $page = $page > 0 ? $page : self::DEFAULT_PAGE;
        $per_page = $per_page > 0 ? $per_page : self::DEFAULT_PER_PAGE;

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
}