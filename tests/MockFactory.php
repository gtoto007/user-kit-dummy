<?php

namespace Toto\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;


class MockFactory
{

    public static function createHttpClient()
    {
        $mockHttpClient = Mockery::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('sendRequest')
            ->andReturnUsing(function (RequestInterface $request) {
                if (preg_match('@/api/users(/(\d+))?$@', $request->getUri()->getPath(), $matches)) {
                    if (isset($matches[2])) {
                        return self::mockGetUserResponse(userId: intval($matches[2]));
                    } else if ($request->getMethod() == 'GET') {
                        return self::mockGetUsersResponse($request);
                    } else if ($request->getMethod() == 'POST') {
                        return self::mockPostUserResponse($request);
                    }
                }
                return self::mock404Response();
            });

        return $mockHttpClient;
    }

    public static function createHttpClientWithCustomResponse(int $status_code, string $content)
    {
        $mockHttpClient = Mockery::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('sendRequest')->andReturn(self::createHttpResponse($content, $status_code));
        return $mockHttpClient;
    }

    private static function mockGetUserResponse(int $userId): Response
    {
        $user = self::findStubUser($userId);
        if ($user) {
            return self::createHttpResponse(json_encode(['data' => $user]));
        } else
            return self::createHttpResponse("{}");
    }

    private static function findStubUser(int $userId)
    {
        $page = json_decode(self::readFile('Stubs/api-users/page=1&per_page=6.json'), true);
        foreach ($page["data"] as $user) {
            if ($user["id"] == $userId) {
                return $user;
            }
        }
        return null;
    }

    private static function mock404Response(): Response
    {
        return self::createHttpResponse("{}", status: 404);
    }


    private static function readFile(string $file): string|false
    {
        return file_get_contents(__DIR__.'/'.$file);
    }

    private static function fileExists(string $file): bool
    {
        return file_exists(__DIR__.'/'.$file);
    }

    private static function createHttpResponse(string $content, int $status = 200): Response
    {
        return new Response($status, [], (new HttpFactory())->createStream($content));
    }

    private static function mockGetUsersResponse(RequestInterface $request): Response
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

    private static function mockPostUserResponse(RequestInterface $request): Response
    {
        $requestData = json_decode($request->getBody()->getContents(), true);
        $userId = rand(1, 1000);
        $responseData = array_merge(['id' => $userId], $requestData);
        return self::createHttpResponse(json_encode($responseData), 201);

    }

    private static function handleEmptyPageResponse($query): string
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