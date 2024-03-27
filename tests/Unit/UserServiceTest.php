<?php

use Toto\UserKit\DTOs\Paginator;
use Toto\UserKit\DTOs\UserDto;
use Toto\UserKit\Exceptions\Api\ResourceNotCreatedException;
use Toto\UserKit\Exceptions\Api\ResourceNotFoundException;


describe('createUser', function () {

    it('creates a new user', function ($first_name, $last_name, $job) {

        // Setup
        $service = createUserServiceMock();

        // Act
        $user_id = $service->createUser($first_name, $last_name, $job);

        // Expect
        expect($user_id)->not->toBeEmpty();
    })->with([['Mario', 'Rossi', 'Developer']]);

    it('throws ResourceNotCreatedException when id does not exist in body response', function () {
        // Setup
        $service = createUserServiceMockWithCustomHttpResponse(status_code: 201, content: "{success:true}");

        // Act
        $service->createUser("first", "last", "job");

    })->throws(ResourceNotCreatedException::class);


    foreach (getErrorCodes() as $statusCode => $exception) {
        it("throws {$exception} when status_code equals {$statusCode}", function () use ($statusCode, $exception) {

            // Setup
            $userService = createUserServiceMockWithCustomHttpResponse(status_code: $statusCode, content: "{}");

            // Act
            $userService->createUser("first", "last", "job");

        })->throws($exception);
    }


});

describe('findUser', function () {
    it('retrieves a single user by ID', function (int $id, string $email, string $first_name, string $last_name, string $avatar) {

        // Setup
        $service = createUserServiceMock();

        // Act
        /* When you call the findUser method, it uses a mocked sendRequest function to get user data from a JSON file at /tests/Stubs/api-users/page=1&per_page=6.json.*/
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
        $service = createUserServiceMock();

        // Act
        $user = $service->findUser($user_id);

        // Expect
        expect($user)->toBeNull();

    })->with([100, 0, -1]);


});

describe('findUserOrFail', function () {
    it('throws a ResourceNotFoundException when the user_id does not exist', function ($user_id) {

        // Setup
        $service = createUserServiceMock();

        // Act
        $service->findUserOrFail($user_id);

    })->with([100, 0, -1])->throws(ResourceNotFoundException::class);


    foreach (getErrorCodes() as $statusCode => $exception) {
        it("throws {$exception} when status_code equals {$statusCode}", function () use ($statusCode, $exception) {

            // Setup
            $userService = createUserServiceMockWithCustomHttpResponse(status_code: $statusCode, content: "{}");

            // Act
            $userService->findUserOrFail(1);

        })->throws($exception);
    }

});

describe('paginate', function () {
    it('retrieves a paginated list of users', function (int $page, int $per_page, int $total_pages_expected, array $expected_users) {

        // Setup
        $service = createUserServiceMock();

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

    foreach (getErrorCodes() as $statusCode => $exception) {
        it("throws {$exception} when status_code equals {$statusCode}", function () use ($statusCode, $exception) {

            // Setup
            $userService = createUserServiceMockWithCustomHttpResponse(status_code: $statusCode, content: "{}");

            // Act
            $userService->paginate(1, 12);

        })->throws($exception);
    }
});