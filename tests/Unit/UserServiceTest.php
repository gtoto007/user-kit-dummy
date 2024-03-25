<?php

use Toto\UserKit\Services\UserService;

it('retrieves a single user by ID', function ($id, $email, $first_name, $last_name, $avatar) {

    // Setup
    $repository = createUserRepository();
    // Act
    $service = new UserService($repository);
    $user = $service->findUserByID($id);

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
