<?php

use Toto\UserKit\DTOs\UserDto;

it('converts to array correctly', function () {
    $user = new UserDto(1, 'test@example.com', 'First', 'Last', 'avatar.png');

    $expected = [
        'first_name' => 'First',
        'last_name' => 'Last',
        'email'=>'test@example.com',
        'avatar' => 'avatar.png',
        'id' => 1
    ];
    expect($user->toArray())->toEqual($expected);
});

it('serializes to json correctly', function () {
    $user = new UserDto(1, 'test@example.com', 'First', 'Last', 'avatar.png');

    $expected = json_encode([
        'first_name' => 'First',
        'last_name' => 'Last',
        'email'=>'test@example.com',
        'avatar' => 'avatar.png',
        'id' => 1
    ]);

    expect(json_encode($user))->toEqual($expected);
});