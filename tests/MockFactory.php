<?php

namespace Toto\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;


class MockFactory
{

    static function createHttpClient()
    {
        $mockHttpClient = Mockery::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('sendRequest')
            ->andReturnUsing(function (RequestInterface $request) {
                if (preg_match('@/api/users/(\d+)$@', $request->getUri()->getPath(), $matches)) {
                    return self::handleUserIdRequest(userId: $matches[1]);
                } else if ($request->getUri()->getPath() === '/api/users') {
                    return self::handleUsersRequest($request);
                }
                return self::createHttpResponse("{}", status: 404);
            });

        return $mockHttpClient;
    }

    static function handleUserIdRequest($userId): Response
    {
        $user = self::findStubUser($userId);
        if ($user) {
            return self::createHttpResponse(json_encode(['data' => $user]));
        } else
            return self::createHttpResponse("{}");
    }

    static function findStubUser($userId)
    {
        $page = json_decode(self::readFile('Stubs/api-users/page=1&per_page=6.json'), true);
        foreach ($page["data"] as $user) {
            if ($user["id"] == $userId) {
                return $user;
            }
        }
        return null;
    }


    static function readFile($file): string|false
    {
        return file_get_contents(__DIR__.'/'.$file);
    }

    static function fileExists($file): bool
    {
        return file_exists(__DIR__.'/'.$file);
    }

    static function createHttpResponse($content, $status = 200): Response
    {
        return new Response($status, [], (new HttpFactory())->createStream($content));
    }

    static function handleUsersRequest(RequestInterface $request): Response
    {
        $query = $request->getUri()->getQuery();
        $stub_file_path = "Stubs/api-users/{$query}.json";

        if (self::fileExists($stub_file_path)) {
            $response_body = self::readFile($stub_file_path);
        } else {
            $response_body = self::handleEmptyPageResponse($query);
        }

        return self::createHttpResponse($response_body);
    }


    static function handleEmptyPageResponse($query): string
    {
        parse_str($query, $params);
        $empty_page = json_decode(self::readFile('Stubs/api-users/empty-page.json'), true);
        $empty_page["page"] = intval($params["page"]) ?? 0;
        $empty_page["per_page"] = intval($params["per_page"]) ?? 0;
        $empty_page["total_pages"] = $empty_page["per_page"] > 0 ? ceil($empty_page["total"] / $empty_page["per_page"]) : 0;
        return json_encode($empty_page);
    }

}

?>