<?php

use Toto\UserKit\DTOs\Paginator;
use Toto\UserKit\DTOs\UserDto;
use Toto\UserKit\Exceptions\UserNotFoundException;
use Toto\UserKit\Services\UserService;


describe('createUser', function () {
    it('creates a new user', function ($first_name, $last_name, $job) {
        // Setup
        $repository = createUserRepositoryMock();
        $service = new UserService($repository);

        // Act
        $user_id = $service->createUser($first_name, $last_name, $job);

        // Expect
        expect($user_id)->not->toBeEmpty();
    })->with([['Mario', 'Rossi', 'Developer']]);

    it('throws HttpResponseException when status_code does not equal 200', function ($status_code) {
        // Setup
        $repository = createUserRepoMockWithCustomResponse($status_code);
        $service = new UserService($repository);

        // Act
        $service->createUser("first", "last", "job");

    })->with([400, 500, 504])->throws(\Toto\UserKit\Exceptions\HttpResponseException::class);

    it('throws UserNotCreatedException when id does not exist in body response', function () {
        // Setup
        $repository = createUserRepoMockWithCustomResponse(status_code: 200, content: "{success:true}");
        $service = new UserService($repository);

        // Act
        $user_id = $service->createUser("first", "last", "job");

    })->throws(\Toto\UserKit\Exceptions\HttpResponseException::class);
});

describe('findUser', function () {
    it('retrieves a single user by ID', function (int $id, string $email, string $first_name, string $last_name, string $avatar) {

        // Setup
        $repository = createUserRepositoryMock();
        $service = new UserService($repository);

        // Act
        $user = $service->findUser($id);

        // Expect
        expect($user->id)->toEqual($id)
            ->and($user->email)->toEqual($email)
            ->and($user->first_name)->toEqual($first_name)
            ->and($user->last_name)->toEqual($last_name)
            ->and($user->avatar)->toEqual($avatar);
    })->with([
            [1, 'george.bluth@reqres.in', 'George', 'Bluth', 'https://reqres.in/img/faces/1-image.jpg'],
            [2, 'janet.weaver@reqres.in', 'Janet', 'Weaver', 'https://reqres.in/img/faces/2-image.jpg'],
            [3, 'emma.wong@reqres.in', 'Emma', 'Wong', 'https://reqres.in/img/faces/3-image.jpg']]
    );

    it('returns null when the userId does not exist', function (int $user_id) {

        // Setup
        $repository = createUserRepositoryMock();
        $service = new UserService($repository);

        // Act
        $user = $service->findUser($user_id);

        // Expect
        expect($user)->toBeNull();

    })->with([100, 0, -1]);


});

describe('findUserOrFail', function () {
    it('throws a UserNotFoundException when the user_id does not exist', function ($user_id) {

        // Setup
        $repository = createUserRepositoryMock();
        $service = new UserService($repository);

        // Act
        $service->findUserOrFail($user_id);

    })->with([100, 0, -1])->throws(UserNotFoundException::class);

    it('throws HttpResponseException when status_code does not equal 200', function ($status_code) {
        // Setup
        $repository = createUserRepoMockWithCustomResponse($status_code,"{}");
        $service = new UserService($repository);

        // Act
        $service->findUserOrFail(1);

    })->with([400, 500, 504])->throws(\Toto\UserKit\Exceptions\HttpResponseException::class);
});

describe('paginate', function () {
    it('retrieves a paginated list of users', function (int $page, int $per_page, int $total_pages_expected, array $expected_users) {

        // Setup
        $repository = createUserRepositoryMock();
        $service = new UserService($repository);
        $total_users_expected = 12;
        $per_page_expected = $per_page > 0 ? $per_page : Paginator::DEFAULT_PER_PAGE;
        $page_expected = $page > 0 ? $page : Paginator::DEFAULT_PAGE;

        // Act
        $paginator = $service->paginate($page, $per_page);

        // Expect
        expect($paginator->page)->toEqual($page_expected)
            ->and($paginator->per_page)->toEqual($per_page_expected)
            ->and($paginator->total_pages)->toEqual($total_pages_expected)
            ->and($paginator->total)->toEqual($total_users_expected);

        foreach ($paginator->data as $index => $user) {
            expect($user)->toBeInstanceOf(UserDto::class)
                ->and($user->last_name)->toEqual($expected_users[$index]);
        }

    })->with([
        //VALID CASE:
        [1, 6, 2, ['Bluth', 'Weaver', 'Wong', 'Holt', 'Morris', 'Ramos']],
        [6, 2, 6, ['Edwards', 'Howell']],
        [1000, 10, 2, []],
        //INVALID CASE:
        [0, 0, 2, ['Bluth', 'Weaver', 'Wong', 'Holt', 'Morris', 'Ramos']],
        [0, 2, 6, ['Bluth', 'Weaver']],
        [-1, 2, 6, ['Bluth', 'Weaver']],
        [1, 0, 2, ['Bluth', 'Weaver', 'Wong', 'Holt', 'Morris', 'Ramos']],
        [1, -1, 2, ['Bluth', 'Weaver', 'Wong', 'Holt', 'Morris', 'Ramos']],
    ]);
});