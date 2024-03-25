<?php

namespace Toto\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;


class MockFactory
{
    const usersData = [
        1 => [
            'id' => 1,
            'email' => 'george.bluth@reqres.in',
            'first_name' => 'George',
            'last_name' => 'Bluth',
            'avatar' => 'https://reqres.in/img/faces/1-image.jpg'
        ],
        2 => [
            'id' => 2,
            'email' => 'janet.weaver@reqres.in',
            'first_name' => 'Janet',
            'last_name' => 'Weaver',
            'avatar' => 'https://reqres.in/img/faces/2-image.jpg'
        ],
        3 => [
            'id' => 3,
            'email' => 'emma.wong@reqres.in',
            'first_name' => 'Emma',
            'last_name' => 'Wong',
            'avatar' => 'https://reqres.in/img/faces/3-image.jpg'
        ]
    ];



    static function createHttpClient()
    {
        $mockHttpClient = Mockery::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('sendRequest')
            ->andReturnUsing(function (RequestInterface $request) {
                if (preg_match('@/api/users/(\d+)$@', $request->getUri()->getPath(), $matches)) {
                    $userId = $matches[1];
                    if (array_key_exists($userId, self::usersData)) {
                        return new Response(200, [], (new HttpFactory())->createStream(json_encode(['data' => self::usersData[$userId]])));
                    }
                }
                return new Response(200, [], (new HttpFactory())->createStream("{}"));
            });
        return $mockHttpClient;

    }
}

?>