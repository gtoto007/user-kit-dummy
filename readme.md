
# User Kit: a framework-agnostic composer PHP package

The goal of this repository is to develop a framework-agnostic PHP package using Composer. My aim is to offer thorough answers to a range of questions, showcasing my methodology in testing, and to provide a basis for discussion during a technical job interview.

### 🎯 The Original Task
Create a framework-agnostic Composer package that provides a service for retrieving and creating users via a remote API (integrate with the https://reqres.in/ dummy API for the purposes of this test).
The service should provide:
- A method to retrieve a single user by ID
- A method to retrieve a paginated list of users
- A method to create a new user, providing a name and job, and returning a User ID.
- All users returned by the service must be converted into well defined DTO models implementing JSON serializable interfaces and supporting conversion to a standard array structure.

### 👨‍💻 Development and Testing Setup

##### 1. Clone the repository
```
git clone git@github.com:gtoto007/user-kit-dummy.git
```
##### 2. Installation  Dependencies
Run `composer install` to install all required dependencies. This includes development dependencies necessary for testing the package.

##### 3. Running Tests
To test the package, simply execute the following command:
```sh
./vendor/bin/pest
```

### 🚀 For Production Use:

##### 1. How to add the package in  php project

1. Add the repository in composer.json as follows:
``` json
    "repositories": [
        {
            "type": "github",
            "url": "git@github.com:gtoto007/user-kit-dummy.git"
        }
    ],
```
2. Now are you are ready to add the custom package with the following command:
```sh
composer require toto/user-kit
```
#####  2. Install an HTTP Client for Production Use
For production use, ensure that an HTTP client package is installed (e.g., `composer require guzzlehttp/guzzle`).
This is because the package is designed to be agnostic of any specific HTTP client while complying with PSR-18 (HTTP Client) and PSR-17 (HTTP Factories) standards. This approach ensures that it can work flexibly and interoperably with any HTTP client that follows these specifications.


### 📦 How to Use the Package

```php
use Toto\UserKit\Services\UserService;

// Initialize the UserService
$service = new UserService();

// Retrieve a single user by ID
$user = $service->findUser(1); 
// This retrieves a UserDto object for the user with ID 1 from the API at https://reqres.in/users/1

// Retrieve a paginated list of users
$paginator = $service->paginate(page: 1, per_page: 5); 
// This retrieves a PaginatorDto object containing a paginated list of users from the API at https://reqres.in/users?page=1&per_page=5
// Access the list of User DTOs via $paginator->data

// Create a new User
$user_id = $service->create("John", "Doe", "Developer"); 
// This sends a POST request to https://reqres.in/users and returns the ID of the created user

```
## ❓Questions
### 1. How can you make your code testable?
I opted to use the [Pest Framework](https://pestphp.com/) package for writing tests because it allows for the creation of tests in a more readable and elegant manner. Additionally, it's gaining significant popularity in major projects like Laravel.

You can find the unit tests in  `/tests/Unit` folder and run them by executing the command:
```sh
./vendor/bin/pest
```

Output:
````
 PASS  Tests\Unit\UserDtoTest
  ✓ it converts to array correctly
  ✓ it serializes to json correctly

 PASS  Tests\Unit\UserServiceTest
  ✓ createUser → it creates a new user with ('Mario', 'Rossi', 'Developer')                                              
  ✓ createUser → it throws ResourceNotCreatedException when id does not exist in body response
  ✓ createUser → it throws BadRequestException when status_code equals 400
  ✓ createUser → it throws UnauthorizedException when status_code equals 401
  ✓ createUser → it throws ApiException when status_code equals 412
  ✓ createUser → it throws ServerErrorException when status_code equals 500
  ✓ findUser → it retrieves a single user by ID with (1, 'george.bluth@reqres.in', 'George', …)
  ✓ findUser → it retrieves a single user by ID with (2, 'janet.weaver@reqres.in', 'Janet', …)
  ✓ findUser → it retrieves a single user by ID with (3, 'emma.wong@reqres.in', 'Emma', …)
  ✓ findUser → it returns null when the userId does not exist with (100)
  ✓ findUser → it returns null when the userId does not exist with (0)
  ✓ findUser → it returns null when the userId does not exist with (-1)
  ✓ findUserOrFail → it throws a ResourceNotFoundException when the user_id does not exist with (100)
  ✓ findUserOrFail → it throws a ResourceNotFoundException when the user_id does not exist with (0)
  ✓ findUserOrFail → it throws a ResourceNotFoundException when the user_id does not exist with (-1)
  ✓ findUserOrFail → it throws BadRequestException when status_code equals 400
  ✓ findUserOrFail → it throws UnauthorizedException when status_code equals 401
  ✓ findUserOrFail → it throws ApiException when status_code equals 412
  ✓ findUserOrFail → it throws ServerErrorException when status_code equals 500
  ✓ paginate → it retrieves a paginated list of users with (1, 6, 2, …)
  ✓ paginate → it retrieves a paginated list of users with (6, 2, 6, …)
  ✓ paginate → it retrieves a paginated list of users with (1000, 10, 2, …)
  ✓ paginate → it retrieves a paginated list of users with (0, 0, 2, …)
  ✓ paginate → it retrieves a paginated list of users with (0, 2, 6, …)
  ✓ paginate → it retrieves a paginated list of users with (-1, 2, 6, …)
  ✓ paginate → it retrieves a paginated list of users with (1, 0, 2, …)
  ✓ paginate → it retrieves a paginated list of users with (1, -1, 2, …)
  ✓ paginate → it throws BadRequestException when status_code equals 400
  ✓ paginate → it throws UnauthorizedException when status_code equals 401
  ✓ paginate → it throws ApiException when status_code equals 412
  ✓ paginate → it throws ServerErrorException when status_code equals 500

````

---
### 2. Would your tests still pass if the API was offline or the data on the API changed?
To ensure my tests remain reliable regardless of the API's availability or changes in its data, I use mock and stub techniques.
By default, the `UserRepository` and `UserService` will use an HTTP client discovered by the system if no parameters are provided.
However, you can directly inject a mock response into the `UserRepository` through the `ClientInterface`.

In the following example, the `UserService` class test is isolated by mocking the response from `ClientInterface`, which is then injected into the `UserRepository` class:
```php
   it('retrieves a single user by ID', function (int $id, string $email, string $first_name, string $last_name, string $avatar) {

        // Setup
        // inject a mock response from `ClientInterface` directly into `UserRepository`.
         $repository = new UserRepository(MockFactory::createHttpClient());
         $service =  new UserService($repository);

        // Act
       /*When you call the findUser method, it uses a mocked `sendRequest` function to get user data from a JSON file at /tests/Stubs/api-users/page=1&per_page=6.json.*/
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
   ```


As you can see from the example, HTTP client mock creation is handled by the `MockFactory::createHttpClient`  method
In particular, I have integrated [Mockery package](https://packagist.org/packages/mockery/mockery) to mock the`sendRequest` method's response of `ClientInterface`.The send request method returns a stub JSON file as a response, based on the request URL, to simulate the output. This allows for flexible testing scenarios.

For more details you can view the source code  of the core method  `MockFactory::createHttpClient`
```php
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
```
---
### 3. How can you make a generic exception thrown by an API or third party package more specific to your domain?
I have created a parent exception named `ApiException` which is extended by child exceptions for specific scenarios:

##### - `ResourceNotCreatedException`

This is thrown when the creation of a user fails. For example, when you call the `create` method of UserService and the user id is not present in the response body, it will throw an exception.

##### - `ResourceNotFoundException`
This is thrown when either the user id is not found in  or when the HTTP response status code is 404. For example when you call method `findOrFail` of UserService and user with id doesn't exist, this exception will throw.

##### - `BadRequestException`
This is thrown when the HTTP response status code is 400.

##### - `UnauthorizedException`
This is thrown when the HTTP response status code is 401.

##### - `ServerErrorException`
This is thrown when the HTTP response status code starts with 5**.

 `ApiException` will be thrown in all other cases or when `sendRequest` method throws a `ClientExceptionInterface`.

I adopted this approach because it allows the client to decide whether to catch a generic API exception with `ApiException` or catch a specific exception for a particular scenario. For example, if the user service throws an `UnauthorizedException`, then the client can handle this exception and redirect the user to the login page.

#### Example Code: Throwing Specific Exceptions

In this example, the method `find` throw `ResourceNotFoundException` when user with specific $id doesn't exist
```php
class UserRepository {
 // ....other methods
    public function find(int $id): stdClass
    {
        $request = $this->requestFactory->createRequest('GET', self::BASE_URL."/$id");
        $response = $this->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
                $body = json_decode($response->getBody()->getContents());
                if (empty($body) || ! isset($body->data)) {
                    throw new ResourceNotFoundException($request, $response, "user with id $id does not exist");
                }
                return $body->data;
            case 404:
                throw new ResourceNotFoundException($request, $response, "user with id $id does not exist");
            default:
                $this->throwAppropriateApiException($request, $response);
        }
    }

    private function throwAppropriateApiException(RequestInterface $request, ResponseInterface $response)
    {

        if ($response->getStatusCode() == 400) {
            throw new BadRequestException($request, $response);
        }
        if ($response->getStatusCode() == 401) {
            throw new UnauthorizedException($request, $response);
        }
        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            throw new ServerErrorException($request, $response);
        }
        throw new ApiException($request, $response);
    }
}
```

---
### 4. Bonus: Is this package independent of a specific HTTP client?
Yes, this package is designed to be independent of any specific HTTP client, adhering to the PSR-18 (HTTP Client) and PSR-17 (HTTP Factories) standards. This independence ensures flexibility and ease of integration into various projects.

At the same time, to streamline the process, when you instantiate the `UserService` or `UserRepository`, you don't need to specify the HTTP Client interface explicitly, thanks to a discovery system implemented in UserRepository:

```php
class UserRepository
{
    public function __construct(private ?ClientInterface $httpClient = null, private ?RequestFactoryInterface $requestFactory = null, private ?StreamFactoryInterface $streamFactory = null)
    {
        $this->httpClient = $this->httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $this->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $this->streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }
```


