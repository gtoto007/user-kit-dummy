<?php

use Toto\UserKit\Exceptions\UserNotFoundException;
use Toto\UserKit\Repositories\UserRepository;
use Toto\UserKit\Services\UserService;

it('retrieves a single user by ID', function ($id, $email, $first_name, $last_name, $avatar) {

    // Setup
    $repository = createUserRepositoryMock();
    // Act
    $service = new UserService(new UserRepository());
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


it('throws a UserNotFoundException when the userId does not exist', function ($id) {

    // Setup
    $repository = createUserRepositoryMock();
    // Act
    $service = new UserService($repository);
    $service->findUserOrFail($id);

})->with([4, 5, 6])->throws(UserNotFoundException::class);

it('returns null when the userId does not exist', function ($id) {
    // Setup
    $repository = createUserRepositoryMock();
    // Act
    $service = new UserService($repository);
    expect($service->findUser($id))->toBeNull();

})->with([4, 5, 6]);
